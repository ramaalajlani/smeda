<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingKitAttachment extends Model
{
    protected $fillable = [
        'training_kit_id',
        'uploaded_by',
        'title',
        'original_name',
        'path',
        'mime',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(TrainingKit::class, 'training_kit_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function displayName(): string
    {
        return filled($this->title) ? $this->title : $this->original_name;
    }
}
