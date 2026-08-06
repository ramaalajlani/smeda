<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraineeModuleScore extends Model
{
    protected $table = 'trainee_module_scores';

    protected $fillable = [
        'training_course_id', 'trainee_id', 'program_module_id',
        'score', 'coursework_score', 'exam_score', 'max_score', 'pass_mark', 'result', 'notes',
    ];

    protected $casts = [
        'score'            => 'decimal:2',
        'coursework_score' => 'decimal:2',
        'exam_score'       => 'decimal:2',
        'max_score'        => 'decimal:2',
        'pass_mark'        => 'decimal:2',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ProgramModule::class, 'program_module_id');
    }
}
