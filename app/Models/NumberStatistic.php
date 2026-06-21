<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberStatistic extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_appeared_date' => 'date',
            'total_appearances' => 'integer',
            'days_since_last_appearance' => 'integer',
            'appearance_frequency' => 'decimal:4',
            'updated_at' => 'datetime',
        ];
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }
}
