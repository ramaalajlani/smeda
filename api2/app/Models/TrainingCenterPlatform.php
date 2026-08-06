<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCenterPlatform extends Model
{
    protected $fillable = [
        'training_center_id',
        'platform_name',
        'platform_url',
        'status',
        'approved_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'date',
        'expires_at' => 'date',
    ];

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function getIsValidAttribute(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        if (is_null($this->expires_at)) {
            return true;
        }

        return $this->expires_at->isFuture() || $this->expires_at->isToday();
    }
}