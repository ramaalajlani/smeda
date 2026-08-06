<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'title', 'body',
        'action_url', 'action_label',
        'icon', 'color',
        'reference_type', 'reference_id',
        'is_read', 'read_at', 'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper: إنشاء إشعار بسرعة
    public static function send(int $userId, string $type, string $title, array $options = []): self
    {
        return static::create(array_merge([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'color'      => 'primary',
            'icon'       => 'bi-bell-fill',
            'created_at' => now(),
        ], $options));
    }

    // Helper: إشعار لأصحاب الدور
    public static function sendToRole(string $role, string $type, string $title, array $options = []): void
    {
        User::role($role)->select('id')->each(function (User $u) use ($type, $title, $options) {
            static::send($u->id, $type, $title, $options);
        });
    }
}
