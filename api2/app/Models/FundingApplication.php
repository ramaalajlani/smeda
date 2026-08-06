<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FundingApplication extends Model
{
    protected $fillable = [
        'application_number', 'applicant_user_id', 'applicant_name', 'national_id', 'phone', 'email',
        'governorate_id', 'branch_id', 'project_name', 'project_type', 'project_sector', 'project_size',
        'business_stage', 'requested_amount', 'currency', 'financing_type', 'repayment_period_months',
        'purpose', 'description', 'status', 'current_stage', 'submitted_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasOne
    {
        return $this->hasOne(FundingApplicationDetail::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FundingDocument::class);
    }

    public function consultantAssignments(): HasMany
    {
        return $this->hasMany(ConsultantAssignment::class);
    }

    public function consultantReports(): HasMany
    {
        return $this->hasMany(ConsultantReport::class);
    }

    public function partnerAssignments(): HasMany
    {
        return $this->hasMany(FundingPartnerAssignment::class);
    }

    public function fundedLoans(): HasMany
    {
        return $this->hasMany(FundedLoan::class);
    }
}
