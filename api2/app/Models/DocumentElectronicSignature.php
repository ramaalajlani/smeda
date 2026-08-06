<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentElectronicSignature extends Model
{
    protected $fillable = [
        'signable_type',
        'signable_id',
        'role_key',
        'signed_by_user_id',
        'user_electronic_signature_id',
        'signer_name',
        'signer_title',
        'signature_image_path',
        'signature_image_hash',
        'document_hash',
        'signature_hmac',
        'verification_code',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    public function userElectronicSignature(): BelongsTo
    {
        return $this->belongsTo(UserElectronicSignature::class);
    }
}
