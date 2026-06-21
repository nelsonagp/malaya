<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://www.loteriadebogota.com/resultados/ el 2026-06-19.
 * El sitio redirige www -> sin www. Solo muestra el último sorteo (sin lista
 * histórica en el HTML estático), así que basta tomar el primer match de
 * cada selector.
 *
 * Usa getHtmlViaBrowser() en vez de getHtml(): el sitio está detrás de
 * Cloudflare y bloquea el handshake TLS de PHP-curl/Guzzle (confirmado
 * 2026-06-20 — curl de terminal con Schannel pasa, PHP-curl con OpenSSL no,
 * sin importar los headers HTTP). Solo un navegador real (Playwright) pasa
 * el challenge.
 */
class LoteriaBogotaScraper extends BaseLotteryScraper
{
    use ParsesSpanishDates;

    public function fetchLatestResults(): array
    {
        $html = $this->getHtmlViaBrowser($this->lottery->results_url, '.resultado-sorteo');
        $result = $this->parseResult($html);

        return $result ? [$result] : [];
    }

    protected function parseResult(string $html): ?array
    {
        $crawler = new Crawler($html);

        $sorteoText = $crawler->filter('.resultado-sorteo')->first();
        $fechaText = $crawler->filter('.resultado-fecha')->first();
        $mayorRow = $crawler->filter('tbody.mayor tr')->first();

        if ($sorteoText->count() === 0 || $fechaText->count() === 0 || $mayorRow->count() === 0) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($fechaText->text());

        if (! $drawDate) {
            return null;
        }

        $numeroDigits = $mayorRow->filter('td')->eq(0)->filter('span')
            ->each(fn (Crawler $node) => trim($node->text()));

        $serie = $mayorRow->filter('td')->eq(1)->filter('span')
            ->each(fn (Crawler $node) => trim($node->text()));

        if (empty($numeroDigits)) {
            return null;
        }

        preg_match('/\d+/', $sorteoText->text(), $drawNumberMatch);

        $premioMayorText = $crawler->filter('.premiomayor2')->first();
        $jackpotAmount = null;

        if ($premioMayorText->count() > 0 && preg_match('/[\d.,]+/', $premioMayorText->text(), $amountMatch)) {
            $jackpotAmount = (float) str_replace(['.', ','], '', $amountMatch[0]) * 1_000_000;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawNumberMatch[0] ?? null,
            'numbers' => array_filter([implode('', $numeroDigits), $serie[0] ?? null]),
            'jackpot_amount' => $jackpotAmount,
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
