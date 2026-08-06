<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerRegistrationRequest extends Model
{
    protected $fillable = [
        'request_number',
        'training_center_id',
        'full_name',
        'national_id',
        'phone',
        'email',
        'specialization',
        'classification_requested',
        'has_tot',
        'tot_certificate_number',
        'tot_certificate_source',
        'tot_issue_date',
        'tot_expiry_date',
        'cv_file',
        'certificate_file',
        'submitted_by_user_id',
        'status',
        'reviewed_by_user_id',
        'review_notes',
        'approved_trainer_id',
        'approved_at',
        'rejected_at',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'has_tot' => 'boolean',
        'tot_issue_date' => 'date',
        'tot_expiry_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedTrainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'approved_trainer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('request_number', 'like', "%{$value}%")
                ->orWhere('full_name', 'like', "%{$value}%")
                ->orWhere('national_id', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%")
                ->orWhere('specialization', 'like', "%{$value}%")
                ->orWhere('classification_requested', 'like', "%{$value}%");
        });
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}