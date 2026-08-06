<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CertificateApproval extends Model
{
    protected $fillable = [
        'certificate_id',
        'approved_by',
        'approval_step',
        'decision',
        'decision_at',
        'notes',
    ];

    protected $casts = [
        'decision_at' => 'datetime',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function electronicSignature(): MorphOne
    {
        return $this->morphOne(DocumentElectronicSignature::class, 'signable');
    }
}