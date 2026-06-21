<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Scrapers\ScraperFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeLotteryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(public string $lotterySlug)
    {
    }

    public function handle(): void
    {
        $lottery = Lottery::where('slug', $this->lotterySlug)->where('is_active', true)->first();

        if (! $lottery) {
            return;
        }

        ScraperFactory::make($lottery)->run();
    }
}
