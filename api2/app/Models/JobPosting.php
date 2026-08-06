<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'user_id',
        'organization_name',
        'title',
        'city',
        'governorate_id',
        'employment_type',
        'sector',
        'description',
        'skills',
        'status',
        'contact_email',
        'contact_phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('title', 'like', "%{$value}%")
                ->orWhere('organization_name', 'like', "%{$value}%")
                ->orWhere('city', 'like', "%{$value}%")
                ->orWhere('sector', 'like', "%{$value}%");
        });
    }
}
