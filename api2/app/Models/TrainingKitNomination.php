<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingKitNomination extends Model
{
    protected $fillable = [
        'trainer_id',
        'training_kit_id',
        'proposed_name',
        'description',
        'sector',
        'category',
        'hours',
        'status',
        'decision_notes',
        'decided_at',
    ];

    protected $casts = [
        'hours' => 'integer',
        'decided_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function trainingKit(): BelongsTo
    {
        return $this->belongsTo(TrainingKit::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}