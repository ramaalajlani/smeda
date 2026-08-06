<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraineeRegistrationRequest extends Model
{
    protected $fillable = [
        'request_number',
        'full_name',
        'national_id',
        'phone',
        'email',
        'city',
        'address',
        'birth_date',
        'gender',
        'education_level',
        'registration_mode',
        'guardian_name',
        'guardian_phone',
        'guardian_national_id',
        'group_name',
        'submitted_by_user_id',
        'status',
        'reviewed_by_user_id',
        'review_notes',
        'approved_trainee_id',
        'approved_at',
        'rejected_at',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedTrainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class, 'approved_trainee_id');
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
                ->orWhere('city', 'like', "%{$value}%")
                ->orWhere('education_level', 'like', "%{$value}%")
                ->orWhere('guardian_name', 'like', "%{$value}%")
                ->orWhere('group_name', 'like', "%{$value}%");
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