<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingKit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'sector',
        'category',
        'type',
        'material_code',
        'level',
        'hours',
        'objective',
        'description',
        'status',
        'is_active',
    ];

    public function materials()
    {
        return $this->hasMany(\App\Models\KitMaterial::class, 'training_kit_id')->orderBy('sort_order')->orderBy('id');
    }

    protected $casts = [
        'hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(
            Trainer::class,
            'trainer_training_kit'
        )->withPivot([
            'is_authorized',
            'authorized_from',
            'authorized_to',
            'notes',
        ])->withTimestamps();
    }

    public function centers(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingCenter::class,
            'training_center_training_kit'
        )->withPivot([
            'is_assigned',
            'assigned_from',
            'assigned_to',
            'notes',
        ])->withTimestamps();
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingProgram::class,
            'training_program_training_kit'
        )->withPivot([
            'sort_order',
            'is_required',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'active');
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
                ->orWhere('sector', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('material_code', 'like', "%{$search}%")
                ->orWhere('level', 'like', "%{$search}%");
        });
    }
}