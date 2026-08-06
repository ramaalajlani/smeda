<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramApprovalLog extends Model
{
    protected $table = 'training_program_approval_logs';

    protected $fillable = [
        'program_id', 'from_status', 'to_status',
        'action', 'notes', 'user_id',
    ];

    public static array $ACTION_LABELS = [
        'created'    => 'تم الإنشاء',
        'submitted'  => 'أُرسل للاعتماد',
        'approved'   => 'اعتُمد',
        'rejected'   => 'رُفض',
        'suspended'  => 'أُوقف',
        'archived'   => 'أُرشف',
        'revised'    => 'أُعيد للمراجعة',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
