<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncubatedProject extends Model
{
    protected $fillable = [
        'application_id', 'incubator_id', 'owner_user_id', 'mentor_user_id',
        'project_name', 'stage', 'start_date', 'expected_end_date',
        'actual_end_date', 'status', 'current_revenue', 'current_employees', 'notes',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'expected_end_date' => 'date',
        'actual_end_date'   => 'date',
        'current_revenue'   => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(IncubationApplication::class);
    }

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_user_id');
    }

    public function mentoringSessions(): HasMany
    {
        return $this->hasMany(MentoringSession::class, 'project_id');
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(IncubationProgressReport::class, 'project_id');
    }
}
