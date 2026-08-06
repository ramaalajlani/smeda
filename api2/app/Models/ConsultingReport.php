<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingReport extends Model
{
    protected $fillable = [
        'contract_id', 'request_id', 'report_pdf_path', 'submission_date',
        'review_status', 'reviewer_id', 'review_date', 'reviewer_notes',
        'recommendation_type', 'recommendation_details', 'isic4_recommendation',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'review_date'     => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ConsultingContract::class, 'contract_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ConsultingRequest::class, 'request_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
