<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://www.baloto.com/resultados el 2026-06-19.
 * Una sola página muestra el último sorteo de Baloto Y de Revancha lado a
 * lado, dentro de #container-banner-results > .row > .col-md-5 (el primero
 * es Baloto, el segundo Revancha). Esta clase se registra para ambas
 * loterías (slugs "baloto" y "revancha-baloto") y decide cuál bloque leer
 * según $this->lottery->slug.
 */
class BalotoScraper extends BaseLotteryScraper
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

        $container = $crawler->filter('#container-banner-results')->first();

        if ($container->count() === 0) {
            return null;
        }

        $title = $container->filter('h2')->first();
        $dateText = $container->filter('h3')->first();

        if ($title->count() === 0 || $dateText->count() === 0) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($dateText->text());

        if (! $drawDate) {
            return null;
        }

        preg_match('/\d+/', $title->text(), $drawNumberMatch);

        $blockIndex = $this->lottery->slug === 'revancha-baloto' ? 1 : 0;
        $block = $container->filter('.col-md-5')->eq($blockIndex);

        if ($block->count() === 0) {
            return null;
        }

        $whiteNumbers = $block->filter('.yellow-ball')->each(fn (Crawler $node) => trim($node->text()));
        $superBalota = $block->filter('.red-ball')->each(fn (Crawler $node) => trim($node->text()));

        if (empty($whiteNumbers)) {
            return null;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawNumberMatch[0] ?? null,
            'numbers' => array_values(array_merge($whiteNumbers, $superBalota)),
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
