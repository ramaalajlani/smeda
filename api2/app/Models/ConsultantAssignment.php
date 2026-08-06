<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantAssignment extends Model
{
    protected $fillable = [
        'funding_application_id', 'consultant_office_id', 'assigned_by', 'assigned_at', 'status',
        'price_offer_amount', 'price_offer_currency', 'price_offer_status', 'consultant_notes', 'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'price_offer_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultantOffice::class, 'consultant_office_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
