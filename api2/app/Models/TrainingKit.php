<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingKit extends Model
{
    use SoftDeletes;

    public const WORKFLOW_STATUSES = [
        'draft',
        'under_review',
        'approved',
        'published',
        'inactive',
        'archived',
    ];

    public const LEVELS = [
        'beginner',
        'intermediate',
        'advanced',
    ];

    protected $fillable = [
        'name',
        'name_en',
        'code',
        'sector',
        'category',
        'category_id',
        'subcategory_id',
        'type',
        'material_code',
        'level',
        'hours',
        'suggested_days',
        'objective',
        'description',
        'short_description',
        'prerequisites',
        'target_audience',
        'expected_outcomes',
        'status',
        'workflow_status',
        'published_at',
        'is_active',
        'promotional_file_path',
        'promotional_file_original_name',
        'promotional_file_mime',
        'promotional_file_size',
        'training_bag_file_path',
        'training_bag_file_original_name',
        'training_bag_file_mime',
        'training_bag_file_size',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'hours' => 'integer',
        'suggested_days' => 'integer',
        'is_active' => 'boolean',
        'promotional_file_size' => 'integer',
        'training_bag_file_size' => 'integer',
        'published_at' => 'datetime',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(KitMaterial::class, 'training_kit_id')->orderBy('sort_order')->orderBy('id');
    }

    public function trainingCategory(): BelongsTo
    {
        return $this->belongsTo(TrainingCategory::class, 'category_id');
    }

    public function trainingSubcategory(): BelongsTo
    {
        return $this->belongsTo(TrainingCategory::class, 'subcategory_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

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
        return $query->where('workflow_status', 'published');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('sector', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('material_code', 'like', "%{$search}%")
                ->orWhere('level', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function hasPromotionalFile(): bool
    {
        return filled($this->promotional_file_path);
    }

    public function hasTrainingBagFile(): bool
    {
        return filled($this->training_bag_file_path);
    }
}
