<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://loteriademedellin.com.co/resultados/ el 2026-06-19.
 * El widget "lottery_jackpot" de Elementor trae el último resultado ya
 * renderizado en el HTML estático (a diferencia del widget
 * "lottery-last-result-winner", que carga por JS y aparece vacío/skeleton).
 */
class LoteriaMedellinScraper extends BaseLotteryScraper
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

        $drawIdNode = $crawler->filter('.elementor-lottery-jackpot-draw-id')->first();
        $dateNode = $crawler->filter('.elementor-lottery-jackpot-date')->first();
        $numberNode = $crawler->filter('.elementor-lottery-jackpot-winner:not(.elementor-lottery-jackpot-serie) .elementor-lottery-jackpot-number')->first();
        $serieNode = $crawler->filter('.elementor-lottery-jackpot-serie .elementor-lottery-jackpot-number')->first();

        if ($dateNode->count() === 0 || $numberNode->count() === 0) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($dateNode->text());

        if (! $drawDate) {
            return null;
        }

        $numbers = array_filter([
            trim($numberNode->text()),
            $serieNode->count() > 0 ? trim($serieNode->text()) : null,
        ]);

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawIdNode->count() > 0 ? trim($drawIdNode->text()) : null,
            'numbers' => array_values($numbers),
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
