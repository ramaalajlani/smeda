<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IncubationApplication extends Model
{
    protected $fillable = [
        'application_number', 'incubator_id', 'program_id', 'applicant_user_id',
        'project_name', 'project_sector', 'business_stage', 'project_description',
        'problem_statement', 'target_market', 'team_size', 'has_prototype',
        'has_revenue', 'status', 'reviewer_notes', 'reviewed_by', 'reviewed_at',
        // حقول مشتركة إضافية
        'funding_needed', 'funding_stage', 'expected_jobs', 'competitive_advantage',
        // حقول تقنية
        'tech_readiness_level', 'revenue_model', 'demo_url', 'github_url',
        'has_ip', 'ip_description', 'tech_stack', 'target_platform',
    ];

    protected $casts = [
        'has_prototype'         => 'boolean',
        'has_revenue'           => 'boolean',
        'has_ip'                => 'boolean',
        'tech_stack'            => 'array',
        'tech_readiness_level'  => 'integer',
        'funding_needed'        => 'decimal:2',
        'expected_jobs'         => 'integer',
        'reviewed_at'           => 'datetime',
    ];

    public function isTechApplication(): bool
    {
        return $this->incubator?->sector === 'tech';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->application_number)) {
                $model->application_number = 'INC-' . date('Y') . '-' . str_pad(
                    (self::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT
                );
            }
        });
    }

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(IncubationProgram::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function project(): HasOne
    {
        return $this->hasOne(IncubatedProject::class, 'application_id');
    }
}
