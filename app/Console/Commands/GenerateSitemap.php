<?php

namespace App\Console\Commands;

use App\Support\SitemapBuilder;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'malaya:generar-sitemap';

    protected $description = 'Regenera public/sitemap.xml con las páginas públicas vigentes';

    public function handle(): int
    {
        file_put_contents(public_path('sitemap.xml'), SitemapBuilder::build());

        $this->info('sitemap.xml regenerado correctamente.');

        return self::SUCCESS;
    }
}
