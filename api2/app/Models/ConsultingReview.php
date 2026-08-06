<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingReview extends Model
{
    protected $fillable = [
        'contract_id', 'office_id', 'reviewer_id',
        'overall_rating', 'quality_rating', 'time_rating', 'communication_rating',
        'comment', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ConsultingContract::class, 'contract_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffice::class, 'office_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ConsultingReview $review) {
            $review->office->recalculateRating();
        });
    }
}
