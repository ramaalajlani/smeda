<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NeedActionLog extends Model
{
    protected $fillable = [
        'need_id', 'action', 'from_status', 'to_status', 'performed_by', 'note', 'payload',
    ];

    protected $casts = ['payload' => 'array'];

    public function need(): BelongsTo
    {
        return $this->belongsTo(Need::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
