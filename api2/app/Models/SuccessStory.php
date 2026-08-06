<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SuccessStory extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'body',
        'incubated_project_id', 'incubator_id', 'branch_id',
        'hero_name', 'hero_title', 'hero_photo_url',
        'project_name', 'sector',
        'revenue_achieved', 'jobs_created', 'years_in_incubator', 'current_stage',
        'featured_quote',
        'cover_image_url', 'video_url', 'gallery',
        'status', 'is_featured', 'published_at',
        'created_by', 'approved_by',
    ];

    protected $casts = [
        'gallery'       => 'array',
        'is_featured'   => 'boolean',
        'published_at'  => 'datetime',
        'revenue_achieved' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title) . '-' . Str::random(5);
            }
            if (empty($m->created_by)) {
                $m->created_by = auth()->id();
            }
        });
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(IncubatedProject::class, 'incubated_project_id');
    }

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }
}
