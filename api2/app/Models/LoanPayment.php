<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'funded_loan_id', 'due_date', 'paid_date', 'amount_due', 'amount_paid', 'status', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(FundedLoan::class, 'funded_loan_id');
    }
}
