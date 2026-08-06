<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingOfficeSpecialization extends Model
{
    protected $fillable = [
        'office_id', 'category_code', 'sector',
        'years_experience', 'sample_work_url', 'is_verified',
    ];

    protected $casts = ['is_verified' => 'boolean'];

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffice::class, 'office_id');
    }
}
