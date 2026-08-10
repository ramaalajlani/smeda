<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingApplicationDetail extends Model
{
    protected $fillable = [
        'funding_application_id', 'owner_experience', 'employees_count', 'monthly_revenue',
        'monthly_expenses', 'existing_debts', 'assets_description', 'market_description',
        'challenges', 'requested_support', 'notes', 'extra_data',
    ];

    protected $casts = [
        'monthly_revenue' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'existing_debts' => 'decimal:2',
        'extra_data' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }
}
