<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdBanner extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'click_count' => 'integer',
            'impression_count' => 'integer',
            'price_per_month' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today));
    }

    public function scopeForPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }
}
