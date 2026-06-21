<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra
 * https://loteriadelhuila.com/resultado-del-ultimo-sorteo-loteria-del-huila-sitio-oficial/
 * el 2026-06-21 (no la portada loteriadelhuila.com — el mismo bloque ahí
 * está armado con divs de estilo inline sin clases; esta subpágina lo
 * renderiza con clases reales del plugin "lottery-rewards": .lr-pmayor,
 * .lr-numero, .lr-fecha).
 */
class LoteriaHuilaScraper extends BaseLotteryScraper
{
    use ParsesSpanishDates;

    public function fetchLatestResults(): array
    {
        $html = $this->getHtml($this->lottery->results_url);
        $result = $this->parseResult($html);

        return $result ? [$result] : [];
    }

    protected function parseResult(string $html): ?array
    {
        $crawler = new Crawler($html);
        $block = $crawler->filter('.lr-pmayor')->first();

        if ($block->count() === 0) {
            return null;
        }

        $numeroNode = $block->filter('.lr-numero')->first();
        $fechaNode = $block->filter('.lr-fecha')->first();
        $premioCells = $block->filter('.lr-rmayor table td');

        if ($fechaNode->count() === 0 || $premioCells->count() < 2) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($fechaNode->text());

        if (! $drawDate) {
            return null;
        }

        $digits = $premioCells->each(function (Crawler $cell) {
            preg_match('/^\d+/', trim($cell->text()), $m);

            return $m[0] ?? null;
        });

        if (empty($digits[0])) {
            return null;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $numeroNode->count() > 0 ? trim($numeroNode->text()) : null,
            'numbers' => array_values(array_filter($digits)),
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
