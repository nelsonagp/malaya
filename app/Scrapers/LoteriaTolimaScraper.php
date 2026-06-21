<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;

/**
 * Verificado contra https://loteriadeltolima.com/resultados/ el 2026-06-21.
 * La página es HTML generado con estilos inline (sin clases CSS), así que
 * se parsea por regex anclado a los textos fijos "RESULTADOS SORTEO",
 * "NÚMERO"/"SERIE" y "PREMIO MAYOR" en vez de selectores DOM.
 */
class LoteriaTolimaScraper extends BaseLotteryScraper
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
        if (! preg_match('/RESULTADOS SORTEO\s+(\d+)/iu', $html, $drawMatch)) {
            return null;
        }

        if (! preg_match('/ULTIMO SORTEO\s*\/\s*\w+,\s*(\d{1,2}\s+DE\s+\w+\s+DE\s+\d{4})/iu', $html, $dateMatch)) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($dateMatch[1]);

        if (! $drawDate) {
            return null;
        }

        if (! preg_match('/PREMIO MAYOR[\s\S]*?NÚMERO<\/span>(\d+)<\/th>[\s\S]*?SERIE<\/span>(\d+)<\/th>/u', $html, $numbersMatch)) {
            return null;
        }

        $jackpotAmount = null;

        if (preg_match('/PREMIO MAYOR\s*([\d.,]+)\s*MILLONES/iu', $html, $jackpotMatch)) {
            $jackpotAmount = (float) str_replace(['.', ','], '', $jackpotMatch[1]) * 1_000_000;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawMatch[1],
            'numbers' => [$numbersMatch[1], $numbersMatch[2]],
            'jackpot_amount' => $jackpotAmount,
            'currency' => 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
