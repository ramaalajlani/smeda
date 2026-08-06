<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboxMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id', 'sender_role',
        'recipient_id', 'is_broadcast', 'broadcast_role',
        'subject', 'body', 'requires_reply', 'priority',
        'attachments', 'parent_id',
        'is_read', 'read_at',
    ];

    protected $casts = [
        'attachments'    => 'array',
        'is_broadcast'   => 'boolean',
        'requires_reply' => 'boolean',
        'is_read'        => 'boolean',
        'read_at'        => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(InboxMessage::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'parent_id')->latest();
    }

    public function reads(): HasMany
    {
        return $this->hasMany(InboxMessageRead::class, 'message_id');
    }
}
