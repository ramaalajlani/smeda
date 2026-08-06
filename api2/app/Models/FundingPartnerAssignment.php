<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingPartnerAssignment extends Model
{
    protected $fillable = [
        'funding_application_id', 'funding_partner_id', 'assigned_by', 'assigned_at', 'status',
        'approved_amount', 'approved_currency', 'decision_notes', 'decision_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'decision_at' => 'datetime',
        'approved_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FundingApplication::class, 'funding_application_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FundingPartner::class, 'funding_partner_id');
    }
}
