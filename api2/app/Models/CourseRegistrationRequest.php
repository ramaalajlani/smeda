<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseRegistrationRequest extends Model
{
    protected $fillable = [
        'request_number',
        'training_course_id',
        'registration_mode',
        'submitted_by_user_id',
        'submitted_by_type',
        'applicant_name',
        'applicant_phone',
        'applicant_email',
        'guardian_name',
        'guardian_phone',
        'guardian_national_id',
        'notes',
        'status',
        'guardian_confirmed_at',
        'completed_at',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'guardian_confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CourseRegistrationRequestMember::class, 'course_registration_request_id');
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
                ->orWhere('applicant_name', 'like', "%{$value}%")
                ->orWhere('applicant_phone', 'like', "%{$value}%")
                ->orWhere('applicant_email', 'like', "%{$value}%")
                ->orWhere('guardian_name', 'like', "%{$value}%")
                ->orWhereHas('trainingCourse', function (Builder $courseQuery) use ($value) {
                    $courseQuery->where('title', 'like', "%{$value}%")
                        ->orWhere('course_code', 'like', "%{$value}%");
                });
        });
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGuardianConfirmed(): bool
    {
        return $this->status === 'guardian_confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}