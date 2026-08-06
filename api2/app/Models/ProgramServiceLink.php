<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramServiceLink extends Model
{
    protected $table = 'training_program_service_links';

    protected $fillable = [
        'program_id', 'service_type', 'service_reference_id', 'notes',
    ];

    public static array $SERVICE_LABELS = [
        'needs_map'       => 'خريطة الاحتياجات',
        'entrepreneurs'   => 'رواد الأعمال',
        'finance'         => 'التمويل',
        'consulting'      => 'الاستشارات',
        'incubators'      => 'الحاضنات',
        'accelerators'    => 'المسرعات',
        'certificates'    => 'الشهادات',
        'risk_assessment' => 'تقييم المخاطر',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }

    public function getServiceLabelAttribute(): string
    {
        return self::$SERVICE_LABELS[$this->service_type] ?? $this->service_type;
    }
}
