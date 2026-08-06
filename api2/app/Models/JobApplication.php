<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_posting_id',
        'user_id',
        'applicant_name',
        'phone',
        'email',
        'specialty',
        'city',
        'experience_years',
        'summary',
        'cv_path',
        'status',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
