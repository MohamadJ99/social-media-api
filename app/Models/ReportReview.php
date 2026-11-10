<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportReview extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'reviewer_id',
        'previous_status',
        'new_status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'previous_status' => ReportStatus::class,
            'new_status' => ReportStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
