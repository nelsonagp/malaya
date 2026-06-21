<?php

namespace App\Scrapers;

use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://loteriadelmeta.gov.co/resultados/ el 2026-06-21.
 * Plugin propio "loteria-meta-resultados" con clases reales (.lm-draw-title,
 * .lm-draw-date, .lm-pill). La fecha viene en formato numérico d/m/Y, no en
 * español, así que no usa ParsesSpanishDates.
 */
class LoteriaMetaScraper extends BaseLotteryScraper
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
        $header = $crawler->filter('.lm-draw-block')->first();

        if ($header->count() === 0) {
            return null;
        }

        $titleNode = $header->filter('.lm-draw-title')->first();
        $dateNode = $header->filter('.lm-draw-date')->first();
        $pills = $header->filter('.lm-pill');

        if ($dateNode->count() === 0 || $pills->count() < 2) {
            return null;
        }

        $drawDate = Carbon::createFromFormat('d/m/Y', trim($dateNode->text()));

        preg_match('/\d+/', $titleNode->text(), $drawNumberMatch);

        $jackpotAmount = null;
        $jackpotNode = $crawler->filter('.lm-grand-prize-img')->first();

        if ($jackpotNode->count() > 0 && preg_match('/[\d.,]+/', $jackpotNode->attr('alt') ?? '', $m)) {
            $jackpotAmount = (float) str_replace(['.', ','], '', $m[0]) * 1_000_000;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawNumberMatch[0] ?? null,
            'numbers' => [trim($pills->eq(0)->text()), trim($pills->eq(1)->text())],
            'jackpot_amount' => $jackpotAmount,
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
