<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTrainingRequest extends Model
{
    protected $fillable = [
        'user_id',
        'organization_name',
        'employees_count',
        'training_field',
        'city',
        'details',
        'status',
    ];

    protected $casts = [
        'employees_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
