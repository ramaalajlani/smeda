<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundedLoan extends Model
{
    protected $fillable = [
        'funding_application_id', 'funding_partner_id', 'loan_number', 'approved_amount', 'currency',
        'interest_type', 'interest_rate', 'profit_margin', 'installment_count', 'installment_amount',
        'start_date', 'end_date', 'status',
    ];

    protected $casts = [
        'approved_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'profit_margin' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FundingPartner::class, 'funding_partner_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
