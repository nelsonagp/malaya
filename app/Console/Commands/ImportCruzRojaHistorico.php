<?php

namespace App\Console\Commands;

use App\Models\Lottery;
use App\Scrapers\CruzRojaScraper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

/**
 * Importa el histórico oficial de la Lotería de la Cruz Roja (sorteos 1965
 * 2007, ~1768 filas) desde el .xls publicado en lotecruz.org.co como
 * "Descargar Histórico". Columnas: Sorteo | Premio1..Premio16 | Fecha |
 * Ciudad. Premio1 es el premio mayor; el resto son "secos" (puede venir
 * vacío como "*"). El formato de Fecha cambia tres veces a lo largo del
 * archivo (confirmado inspeccionando filas de muestra el 2026-06-20):
 *   - 1965–2001: M/D/Y con "/"  (ambiguo en la mayoría de filas, pero
 *     desambiguado por filas con día > 12, p.ej. "06/30/1982")
 *   - 2002–2007: D-M-Y con "-"  (desambiguado igual, p.ej. "23-03-2004")
 *   - cola final: Y-M-D ISO     (p.ej. "2007-06-19")
 *   - un caso aislado (sorteo 2143) viene como entero sin separadores
 *     "31102006" (=DDMMYYYY) en vez de fecha de Excel.
 */
class ImportCruzRojaHistorico extends Command
{
    protected $signature = 'lottery:import-cruzroja-historico {file : Ruta al .xls del histórico}';

    protected $description = 'Importa el histórico (1965-2007) de la Lotería de la Cruz Roja desde el .xls oficial';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("No existe el archivo: {$path}");

            return self::FAILURE;
        }

        $lottery = Lottery::query()->where('slug', 'cruz-roja')->first();

        if (! $lottery) {
            $this->error('No existe la lotería con slug "cruz-roja". Corre el seeder primero.');

            return self::FAILURE;
        }

        $sheet = IOFactory::load($path)->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $results = [];
        $skipped = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $sorteo = $sheet->getCell([1, $row])->getValue();

            if (empty($sorteo)) {
                continue;
            }

            $date = $this->parseHistoricalDate($sheet->getCell([18, $row])->getValue());
            $premioMayor = $this->parsePremio($sheet->getCell([2, $row])->getValue());

            if (! $date || ! $premioMayor) {
                $skipped[] = (int) $sorteo;

                continue;
            }

            $prizeBreakdown = [];

            for ($col = 2; $col <= 17; $col++) {
                $premio = $this->parsePremio($sheet->getCell([$col, $row])->getValue());

                if ($premio) {
                    $prizeBreakdown['premio_'.($col - 1)] = $premio;
                }
            }

            $numbers = array_values(array_filter([$premioMayor['number'], $premioMayor['serie']]));

            $results[] = [
                'draw_date' => $date->toDateString(),
                'draw_number' => (int) $sorteo,
                'numbers' => $numbers,
                'prize_breakdown' => $prizeBreakdown,
                'currency' => 'COP',
                'source_url' => 'https://lotecruz.org.co/wp-content/uploads/2019/05/R-lot_cruzR-207.241.233.34-20070626.xls',
                'raw_data' => ['ciudad' => $sheet->getCell([19, $row])->getValue()],
            ];
        }

        $this->info(count($results).' filas parseadas, '.count($skipped).' omitidas (fecha o premio mayor inválido).');

        if ($skipped) {
            $this->warn('Sorteos omitidos: '.implode(', ', $skipped));
        }

        if (empty($results)) {
            return self::SUCCESS;
        }

        if (! $this->confirm('¿Importar '.count($results).' resultados a lottery_results?', true)) {
            return self::SUCCESS;
        }

        $stored = (new CruzRojaScraper($lottery))->importHistorical($results);

        $this->info(count($stored).' resultados nuevos insertados ('.(count($results) - count($stored)).' ya existían para esa fecha).');

        return self::SUCCESS;
    }

    private function parseHistoricalDate(mixed $raw): ?Carbon
    {
        if (is_numeric($raw)) {
            $raw = (string) (int) $raw;
        }

        // Limpia typos del archivo original: guiones repetidos/al borde
        // ("11-03-03-", "17-06--2003").
        $value = trim(trim((string) $raw), '-');
        $value = preg_replace('/-+/', '-', $value);

        // DDMMYYYY sin separadores (caso aislado: sorteo 2143 → "31102006")
        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $value, $m)) {
            return $this->safeDate($m[3], $m[2], $m[1]);
        }

        // ISO: YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m)) {
            return $this->safeDate($m[1], $m[2], $m[3]);
        }

        // D-M-Y, D-M-YY o D-M-YYYYY con cero de más (con guiones, usado
        // ~2002-2007; "6-02-02007" → year casteado a int descarta el 0).
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2,5})$/', $value, $m)) {
            $year = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];

            return $this->safeDate($year, $m[2], $m[1]);
        }

        // Con slash: la mayoría del archivo (1965-2001) es M/D/Y, pero hay un
        // bloque de mediados de 2002 en D/M/Y — desambiguado cuando el
        // primer número no puede ser mes (>12).
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            [$first, $second, $year] = [(int) $m[1], (int) $m[2], $m[3]];

            return $first > 12
                ? $this->safeDate($year, $second, $first)
                : $this->safeDate($year, $first, $second);
        }

        return null;
    }

    private function safeDate(string $year, string $month, string $day): ?Carbon
    {
        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        try {
            return Carbon::createFromDate($year, $month, $day)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{number: string, serie: ?string}|null
     */
    private function parsePremio(mixed $raw): ?array
    {
        $value = trim((string) $raw);

        if ($value === '' || $value === '*' || $value === '-') {
            return null;
        }

        if (str_contains($value, '-')) {
            [$number, $serie] = array_map('trim', explode('-', $value, 2));

            return ['number' => $number, 'serie' => $serie ?: null];
        }

        return ['number' => $value, 'serie' => null];
    }
}
