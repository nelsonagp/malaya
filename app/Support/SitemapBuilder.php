<?php

namespace App\Support;

use App\Models\Lottery;

class SitemapBuilder
{
    public static function build(): string
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('resultados.index'), 'priority' => '0.8'],
            ['loc' => route('estadisticas.index'), 'priority' => '0.7'],
            ['loc' => route('generador.show'), 'priority' => '0.6'],
            ['loc' => route('buscar.index'), 'priority' => '0.5'],
        ];

        Lottery::query()->where('is_active', true)->orderBy('display_order')->get()->each(function (Lottery $lottery) use (&$urls) {
            $urls[] = ['loc' => route('loteria.show', $lottery->slug), 'priority' => '0.9'];
            $urls[] = ['loc' => route('estadisticas.show', $lottery->slug), 'priority' => '0.7'];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
