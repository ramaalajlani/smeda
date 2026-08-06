<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingDocument extends Model
{
    protected $fillable = [
        'funding_application_id', 'document_type', 'file_path', 'original_name',
        'mime_type', 'size', 'uploaded_by',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
