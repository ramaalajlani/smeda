<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveSignerProfile extends Model
{
    public const ROLE_GENERAL_DIRECTOR = 'general_director';

    public const ROLE_DEPUTY_GENERAL_DIRECTOR = 'deputy_general_director';

    protected $fillable = [
        'role_key',
        'user_id',
        'signer_name',
        'signer_title',
        'signature_image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forRole(string $roleKey): ?self
    {
        return static::query()
            ->where('role_key', $roleKey)
            ->where('is_active', true)
            ->first();
    }
}
