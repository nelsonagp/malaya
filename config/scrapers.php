<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scrapers registrados
    |--------------------------------------------------------------------------
    |
    | Mapeo "clase de scraper" => clase PHP en App\Scrapers. Se llena cuando
    | se construya el módulo de scraping (ver PROMPT_CLAUDE_CODE_MALAYASEAMISUERTE.md
    | > MÓDULO DE SCRAPING). Por ahora el admin puede registrar el nombre de
    | la clase en la lotería, pero "Probar scraper" / "Forzar scraping" no
    | estarán disponibles hasta que existan clases aquí.
    |
    */

    'registered' => [
        'LoteriaBogotaScraper' => \App\Scrapers\LoteriaBogotaScraper::class,
        'LoteriaMedellinScraper' => \App\Scrapers\LoteriaMedellinScraper::class,
        'CruzRojaScraper' => \App\Scrapers\CruzRojaScraper::class,
        'BalotoScraper' => \App\Scrapers\BalotoScraper::class,
        'MegaMillionsScraper' => \App\Scrapers\MegaMillionsScraper::class,
        'PowerballScraper' => \App\Scrapers\PowerballScraper::class,
        'LoteriaManizalesScraper' => \App\Scrapers\LoteriaManizalesScraper::class,
        'LoteriaCaucaScraper' => \App\Scrapers\LoteriaCaucaScraper::class,
        'LoteriaHuilaScraper' => \App\Scrapers\LoteriaHuilaScraper::class,
        'LoteriaTolimaScraper' => \App\Scrapers\LoteriaTolimaScraper::class,
        'LoteriaMetaScraper' => \App\Scrapers\LoteriaMetaScraper::class,

        // Cundinamarca: pendiente — su sitio oficial es una SPA Angular que solo
        // expone un formulario "¿ganaste?" (número/serie/sorteo a verificar),
        // sin ningún HTML ni endpoint que muestre el número ganador directamente.
        // Ver historial de scraping en memoria del proyecto antes de reintentar.
    ],

];
