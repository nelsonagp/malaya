<?php

namespace App\Jobs;

use App\Models\Lottery;
use App\Models\NumberStatistic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateStatisticsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Lottery::all()->each(function (Lottery $lottery) {
            $totalDraws = $lottery->results()->count();

            if ($totalDraws === 0) {
                return;
            }

            $lottery->numberStatistics()->each(function (NumberStatistic $stat) use ($totalDraws) {
                $stat->update([
                    'appearance_frequency' => round($stat->total_appearances / $totalDraws, 4),
                    'days_since_last_appearance' => $stat->last_appeared_date
                        ? $stat->last_appeared_date->diffInDays(now())
                        : null,
                    'updated_at' => now(),
                ]);
            });
        });
    }
}
