<?php

use App\Jobs\RecalculateStatisticsJob;
use App\Jobs\ScrapeLotteryJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Loterías según horario oficial — ver PROMPT_CLAUDE_CODE_MALAYASEAMISUERTE.md > MÓDULO DE SCRAPING
// (twiceWeekly() no existe en esta versión de Laravel — se usa days()->at() en su lugar)
Schedule::job(new ScrapeLotteryJob('loteria-bogota'))->weeklyOn(6, '23:45')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('loteria-medellin'))->weeklyOn(5, '22:30')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('cruz-roja'))->weeklyOn(3, '22:30')->timezone('America/Bogota'); // día/hora estimados — el spec no especifica el horario de Cruz Roja, ajustar si no coincide
Schedule::job(new ScrapeLotteryJob('baloto'))->days([3, 6])->at('23:00')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('revancha-baloto'))->days([3, 6])->at('23:05')->timezone('America/Bogota'); // mismo sorteo que baloto, +5min para no pisar el mismo lock de scraping
Schedule::job(new ScrapeLotteryJob('mega-millions'))->days([2, 5])->at('23:00')->timezone('America/New_York');
Schedule::job(new ScrapeLotteryJob('powerball'))->days([1, 3, 6])->at('23:00')->timezone('America/New_York');

// Loterías regionales — horario y día de sorteo confirmados contra el HTML real
// scrapeado (no contra la spec, que tenía varios días equivocados), ver
// LotterySeeder::run() y la memoria del proyecto sobre el módulo de scraping.
Schedule::job(new ScrapeLotteryJob('loteria-cauca'))->weeklyOn(6, '23:15')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('loteria-manizales'))->weeklyOn(3, '22:45')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('loteria-huila'))->weeklyOn(2, '22:45')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('loteria-tolima'))->weeklyOn(2, '22:45')->timezone('America/Bogota');
Schedule::job(new ScrapeLotteryJob('loteria-meta'))->weeklyOn(3, '22:45')->timezone('America/Bogota');

// Estadísticas cada 2 horas
Schedule::job(new RecalculateStatisticsJob())->everyTwoHours();

// Sitemap.xml — ver PROMPT_CLAUDE_CODE_MALAYASEAMISUERTE.md > SEO técnico
Schedule::command('malaya:generar-sitemap')->daily();
