<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Scraper genérico para sitios cuyo HTML real no se pudo verificar en vivo
 * todavía (ver scraper_error de la lotería tras "Probar scraper"). Lee los
 * selectores CSS desde lotteries.scraper_config en vez de tenerlos
 * hardcodeados, así que se puede activar sin tocar código una vez que se
 * confirme la estructura real del sitio. Claves esperadas en scraper_config:
 *
 *   {
 *     "date_selector": "...",       // requerido — nodo con la fecha del sorteo
 *     "date_format": "d/m/Y",       // opcional — formato Carbon; si se omite, intenta fechas en español
 *     "numbers_selector": "...",    // requerido — selector que matchea un nodo por número
 *     "draw_number_selector": "..." // opcional
 *   }
 */
class ConfigurableScraper extends BaseLotteryScraper
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
        $config = $this->lottery->scraper_config ?? [];

        if (empty($config['date_selector']) || empty($config['numbers_selector'])) {
            throw new RuntimeException(
                "scraper_config de \"{$this->lottery->name}\" no tiene date_selector/numbers_selector. ".
                'Configúralo desde el admin una vez que se verifique la estructura real del sitio.'
            );
        }

        $crawler = new Crawler($html);

        $dateNode = $crawler->filter($config['date_selector'])->first();

        if ($dateNode->count() === 0) {
            return null;
        }

        $drawDate = isset($config['date_format'])
            ? \Carbon\Carbon::createFromFormat($config['date_format'], trim($dateNode->text()))
            : $this->parseSpanishDate($dateNode->text());

        if (! $drawDate) {
            return null;
        }

        $numbers = $crawler->filter($config['numbers_selector'])->each(fn (Crawler $node) => trim($node->text()));

        if (empty($numbers)) {
            return null;
        }

        $drawNumber = null;

        if (! empty($config['draw_number_selector'])) {
            $drawNumberNode = $crawler->filter($config['draw_number_selector'])->first();
            $drawNumber = $drawNumberNode->count() > 0 ? trim($drawNumberNode->text()) : null;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawNumber,
            'numbers' => array_values($numbers),
            'currency' => $config['currency'] ?? 'COP',
            'source_url' => $this->lottery->results_url,
        ];
    }
}
