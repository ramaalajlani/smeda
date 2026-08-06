<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsultingRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_code', 'user_id', 'governorate_id', 'branch_id', 'branch_manager_id',
        'category_code', 'request_type', 'title', 'description',
        'project_name', 'economic_activity', 'isic4_code',
        'budget_min', 'budget_max', 'expected_duration_days',
        'status', 'branch_notes', 'submitted_at', 'completed_at', 'offers_deadline',
    ];

    protected $casts = [
        'submitted_at'   => 'datetime',
        'completed_at'   => 'datetime',
        'offers_deadline'=> 'datetime',
        'budget_min'     => 'decimal:2',
        'budget_max'     => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_manager_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConsultingRequestAttachment::class, 'request_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(ConsultingOffer::class, 'request_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(ConsultingContract::class, 'request_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ConsultingReport::class, 'request_id');
    }

    protected static function booted(): void
    {
        static::creating(function (ConsultingRequest $req) {
            if (empty($req->request_code)) {
                $year = now()->year;
                $count = \Illuminate\Support\Facades\DB::table('consulting_requests')
                    ->whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->count() + 1;
                $req->request_code = 'CON-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
