<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantReport extends Model
{
    protected $fillable = [
        'funding_application_id', 'consultant_office_id', 'consultant_user_id', 'feasibility_score',
        'risk_level', 'recommended_amount', 'recommendation', 'report_summary', 'strengths',
        'weaknesses', 'conditions',
    ];

    protected $casts = ['recommended_amount' => 'decimal:2'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultantOffice::class, 'consultant_office_id');
    }

    public function consultantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_user_id');
    }
}
