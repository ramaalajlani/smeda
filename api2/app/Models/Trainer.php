<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trainer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'training_center_id',
        'name',
        'trainer_code',
        'phone',
        'email',
        'governorate',
        'city',
        'district',
        'service_areas',
        'location_visibility',
        'specialization',
        'classification',
        'has_tot',
        'tot_certificate_number',
        'tot_certificate_source',
        'tot_issue_date',
        'tot_expiry_date',
        'can_train',
        'can_evaluate',
        'status',
        'accreditation_start_date',
        'accreditation_end_date',
        'bio',
        'notes',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'has_tot' => 'boolean',
        'can_train' => 'boolean',
        'can_evaluate' => 'boolean',
        'tot_issue_date' => 'date',
        'tot_expiry_date' => 'date',
        'accreditation_start_date' => 'date',
        'accreditation_end_date' => 'date',
        'service_areas' => 'array',
    ];

    /**
     * Relationships
     */
    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingKit::class,
            'trainer_training_kit'
        )->withPivot([
            'is_authorized',
            'authorized_from',
            'authorized_to',
            'notes',
        ])->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(TrainerProfile::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(TrainingKitNomination::class);
    }

    /**
     * Query Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCanTrain(Builder $query): Builder
    {
        return $query->where('can_train', true);
    }

    public function scopeHasTot(Builder $query): Builder
    {
        return $query->where('has_tot', true);
    }

    public function scopeForCenter(Builder $query, int|string|null $trainingCenterId): Builder
    {
        if (blank($trainingCenterId)) {
            return $query;
        }

        return $query->where('training_center_id', $trainingCenterId);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('trainer_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('specialization', 'like', "%{$search}%")
                ->orWhere('classification', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors
     */
    public function getIsTotValidAttribute(): bool
    {
        if (!$this->has_tot) {
            return false;
        }

        if (is_null($this->tot_expiry_date)) {
            return true;
        }

        return $this->tot_expiry_date->isFuture() || $this->tot_expiry_date->isToday();
    }

    public function getIsAccreditationValidAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (is_null($this->accreditation_end_date)) {
            return true;
        }

        return $this->accreditation_end_date->isFuture() || $this->accreditation_end_date->isToday();
    }

    public function getCanActuallyTrainAttribute(): bool
    {
        return $this->can_train
            && $this->has_tot
            && $this->is_tot_valid
            && $this->is_accreditation_valid
            && $this->status === 'active';
    }

    /**
     * Helper Methods
     */
    public function isEligibleTrainer(): bool
    {
        return $this->can_actually_train;
    }

    public function isAuthorizedForKit(int|string|null $trainingKitId): bool
    {
        if (blank($trainingKitId)) {
            return false;
        }

        $kit = $this->kits()
            ->where('training_kits.id', $trainingKitId)
            ->first();

        if (!$kit) {
            return false;
        }

        if (!(bool) ($kit->pivot->is_authorized ?? false)) {
            return false;
        }

        $authorizedFrom = $kit->pivot->authorized_from;
        $authorizedTo = $kit->pivot->authorized_to;
        $today = now()->toDateString();

        if (!empty($authorizedFrom) && $authorizedFrom > $today) {
            return false;
        }

        if (!empty($authorizedTo) && $authorizedTo < $today) {
            return false;
        }

        return true;
    }
}