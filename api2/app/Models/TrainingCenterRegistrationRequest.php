<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TrainingCenterRegistrationRequest extends Model
{
    protected $fillable = [
        'request_number',

        'center_name',
        'city',
        'address',
        'phone',
        'email',

        'classification_requested',
        'supports_offline_training',
        'supports_online_training',

        'latitude',
        'longitude',

        'license_number',
        'license_issue_date',
        'license_issued_by',
        'license_image_path',

        'license_file',
        'accreditation_file',

        'notes',

        'submitted_by_user_id',
        'status',

        'reviewed_by_user_id',
        'review_notes',
        'decision_notes',
        'reviewed_at',

        'approved_training_center_id',
        'approved_at',
        'rejected_at',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'supports_offline_training' => 'boolean',
        'supports_online_training' => 'boolean',

        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',

        'license_issue_date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $appends = [
        'license_image_url',
    ];

    /*
    |----------------------------------------------------------------------
    | Relationships
    |----------------------------------------------------------------------
    */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedTrainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'approved_training_center_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /*
    |----------------------------------------------------------------------
    | Accessors
    |----------------------------------------------------------------------
    */
    public function getLicenseImageUrlAttribute(): ?string
    {
        if (blank($this->license_image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->license_image_path);
    }

    /*
    |----------------------------------------------------------------------
    | Scopes
    |----------------------------------------------------------------------
    */
    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('request_number', 'like', "%{$value}%")
                ->orWhere('center_name', 'like', "%{$value}%")
                ->orWhere('city', 'like', "%{$value}%")
                ->orWhere('address', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%")
                ->orWhere('classification_requested', 'like', "%{$value}%")
                ->orWhere('license_number', 'like', "%{$value}%")
                ->orWhere('license_issued_by', 'like', "%{$value}%");
        });
    }

    /*
    |----------------------------------------------------------------------
    | Status helpers
    |----------------------------------------------------------------------
    */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}