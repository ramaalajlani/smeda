<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingMessage extends Model
{
    protected $fillable = [
        'contract_id', 'sender_id', 'sender_role',
        'message_text', 'attachment_path',
        'is_read', 'read_at', 'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ConsultingContract::class, 'contract_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
