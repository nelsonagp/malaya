<?php

namespace App\Scrapers\Concerns;

use Carbon\Carbon;

trait ParsesSpanishDates
{
    private const MONTHS = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
        'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
    ];

    /**
     * Convierte fechas en español como "18 de junio 2026", "12/Junio/2026" o
     * "Miércoles 17 de Junio de 2026" a Carbon. Devuelve null si no matchea.
     */
    protected function parseSpanishDate(string $text): ?Carbon
    {
        $text = trim($text);

        if (preg_match('/(\d{1,2})\s*\/\s*([a-záéíóú]+)\s*\/\s*(\d{4})/iu', $text, $m)) {
            return $this->buildDate($m[3], $m[2], $m[1]);
        }

        if (preg_match('/(\d{1,2})\s+de\s+([a-záéíóú]+)(?:\s+de)?\s+(\d{4})/iu', $text, $m)) {
            return $this->buildDate($m[3], $m[2], $m[1]);
        }

        return null;
    }

    private function buildDate(string $year, string $monthName, string $day): ?Carbon
    {
        $month = self::MONTHS[mb_strtolower($monthName)] ?? null;

        if (! $month) {
            return null;
        }

        return Carbon::createFromDate((int) $year, $month, (int) $day)->startOfDay();
    }
}
