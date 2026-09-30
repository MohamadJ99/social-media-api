<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'media_path',
    'media_disk',
    'media_type',
    'mime_type',
    'media_size',
    'width',
    'height',
    'duration',
    'caption',
    'visibility',
    'expires_at'
])]
class Story extends Model
{
    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_FRIENDS = 'friends';


    protected function casts(): array
    {
        return [
            'media_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'story_views',
            'story_id',
            'viewer_id'
        )
            ->withPivot('viewed_at')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'expires_at',
            '>',
            now()
        );
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(
            'expires_at',
            '<=',
            now()
        );
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
