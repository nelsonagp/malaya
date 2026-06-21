<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryResult extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'draw_date' => 'date',
            'numbers' => 'array',
            'prize_breakdown' => 'array',
            'raw_data' => 'array',
            'jackpot_amount' => 'decimal:2',
            'is_verified' => 'boolean',
            'scraped_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }
}
