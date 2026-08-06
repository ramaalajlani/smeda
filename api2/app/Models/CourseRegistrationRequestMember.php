<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRegistrationRequestMember extends Model
{
    protected $fillable = [
        'course_registration_request_id',
        'trainee_id',
        'full_name',
        'national_id',
        'phone',
        'email',
        'birth_date',
        'gender',
        'education_level',
        'relation_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CourseRegistrationRequest::class, 'course_registration_request_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}