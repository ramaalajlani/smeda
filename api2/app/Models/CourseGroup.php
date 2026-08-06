<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseGroup extends Model
{
    protected $table = 'course_groups';

    protected $fillable = [
        'training_course_id', 'name', 'code', 'capacity', 'notes',
    ];

    protected $casts = ['capacity' => 'integer'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function trainees(): BelongsToMany
    {
        return $this->belongsToMany(Trainee::class, 'training_course_trainee', 'course_group_id', 'trainee_id')
            ->withPivot(['attendance_status', 'result', 'score', 'attended_hours', 'notes'])
            ->withTimestamps();
    }
}
