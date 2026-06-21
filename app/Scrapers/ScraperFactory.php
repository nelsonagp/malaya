<?php

namespace App\Scrapers;

use App\Models\Lottery;
use InvalidArgumentException;

class ScraperFactory
{
    public static function make(Lottery $lottery): BaseLotteryScraper
    {
        $registered = config('scrapers.registered', []);
        $scraperKey = $lottery->scraper_class;

        if (! $scraperKey || ! isset($registered[$scraperKey])) {
            throw new InvalidArgumentException(
                "La lotería \"{$lottery->name}\" no tiene un scraper registrado en config/scrapers.php."
            );
        }

        $class = $registered[$scraperKey];

        return new $class($lottery);
    }
}
