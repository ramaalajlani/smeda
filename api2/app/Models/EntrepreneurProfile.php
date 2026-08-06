<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntrepreneurProfile extends Model
{
    protected $fillable = [
        'full_name', 'governorate', 'phone', 'email', 'age',
        'education_level', 'specialization',
        'project_name', 'project_field', 'project_field_other', 'founding_year',
        'executive_summary', 'elevator_pitch',
        'readiness_stage', 'has_prototype', 'tested_with_users', 'testing_results',
        'problem_description', 'target_customers', 'differentiation', 'competitive_advantages',
        'team_size_range', 'team_roles',
        'technologies',
        'market_validation_methods', 'target_market', 'current_users_range', 'current_customers_range',
        'has_revenue', 'revenue_sources',
        'funding_sources', 'seeking_investment', 'investment_needed_range',
        'challenges',
        'jobs_3years_range', 'scalability_outside_syria',
        'support_needed',
        'previous_participation', 'additional_notes',
    ];

    protected $casts = [
        'has_prototype'             => 'boolean',
        'tested_with_users'         => 'boolean',
        'has_revenue'               => 'boolean',
        'seeking_investment'        => 'boolean',
        'competitive_advantages'    => 'array',
        'team_roles'                => 'array',
        'technologies'              => 'array',
        'market_validation_methods' => 'array',
        'revenue_sources'           => 'array',
        'funding_sources'           => 'array',
        'challenges'                => 'array',
        'support_needed'            => 'array',
        'previous_participation'    => 'array',
        'reviewed_at'               => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
