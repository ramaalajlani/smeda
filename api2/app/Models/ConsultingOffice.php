<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsultingOffice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'governorate_id', 'name', 'code',
        'license_number', 'license_date', 'license_expiry',
        'address', 'phone', 'email', 'website', 'status',
        'accreditation_date', 'overall_rating', 'total_requests_completed',
        'on_time_rate', 'report_accept_rate', 'notes', 'bio',
    ];

    protected $casts = [
        'license_date'       => 'date',
        'license_expiry'     => 'date',
        'accreditation_date' => 'date',
        'overall_rating'     => 'decimal:2',
        'on_time_rate'       => 'decimal:2',
        'report_accept_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function specializations(): HasMany
    {
        return $this->hasMany(ConsultingOfficeSpecialization::class, 'office_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(ConsultingOffer::class, 'office_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ConsultingContract::class, 'office_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ConsultingReview::class, 'office_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(ConsultingOfficeViolation::class, 'office_id');
    }

    public function recalculateRating(): void
    {
        $avg = $this->reviews()->where('is_published', true)->avg('overall_rating') ?? 0;
        $this->update(['overall_rating' => round($avg, 2)]);
    }
}
