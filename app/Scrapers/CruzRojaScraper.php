<?php

namespace App\Scrapers;

use App\Scrapers\Concerns\ParsesSpanishDates;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * lotecruz.org.co (dominio actual, confirmado por el usuario el 2026-06-20 —
 * el de la ficha original, loteriadelacruzroja.com.co, no resuelve) solo
 * publica el resultado del día como una imagen dentro de /resultados/, sin
 * datos en el HTML. Cada sorteo semanal sí tiene un PDF oficial ("Acta") con
 * texto seleccionable, listado en /resultados-ano-{año}/. Verificado contra
 * Acta3158.pdf (sorteo 3158, 16/06/2026). El dominio no está detrás de
 * Cloudflare, así que Guzzle normal (getHtml) funciona tanto para la página
 * de listado como para descargar el PDF.
 */
class CruzRojaScraper extends BaseLotteryScraper
{
    use ParsesSpanishDates;

    public function fetchLatestResults(): array
    {
        $pdfUrl = $this->findLatestActaUrl();

        if (! $pdfUrl) {
            return [];
        }

        $result = $this->fetchActaFromUrl($pdfUrl);

        return $result ? [$result] : [];
    }

    /**
     * Descarga y parsea un PDF de acta puntual. Público porque también lo
     * usa el comando de importación histórica (`lottery:import-cruzroja-actas`)
     * para recorrer todas las actas de un año, no solo la última.
     */
    public function fetchActaFromUrl(string $pdfUrl): ?array
    {
        $text = (new PdfParser())->parseContent($this->getHtml($pdfUrl))->getText();
        $result = $this->parseResult($text);

        if ($result) {
            $result['source_url'] = $pdfUrl;
        }

        return $result;
    }

    /**
     * Todos los enlaces a PDFs de acta en /resultados-ano-{año}/, en el
     * orden en que aparecen en la página (cronológico).
     */
    public function findActaUrlsForYear(int $year): array
    {
        $html = $this->getHtml("https://lotecruz.org.co/resultados-ano-{$year}/");
        $crawler = new Crawler($html);

        $links = $crawler->filter('a')->each(fn (Crawler $node) => $node->attr('href'));

        return array_values(array_filter(
            $links,
            fn (?string $href) => $href && str_contains($href, 'wp-content/uploads') && str_ends_with(strtolower($href), '.pdf')
        ));
    }

    /**
     * Busca el último enlace a un PDF de acta en la página de resultados del
     * año actual (o del anterior, si todavía no hay actas para el año en
     * curso, p.ej. en enero).
     */
    protected function findLatestActaUrl(): ?string
    {
        foreach ([now()->year, now()->year - 1] as $year) {
            $pdfLinks = $this->findActaUrlsForYear($year);

            if (! empty($pdfLinks)) {
                return end($pdfLinks);
            }
        }

        return null;
    }

    public function parseResult(string $text): ?array
    {
        if (! preg_match('/sorteo No\.(\d+)/u', $text, $drawMatch)) {
            return null;
        }

        if (! preg_match('/a los (\d{1,2}) dias del mes de ([A-Za-zÁÉÍÓÚáéíóú]+) de (\d{4})/u', $text, $dateMatch)) {
            return null;
        }

        $drawDate = $this->parseSpanishDate("{$dateMatch[1]} de {$dateMatch[2]} de {$dateMatch[3]}");

        if (! $drawDate) {
            return null;
        }

        if (! preg_match('/PREMIO MAYOR\s+([\d.,]+)\s*MILLONES\s*\n(\d{4,})\s+(\d{2,3})/u', $text, $premioMatch)) {
            return null;
        }

        return [
            'draw_date' => $drawDate->toDateString(),
            'draw_number' => $drawMatch[1],
            'numbers' => [$premioMatch[2], $premioMatch[3]],
            'jackpot_amount' => ((float) str_replace(['.', ','], '', $premioMatch[1])) * 1_000_000,
            'currency' => 'COP',
        ];
    }
}
