<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncubationProgressReport extends Model
{
    protected $fillable = [
        'project_id', 'submitted_by', 'period_type', 'period_label',
        'revenue', 'employees', 'customers', 'achievements',
        'challenges', 'next_steps', 'overall_rating',
    ];

    protected $casts = [
        'revenue' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(IncubatedProject::class, 'project_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
