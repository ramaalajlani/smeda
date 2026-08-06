<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingRequestAttachment extends Model
{
    protected $fillable = [
        'request_id', 'uploader_id', 'file_name',
        'file_path', 'file_type', 'file_size', 'upload_stage',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ConsultingRequest::class, 'request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
