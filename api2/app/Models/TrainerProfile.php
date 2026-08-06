<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerProfile extends Model
{
    protected $fillable = [
        'trainer_id',
        'headline',
        'bio',
        'experience_years',
        'skills',
        'special_interests',
        'linkedin_summary',
        'cv_file',
        'profile_image',
        'visibility',
    ];

    protected $casts = [
        'experience_years' => 'integer',
    ];

    /**
     * Relationships
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}