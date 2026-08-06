<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCenter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'governorate',
        'city',
        'district',
        'address',
        'phone',
        'email',
        'classification',
        'accreditation_status',
        'supports_offline_training',
        'supports_online_training',
        'accreditation_start_date',
        'accreditation_end_date',
        'latitude',
        'longitude',
        'location_visibility',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'license_issued_by',
        'license_image_path',
        'is_active',
        'notes',
        'governorate_id',
        'branch_id',
        'supervisor_id',
    ];

    protected $casts = [
        'supports_offline_training' => 'boolean',
        'supports_online_training' => 'boolean',
        'is_active' => 'boolean',
        'accreditation_start_date' => 'date',
        'accreditation_end_date' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */
    public function platforms(): HasMany
    {
        return $this->hasMany(TrainingCenterPlatform::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(TrainingSupervisor::class, 'supervisor_id');
    }

    public function trainers(): HasMany
    {
        return $this->hasMany(Trainer::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingKit::class,
            'training_center_training_kit'
        )->withPivot([
            'is_assigned',
            'assigned_from',
            'assigned_to',
            'notes',
        ])->withTimestamps();
    }

    /**
     * =========================
     * Query Scopes
     * =========================
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('accreditation_status', 'approved');
    }

    public function scopeSupportsOnline(Builder $query): Builder
    {
        return $query->where('supports_online_training', true);
    }

    public function scopeSupportsOffline(Builder $query): Builder
    {
        return $query->where('supports_offline_training', true);
    }

    public function scopeInCity(Builder $query, int|string|null $city): Builder
    {
        if (blank($city)) {
            return $query;
        }

        return $query->where('city', $city);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('classification', 'like', "%{$search}%");
        });
    }

    /**
     * =========================
     * Accessors
     * =========================
     */
    public function getIsAccreditationValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->accreditation_status !== 'approved') {
            return false;
        }

        if (is_null($this->accreditation_end_date)) {
            return true;
        }

        return $this->accreditation_end_date->isFuture() || $this->accreditation_end_date->isToday();
    }

    public function getCanHostOnlineTrainingAttribute(): bool
    {
        if (!$this->is_accreditation_valid) {
            return false;
        }

        if (!$this->supports_online_training) {
            return false;
        }

        return $this->hasApprovedPlatforms();
    }

    public function getCanHostOfflineTrainingAttribute(): bool
    {
        return $this->is_accreditation_valid
            && $this->supports_offline_training;
    }

    public function getApprovedPlatformsCountAttribute(): int
    {
        return $this->platforms()
            ->where('status', 'approved')
            ->count();
    }

    /**
     * =========================
     * Helper Methods
     * =========================
     */
    public function isEligibleCenter(?string $deliveryMode = null): bool
    {
        if (!$this->is_accreditation_valid) {
            return false;
        }

        if ($deliveryMode === null || $deliveryMode === '') {
            return true;
        }

        if ($deliveryMode === 'online') {
            return $this->can_host_online_training;
        }

        if ($deliveryMode === 'offline') {
            return $this->can_host_offline_training;
        }

        return false;
    }

    public function hasApprovedPlatforms(): bool
    {
        return $this->platforms()
            ->where('status', 'approved')
            ->exists();
    }
}