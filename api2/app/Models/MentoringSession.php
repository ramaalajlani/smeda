<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringSession extends Model
{
    protected $fillable = [
        'project_id', 'mentor_user_id', 'session_date', 'duration_minutes',
        'topic', 'notes', 'action_items', 'rating', 'status',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(IncubatedProject::class, 'project_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_user_id');
    }
}
