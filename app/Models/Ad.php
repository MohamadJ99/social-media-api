<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('title', 'description','advertiser_name',
'advertiser_logo_path', 'image_path', 'target_url',
'is_active','starts_at', 'ends_at')]
class Ad extends Model
{
    use SoftDeletes;


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Scope: Active advertisements.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Advertisements within their schedule.
     */
    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope: Advertisements available for display.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->active()
            ->currentlyRunning();
    }
}