<?php

namespace App\Scrapers;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Verificado contra https://www.loteriadelcauca.gov.co/ el 2026-06-21.
 * La subpágina /la-loteria/ultimos-resultados (que es el results_url de la
 * spec) no trae el resultado embebido: solo enlaza a una herramienta externa
 * (ingesoftingenieria.com/resultados/) que muestra una fecha fija
 * "0000-00-00" y un sorteo desactualizado — no sirve para "último
 * resultado". El dato real está en el slider de la portada.
 */
class LoteriaCaucaScraper extends BaseLotteryScraper
{
    public function fetchLatestResults(): array
    {
        $html = $this->getHtml('https://www.loteriadelcauca.gov.co/');
        $result = $this->parseResult($html);

        return $result ? [$result] : [];
    }

    protected function parseResult(string $html): ?array
    {
        $crawler = new Crawler($html);
        $slide = $crawler->filter('.carousel-item.active')->first();

        if ($slide->count() === 0) {
            return null;
        }

        $sorteoNode = $slide->filter('.serie-info .value')->eq(0);
        $fechaNode = $slide->filter('.serie-info .value')->eq(1);
        $digitNodes = $slide->filter('.number-box .number-item');
        $serieNode = $slide->filter('.serie-box .number-item')->first();

        if ($fechaNode->count() === 0 || $digitNodes->count() === 0) {
            return null;
        }

        $numero = implode('', $digitNodes->each(fn (Crawler $n) => trim($n->text())));

        $jackpotAmount = null;
        $jackpotNode = $slide->filter('.slide-title')->first();

        if ($jackpotNode->count() > 0 && preg_match('/[\d.,]+/', $jackpotNode->text(), $m)) {
            $jackpotAmount = (float) str_replace(['.', ','], '', $m[0]) * 1_000_000;
        }

        return [
            'draw_date' => trim($fechaNode->text()),
            'draw_number' => $sorteoNode->count() > 0 ? trim($sorteoNode->text()) : null,
            'numbers' => array_values(array_filter([$numero, $serieNode->count() > 0 ? trim($serieNode->text()) : null])),
            'jackpot_amount' => $jackpotAmount,
            'currency' => 'COP',
            'source_url' => 'https://www.loteriadelcauca.gov.co/',
        ];
    }
}
