<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // الحقول الأصلية
        'name', 'code', 'description', 'status', 'is_active',
        // الحقول الجديدة لبنك البرامج
        'title', 'slug', 'type', 'sector', 'target_audience',
        'level', 'delivery_mode', 'suggested_hours', 'suggested_sessions',
        'prerequisites', 'outcomes_summary', 'grants_certificate',
        'requires_final_exam', 'requires_project', 'min_attendance_percent',
        'passing_score', 'bank_status', 'created_by', 'approved_by',
        'approved_at', 'suspended_at', 'archived_at',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'grants_certificate'  => 'boolean',
        'requires_final_exam' => 'boolean',
        'requires_project'    => 'boolean',
        'suggested_hours'     => 'integer',
        'suggested_sessions'  => 'integer',
        'min_attendance_percent' => 'integer',
        'passing_score'       => 'integer',
        'approved_at'         => 'datetime',
        'suspended_at'        => 'datetime',
        'archived_at'         => 'datetime',
    ];

    // ── تسميات الحالات ──
    public static array $BANK_STATUS_LABELS = [
        'draft'                 => 'مسودة',
        'under_technical_review'=> 'قيد المراجعة الفنية',
        'under_admin_review'    => 'قيد المراجعة الإدارية',
        'approved'              => 'معتمد',
        'suspended'             => 'موقوف',
        'archived'              => 'مؤرشف',
    ];

    public static array $TYPE_LABELS = [
        'entrepreneurial' => 'ريادي',
        'financial'       => 'مالي',
        'marketing'       => 'تسويقي',
        'technical'       => 'فني',
        'digital'         => 'رقمي',
        'administrative'  => 'إداري',
        'agricultural'    => 'زراعي',
        'craft'           => 'حرفي',
        'other'           => 'أخرى',
    ];

    public static array $LEVEL_LABELS = [
        'beginner'     => 'مبتدئ',
        'intermediate' => 'متوسط',
        'advanced'     => 'متقدم',
    ];

    public static array $DELIVERY_LABELS = [
        'in_person' => 'حضوري',
        'online'    => 'أونلاين',
        'blended'   => 'مدمج',
    ];

    // ── العلاقات الأصلية ──
    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingKit::class,
            'training_program_training_kit'
        )->withPivot(['sort_order', 'is_required'])->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // ── العلاقات الجديدة لبنك البرامج ──
    public function modules(): HasMany
    {
        return $this->hasMany(ProgramModule::class, 'program_id')->orderBy('sort_order');
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(ProgramOutcome::class, 'program_id')->orderBy('sort_order');
    }

    public function serviceLinks(): HasMany
    {
        return $this->hasMany(ProgramServiceLink::class, 'program_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ProgramApprovalLog::class, 'program_id')->orderByDesc('created_at');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(TrainingProgramExecution::class, 'program_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scopes ──
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('bank_status', 'approved');
    }

    public function scopeByBankStatus(Builder $query, ?string $status): Builder
    {
        if (!$status) return $query;
        return $query->where('bank_status', $status);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') return $query;
        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // ── مساعدات ──
    public function getBankStatusLabelAttribute(): string
    {
        return self::$BANK_STATUS_LABELS[$this->bank_status] ?? $this->bank_status;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::$TYPE_LABELS[$this->type] ?? '';
    }

    public function getLevelLabelAttribute(): string
    {
        return self::$LEVEL_LABELS[$this->level] ?? '';
    }

    public function getDeliveryLabelAttribute(): string
    {
        return self::$DELIVERY_LABELS[$this->delivery_mode] ?? '';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->title ?: $this->name;
    }
}
