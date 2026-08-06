<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsultingContract extends Model
{
    protected $fillable = [
        'contract_code', 'request_id', 'offer_id', 'office_id', 'client_user_id',
        'signed_by_client_at', 'signed_by_office_at',
        'start_date', 'expected_end_date', 'actual_end_date',
        'total_value', 'payment_status', 'contract_pdf_path',
    ];

    protected $casts = [
        'signed_by_client_at' => 'datetime',
        'signed_by_office_at' => 'datetime',
        'start_date'          => 'date',
        'expected_end_date'   => 'date',
        'actual_end_date'     => 'date',
        'total_value'         => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ConsultingRequest::class, 'request_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffer::class, 'offer_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffice::class, 'office_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConsultingMessage::class, 'contract_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ConsultingReport::class, 'contract_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ConsultingReview::class, 'contract_id');
    }

    public function isFullySigned(): bool
    {
        return $this->signed_by_client_at && $this->signed_by_office_at;
    }

    protected static function booted(): void
    {
        static::creating(function (ConsultingContract $c) {
            if (empty($c->contract_code)) {
                $year  = now()->year;
                $count = \Illuminate\Support\Facades\DB::table('consulting_contracts')
                    ->whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->count() + 1;
                $c->contract_code = 'CONC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
