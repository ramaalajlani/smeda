<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAttendance extends Model
{
    protected $table = 'session_attendance';

    protected $fillable = [
        'course_session_id', 'trainee_id', 'status', 'minutes_attended', 'notes',
    ];

    protected $casts = [
        'minutes_attended' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class, 'course_session_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }
}
