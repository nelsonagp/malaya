<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;

/**
 * Verificado contra https://loteriademanizales.com/ el 2026-06-21.
 * La página /consulta-resultados/ (results_url de la spec) solo trae un
 * formulario "¿ganaste?" (número + serie + sorteo) vía un iframe a
 * api.loteriademanizales.com — no expone el número ganador, solo permite
 * verificar un número dado. El resultado real vive en la portada, pero ahí
 * el número está renderizado dígito por dígito en 7 widgets "flip-box" de
 * Elementor sin marcador semántico entre número y serie, así que se parsea
 * por posición vía regex en vez de DOM: los primeros 4 dígitos son el
 * número (number_count=4) y los últimos 3 la serie, igual que el resto de
 * loterías colombianas de 4+3 en este proyecto.
 */
class LoteriaManizalesScraper extends BaseLotteryScraper
{
    use ParsesSpanishDates;

    public function fetchLatestResults(): array
    {
        $html = $this->getHtml('https://loteriademanizales.com/');
        $result = $this->parseResult($html);

        return $result ? [$result] : [];
    }

    protected function parseResult(string $html): ?array
    {
        if (! preg_match('/icono sorteo.*?elementor-heading-title[^>]*>\s*(\d+)\s*</s', $html, $drawMatch)) {
            return null;
        }

        if (! preg_match('/Resultados<b>\s*(.+?)\s*<\/b>/iu', $html, $dateMatch)) {
            return null;
        }

        $drawDate = $this->parseSpanishDate($dateMatch[1]);

        if (! $drawDate) {
            return null;
        }

        preg_match_all(
            '/elementor-flip-box__front">.*?elementor-flip-box__layer__title">\s*(\d)\s*<\/h2>/s',
            $html,
            $digitMatches
        );

        $digits = $digitMatches[1] ?? [];

        if (count($digits) < 4) {
            return null;
        }

        $numero = implode('', array_slice($digits, 0, 4));
        $serie = implode('', array_slice($digits, 4, 3));

        $jackpotAmount = null;

        if (preg_match('/Premio Mayor\s*<b>\$\s*([\d.,]+)\s*Millones/iu', $html, $jackpotMatch)) {
            $jackpotAmount = (float) str_replace(['.', ','], '', $jackpotMatch[1]) * 1_000_000;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawMatch[1],
            'numbers' => array_values(array_filter([$numero, $serie])),
            'jackpot_amount' => $jackpotAmount,
            'currency' => 'COP',
            'source_url' => 'https://loteriademanizales.com/',
        ];
    }
}
