<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSession extends Model
{
    protected $table = 'course_sessions';

    protected $fillable = [
        'training_course_id', 'course_group_id', 'program_module_id', 'session_no',
        'session_date', 'start_time', 'end_time', 'title', 'status', 'notes',
    ];

    protected $casts = [
        'session_no'   => 'integer',
        'session_date' => 'date',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ProgramModule::class, 'program_module_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'course_group_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(SessionAttendance::class, 'course_session_id');
    }
}
