<?php

namespace App\Scrapers;

use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://www.powerball.com/ el 2026-06-19. La página
 * /winning-numbers devolvió 404 al revisarla; el resultado más reciente
 * está en la home, dentro de .number-card.number-powerball.complete.
 */
class PowerballScraper extends BaseLotteryScraper
{
    public function fetchLatestResults(): array
    {
        $html = $this->getHtml($this->lottery->results_url);
        $result = $this->parseResult($html);

        return $result ? [$result] : [];
    }

    protected function parseResult(string $html): ?array
    {
        $crawler = new Crawler($html);

        $card = $crawler->filter('.number-card.number-powerball')->first();

        if ($card->count() === 0) {
            return null;
        }

        $dateNode = $card->filter('.title-date')->first();

        if ($dateNode->count() === 0) {
            return null;
        }

        $drawDate = Carbon::createFromFormat('D, M j, Y', trim($dateNode->text()));

        $whiteBalls = $card->filter('.white-balls div')->each(fn (Crawler $node) => trim($node->text()));
        $powerball = $card->filter('.item-powerball.powerball div')->each(fn (Crawler $node) => trim($node->text()));

        if (empty($whiteBalls)) {
            return null;
        }

        $multiplierNode = $crawler->filter('.power-play .multiplier')->first();

        return [
            'draw_date' => $drawDate->toDateString(),
            'numbers' => array_values(array_merge($whiteBalls, $powerball)),
            'prize_breakdown' => $multiplierNode->count() > 0
                ? ['power_play' => trim($multiplierNode->text())]
                : null,
            'currency' => 'USD',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
