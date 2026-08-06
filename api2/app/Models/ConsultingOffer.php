<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsultingOffer extends Model
{
    protected $fillable = [
        'request_id', 'office_id', 'methodology_text',
        'proposed_duration_days', 'price', 'sample_attachments',
        'status', 'submitted_at', 'seen_by_client_at',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'submitted_at'       => 'datetime',
        'seen_by_client_at'  => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ConsultingRequest::class, 'request_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffice::class, 'office_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(ConsultingContract::class, 'offer_id');
    }
}
