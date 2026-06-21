<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lottery extends Model
{
    use HasUuid;

    private const DIAS_ES = [
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miércoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sábado',
        'sunday' => 'domingo',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scraper_config' => 'array',
            'draw_schedule' => 'array',
            'has_series' => 'boolean',
            'has_fractions' => 'boolean',
            'is_active' => 'boolean',
            'last_scraped_at' => 'datetime',
        ];
    }

    public function results(): HasMany
    {
        return $this->hasMany(LotteryResult::class);
    }

    public function scrapeLogs(): HasMany
    {
        return $this->hasMany(ScrapeLog::class);
    }

    public function numberStatistics(): HasMany
    {
        return $this->hasMany(NumberStatistic::class);
    }

    public function nextDrawAt(): ?CarbonImmutable
    {
        $schedule = $this->draw_schedule;

        if (! $schedule || empty($schedule['days']) || empty($schedule['time'])) {
            return null;
        }

        $timezone = $schedule['timezone'] ?? 'America/Bogota';
        $days = array_map('strtolower', $schedule['days']);
        [$hour, $minute] = array_pad(explode(':', $schedule['time']), 2, 0);
        $now = CarbonImmutable::now($timezone);

        for ($i = 0; $i <= 7; $i++) {
            $candidate = $now->addDays($i)->setTime((int) $hour, (int) $minute, 0);

            if (in_array(strtolower($candidate->englishDayOfWeek), $days, true) && $candidate->greaterThan($now)) {
                return $candidate;
            }
        }

        return null;
    }

    public function drawScheduleLabel(): ?string
    {
        $schedule = $this->draw_schedule;

        if (! $schedule || empty($schedule['days'])) {
            return null;
        }

        $dias = collect($schedule['days'])
            ->map(fn ($dia) => self::DIAS_ES[strtolower($dia)] ?? $dia)
            ->join(', ', ' y ');

        $hora = isset($schedule['time'])
            ? CarbonImmutable::createFromFormat('H:i', $schedule['time'])->translatedFormat('h:i A')
            : null;

        return trim($dias . ($hora ? ", {$hora}" : ''));
    }

    public function drawFrequencyLabel(): string
    {
        return match ($this->draw_frequency) {
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'biweekly' => 'Dos veces por semana',
            'monthly' => 'Mensual',
            default => $this->draw_frequency ?? 'No definida',
        };
    }
}
