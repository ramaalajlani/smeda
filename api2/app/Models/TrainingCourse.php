<?php

namespace App\Models;

use App\Support\CertificateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCourse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'training_center_id',
        'trainer_id',
        'training_kit_id',
        'training_program_id',
        'course_code',
        'title',
        'delivery_mode',
        'approved_platform',
        'venue_name',
        'governorate',
        'city',
        'district',
        'address',
        'latitude',
        'longitude',
        'location_visibility',
        'online_platform',
        'online_url',
        'start_date',
        'end_date',
        'planned_hours',
        'actual_hours',
        'capacity',
        'status',
        'notes',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_hours' => 'integer',
        'actual_hours' => 'integer',
        'capacity' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */
    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function trainingKit(): BelongsTo
    {
        return $this->belongsTo(TrainingKit::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function trainees(): BelongsToMany
    {
        return $this->belongsToMany(
            Trainee::class,
            'training_course_trainee'
        )->withPivot([
            'attendance_status',
            'result',
            'score',
            'attended_hours',
            'notes',
            'course_group_id',
        ])->withTimestamps();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(CourseGroup::class, 'training_course_id')->orderBy('id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'training_course_id')
            ->orderBy('session_date')
            ->orderBy('session_no');
    }

    public function moduleScores(): HasMany
    {
        return $this->hasMany(TraineeModuleScore::class, 'training_course_id');
    }

    /**
     * =========================
     * Query Scopes
     * =========================
     */
    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('title', 'like', "%{$value}%")
                ->orWhere('course_code', 'like', "%{$value}%")
                ->orWhere('delivery_mode', 'like', "%{$value}%")
                ->orWhere('status', 'like', "%{$value}%")
                ->orWhere('approved_platform', 'like', "%{$value}%")
                ->orWhere('notes', 'like', "%{$value}%")
                ->orWhereHas('trainer', function (Builder $trainerQuery) use ($value) {
                    $trainerQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('trainer_code', 'like', "%{$value}%");
                })
                ->orWhereHas('trainingCenter', function (Builder $centerQuery) use ($value) {
                    $centerQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%")
                        ->orWhere('city', 'like', "%{$value}%");
                })
                ->orWhereHas('trainingKit', function (Builder $kitQuery) use ($value) {
                    $kitQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                })
                ->orWhereHas('trainingProgram', function (Builder $programQuery) use ($value) {
                    $programQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                });
        });
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereNotNull('start_date')
            ->whereDate('start_date', '>', now()->toDateString());
    }

    public function scopeOngoing(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $q) use ($today) {
            $q->where('status', 'ongoing')
              ->orWhere(function (Builder $inner) use ($today) {
                  $inner->whereNotNull('start_date')
                      ->whereNotNull('end_date')
                      ->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
              });
        });
    }

    /**
     * =========================
     * Accessors
     * =========================
     */
    public function getActualHoursResolvedAttribute(): int
    {
        return (int) ($this->actual_hours ?: $this->planned_hours ?: 0);
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->delivery_mode === 'online';
    }

    public function getIsOfflineAttribute(): bool
    {
        return $this->delivery_mode === 'offline';
    }

    public function getIsHybridAttribute(): bool
    {
        return false;
    }

    public function getHasApprovedPlatformAttribute(): bool
    {
        if (!$this->is_online) {
            return true;
        }

        return !empty($this->approved_platform);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getRegisteredTraineesCountAttribute(): int
    {
        if (array_key_exists('trainees_count', $this->attributes)) {
            return (int) $this->attributes['trainees_count'];
        }

        return $this->trainees()->count();
    }

    public function getRemainingCapacityAttribute(): int
    {
        $remaining = (int) $this->capacity - (int) $this->registered_trainees_count;

        return max(0, $remaining);
    }

    public function getHasAvailableCapacityAttribute(): bool
    {
        if ((int) $this->capacity <= 0) {
            return false;
        }

        return $this->remaining_capacity > 0;
    }

    /**
     * =========================
     * Helper Methods
     * =========================
     */
    public function hasTrainee(int|string|null $traineeId): bool
    {
        if (blank($traineeId)) {
            return false;
        }

        return $this->trainees()
            ->where('trainees.id', $traineeId)
            ->exists();
    }

    public function hasCertificateForTrainee(int|string|null $traineeId, ?string $certificateType = null): bool
    {
        if (blank($traineeId)) {
            return false;
        }

        $query = $this->certificates()
            ->where('trainee_id', $traineeId)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($certificateType !== null) {
            $normalizedType = CertificateType::normalize($certificateType);
            $query->where(function (Builder $q) use ($normalizedType) {
                $q->where('certificate_type', $normalizedType);

                if ($normalizedType === CertificateType::COMPLETION) {
                    $q->orWhere('certificate_type', 'pass');
                }
            });
        }

        return $query->exists();
    }

    public function hasApprovedCertificateForTrainee(int|string|null $traineeId): bool
    {
        if (blank($traineeId)) {
            return false;
        }

        return $this->certificates()
            ->where('trainee_id', $traineeId)
            ->where('status', 'approved')
            ->exists();
    }

    public function getResolvedHoursAttribute(): int
    {
        return (int) ($this->actual_hours ?: $this->planned_hours ?: 0);
    }

    public function canBeCompleted(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled'], true);
    }

    public function canBeModified(): bool
    {
        return $this->status !== 'cancelled';
    }

    public function hasAvailableCapacity(): bool
    {
        return $this->has_available_capacity;
    }

    public function canIssueCertificates(): bool
    {
        if (!$this->is_completed) {
            return false;
        }

        if (!$this->trainingCenter || !method_exists($this->trainingCenter, 'isEligibleCenter')) {
            return false;
        }

        if (!$this->trainingCenter->isEligibleCenter($this->delivery_mode)) {
            return false;
        }

        if ($this->is_online && !$this->has_approved_platform) {
            return false;
        }

        if (!$this->trainer || !method_exists($this->trainer, 'isEligibleTrainer')) {
            return false;
        }

        if (!$this->trainer->isEligibleTrainer()) {
            return false;
        }

        if (!method_exists($this->trainer, 'isAuthorizedForKit')) {
            return false;
        }

        if (!$this->trainer->isAuthorizedForKit($this->training_kit_id)) {
            return false;
        }

        return true;
    }
}