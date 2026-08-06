<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserElectronicSignature extends Model
{
    protected $fillable = [
        'user_id',
        'signature_path',
        'original_name',
        'mime_type',
        'file_size',
        'file_hash',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function activeForUser(int $userId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }
}
