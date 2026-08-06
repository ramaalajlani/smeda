<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trainee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'mother_name',
        'trainee_code',
        'national_id',
        'phone',
        'email',
        'governorate',
        'city',
        'district',
        'address',
        'location_visibility',
        'birth_date',
        'gender',
        'education_level',
        'status',
        'notes',
        'governorate_id',
        'branch_id',
        'owned_training_center_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function ownedTrainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class, 'owned_training_center_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            TrainingCourse::class,
            'training_course_trainee'
        )->withPivot([
            'attendance_status',
            'result',
            'score',
            'attended_hours',
            'notes',
        ])->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function workforce(): HasOne
    {
        return $this->hasOne(Workforce::class);
    }
}