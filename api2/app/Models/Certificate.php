<?php

namespace App\Models;

use App\Support\CertificateType;
use App\Support\SignedPrintUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trainee_id',
        'training_center_id',
        'trainer_id',
        'training_kit_id',
        'training_program_id',
        'training_course_id',
        'certificate_number',
        'certificate_code',
        'reference_number',
        'verification_code',
        'certificate_type',
        'center_code',
        'trainer_code',
        'kit_code',
        'course_code',
        'trainee_code',
        'result',
        'score',
        'hours_awarded',
        'training_hours',
        'status',
        'issue_date',
        'issued_at',
        'certificate_date',
        'qr_code_path',
        'qr_url',
        'certificate_file_path',
        'is_verified',
        'verified_at',
        'notes',
        'governorate_id',
        'branch_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'hours_awarded' => 'integer',
        'issue_date' => 'date',
        'certificate_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function trainingKit(): BelongsTo
    {
        return $this->belongsTo(TrainingKit::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(CertificateApproval::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('certificate_number', 'like', "%{$search}%")
                ->orWhere('certificate_code', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('verification_code', 'like', "%{$search}%");
        });
    }

    public function getPrintableUrlAttribute(): string
    {
        return SignedPrintUrl::certificatePrint($this->id);
    }

    public function getIsPrintableAttribute(): bool
    {
        return $this->status === 'approved' && (bool) $this->is_verified;
    }

    public function typeLabel(): string
    {
        return CertificateType::label($this->certificate_type);
    }

    public function resolvedTrainingHours(): int
    {
        return (int) ($this->training_hours ?? $this->hours_awarded ?? 0);
    }

    public function publicViewUrl(): ?string
    {
        return $this->certificate_code
            ? url('/verify-certificate/' . rawurlencode($this->certificate_code))
            : null;
    }

    public function publicPrintUrl(): ?string
    {
        return $this->certificate_code
            ? url('/certificates/' . rawurlencode($this->certificate_code) . '/print')
            : null;
    }
}