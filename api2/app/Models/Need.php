<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Need extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'need_code', 'title', 'description', 'summary',
        'need_owner_type', 'need_scope', 'need_type', 'need_category', 'need_complexity',
        'facility_type', 'facility_subtype', 'targeting_type', 'need_reason',
        'source_platform', 'source_module', 'source_record_id',
        'governorate_id', 'branch_id', 'district_name', 'administrative_unit_name',
        'countryside_name', 'locality_name', 'village_or_neighborhood', 'address_details',
        'latitude', 'longitude', 'location_source',
        'sector', 'economic_sector', 'syrsic_section', 'syrsic_division', 'syrsic_group', 'syrsic_class', 'syrsic_activity',
        'priority', 'status', 'approval_status',
        'state_need_level', 'citizen_need_profile', 'responsible_entity', 'proposed_intervention', 'public_benefit_score',
        'applicant_name', 'applicant_phone', 'applicant_email', 'applicant_type', 'organization_name',
        'beneficiaries_count', 'expected_jobs_count', 'expected_projects_count',
        'impact_level', 'urgency_level', 'expected_duration',
        'available_partners', 'obstacles', 'requirements', 'notes', 'metadata',
        'funding_application_id', 'training_course_id',
        'created_by', 'updated_by', 'reviewed_by', 'approved_by', 'rejected_by',
        'classified_by', 'returned_by', 'resolved_by',
        'reviewer_note', 'approval_note', 'rejection_reason', 'return_reason', 'classification_note',
        'reviewed_at', 'approved_at', 'rejected_at', 'classified_at', 'returned_at', 'resolved_at',
        'is_public', 'is_mapped',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'is_mapped' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'classified_at' => 'datetime',
        'returned_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function classifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'classified_by');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fundingApplication(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class);
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(NeedActionLog::class);
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(NeedSector::class, 'need_need_sector');
    }
}
