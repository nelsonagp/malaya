<?php

namespace App\Console\Commands;

use App\Models\Lottery;
use App\Scrapers\CruzRojaScraper;
use Illuminate\Console\Command;

/**
 * Importa todas las actas en PDF disponibles para un año en
 * /resultados-ano-{año}/ de lotecruz.org.co. Solo cubre años donde el sitio
 * publicó actas en PDF (con texto seleccionable) en vez de fotos del
 * boletín: confirmado 2026-06-20 que 2019-2025 son solo imágenes (sin datos
 * extraíbles) y que 2018 ni siquiera tiene página (404) — por ahora esto
 * solo sirve para 2026 en adelante.
 */
class ImportCruzRojaActas extends Command
{
    protected $signature = 'lottery:import-cruzroja-actas {year : Año a importar, ej. 2026}';

    protected $description = 'Importa todas las actas en PDF de un año de la Lotería de la Cruz Roja desde lotecruz.org.co';

    public function handle(): int
    {
        $year = (int) $this->argument('year');

        $lottery = Lottery::query()->where('slug', 'cruz-roja')->first();

        if (! $lottery) {
            $this->error('No existe la lotería con slug "cruz-roja". Corre el seeder primero.');

            return self::FAILURE;
        }

        $scraper = new CruzRojaScraper($lottery);
        $urls = $scraper->findActaUrlsForYear($year);

        if (empty($urls)) {
            $this->warn("No se encontraron PDFs de acta para {$year} (puede que ese año solo publique imágenes, o no exista la página).");

            return self::SUCCESS;
        }

        $this->info(count($urls)." PDFs encontrados para {$year}.");

        $results = [];
        $failed = [];

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        foreach ($urls as $url) {
            $result = $scraper->fetchActaFromUrl($url);

            if ($result) {
                $results[] = $result;
            } else {
                $failed[] = $url;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info(count($results).' actas parseadas, '.count($failed).' fallidas.');

        if ($failed) {
            $this->warn('No se pudieron parsear: '.implode(', ', $failed));
        }

        if (empty($results)) {
            return self::SUCCESS;
        }

        if (! $this->confirm('¿Importar '.count($results).' resultados a lottery_results?', true)) {
            return self::SUCCESS;
        }

        $stored = $scraper->importHistorical($results);

        $this->info(count($stored).' resultados nuevos insertados ('.(count($results) - count($stored)).' ya existían para esa fecha).');

        return self::SUCCESS;
    }
}
