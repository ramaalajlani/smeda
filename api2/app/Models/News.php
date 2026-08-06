<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'summary', 'body', 'image_url',
        'category', 'branch_id', 'status', 'is_pinned',
        'published_at', 'created_by',
    ];

    protected $casts = [
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title) . '-' . Str::random(5);
            }
            $m->created_by = $m->created_by ?? auth()->id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }
}
