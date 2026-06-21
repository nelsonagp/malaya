<?php

namespace App\Scrapers;

use App\Models\Lottery;
use App\Models\LotteryResult;
use App\Models\NumberStatistic;
use App\Models\ScrapeLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

abstract class BaseLotteryScraper
{
    protected const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    protected const MAX_ATTEMPTS = 3;

    protected const REQUEST_TIMEOUT = 30;

    public function __construct(protected Lottery $lottery)
    {
    }

    /**
     * Obtiene el/los resultados más recientes desde la fuente. Devuelve un
     * array de filas listas para guardar (ver storeResults()).
     */
    abstract public function fetchLatestResults(): array;

    /**
     * Extrae el resultado más reciente a partir del HTML de la página de
     * resultados. Devuelve null si no se pudo encontrar/parsear nada.
     */
    abstract protected function parseResult(string $html): ?array;

    /**
     * Ejecuta el scraping completo: descarga, parsea, guarda y deja
     * constancia en scrape_logs. Pensado para usarse desde el job o desde
     * el botón "Probar scraper" del admin (con $persist = false).
     */
    public function run(bool $persist = true): array
    {
        $log = $persist
            ? ScrapeLog::create([
                'lottery_id' => $this->lottery->id,
                'started_at' => now(),
                'status' => 'running',
            ])
            : null;

        try {
            $results = $this->fetchLatestResults();
            $stored = $persist ? $this->storeResults($results) : $results;

            if ($persist) {
                $this->lottery->update(['last_scraped_at' => now(), 'scrape_error' => null]);

                $log->update([
                    'finished_at' => now(),
                    'status' => 'success',
                    'results_found' => count($stored),
                ]);
            }

            return $stored;
        } catch (Throwable $e) {
            if ($persist) {
                $this->lottery->update(['scrape_error' => $e->getMessage()]);

                $log->update([
                    'finished_at' => now(),
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Descarga el HTML de una URL con reintentos y backoff exponencial.
     */
    protected function getHtml(string $url): string
    {
        $client = new Client([
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'es-CO,es;q=0.9,en;q=0.8',
            ],
        ]);

        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                return (string) $client->get($url)->getBody();
            } catch (Throwable $e) {
                $lastException = $e;

                if ($attempt < self::MAX_ATTEMPTS) {
                    usleep((2 ** $attempt) * 500_000); // 1s, 2s, 4s...
                }
            }
        }

        throw $lastException;
    }

    /**
     * Descarga el HTML usando un Chromium real (Playwright) en vez de
     * Guzzle. Necesario para sitios que bloquean las peticiones de
     * PHP-curl/Guzzle a nivel de fingerprint TLS (JA3) — Cloudflare deja
     * pasar el handshake de un navegador real pero no el de OpenSSL/curl,
     * sin importar qué headers HTTP se envíen.
     */
    protected function getHtmlViaBrowser(string $url, ?string $waitForSelector = null): string
    {
        $script = base_path('scripts/playwright-fetch.mjs');
        $args = array_filter(['node', $script, $url, $waitForSelector]);

        $result = Process::timeout(45)->run($args);

        if ($result->failed()) {
            throw new RuntimeException("Playwright fetch falló para {$url}: ".$result->errorOutput());
        }

        return $result->output();
    }

    /**
     * Punto de entrada público para importar resultados históricos (p.ej.
     * desde un comando de backfill) reusando el mismo guardado/dedupe/
     * estadísticas que usa el scraping en vivo.
     */
    public function importHistorical(array $results): array
    {
        return $this->storeResults($results);
    }

    /**
     * Guarda los resultados nuevos en lottery_results (ignora los que ya
     * existen para esa fecha) y actualiza las estadísticas de números.
     */
    protected function storeResults(array $results): array
    {
        $stored = [];

        foreach ($results as $result) {
            $alreadyExists = LotteryResult::query()
                ->where('lottery_id', $this->lottery->id)
                ->where('draw_date', $result['draw_date'])
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $created = LotteryResult::create([
                'lottery_id' => $this->lottery->id,
                'draw_date' => $result['draw_date'],
                'draw_number' => $result['draw_number'] ?? null,
                'numbers' => $result['numbers'],
                'prize_breakdown' => $result['prize_breakdown'] ?? null,
                'jackpot_amount' => $result['jackpot_amount'] ?? null,
                'currency' => $result['currency'] ?? 'COP',
                'source_url' => $result['source_url'] ?? $this->lottery->results_url,
                'raw_data' => $result['raw_data'] ?? null,
                'scraped_at' => now(),
            ]);

            $this->updateStatistics($this->lottery->id, $created->numbers, $created->draw_date->toDateString());
            $stored[] = $created;
        }

        return $stored;
    }

    /**
     * Incrementa el conteo de apariciones de cada número/serie del
     * resultado en number_statistics. Usa la fecha real del sorteo (no
     * "ahora") para last_appeared_date — necesario porque storeResults()
     * también se usa para importar resultados históricos vía
     * importHistorical(), donde "ahora" no tiene nada que ver con cuándo
     * salió el número. Solo actualiza last_appeared_date si esta fecha es
     * la más reciente vista hasta ahora para ese número, por si los
     * resultados se procesan fuera de orden cronológico.
     */
    protected function updateStatistics(string $lotteryId, array $numbers, string $drawDate): void
    {
        foreach ($numbers as $number) {
            $stat = NumberStatistic::query()->firstOrNew([
                'lottery_id' => $lotteryId,
                'number' => $number,
            ]);

            $stat->total_appearances = ($stat->total_appearances ?? 0) + 1;

            if (! $stat->last_appeared_date || $drawDate >= $stat->last_appeared_date->toDateString()) {
                $stat->last_appeared_date = $drawDate;
                $stat->days_since_last_appearance = (int) \Carbon\Carbon::parse($drawDate)->diffInDays(now());
            }

            $stat->updated_at = now();
            $stat->save();
        }
    }
}
