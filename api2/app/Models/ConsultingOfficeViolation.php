<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingOfficeViolation extends Model
{
    protected $fillable = [
        'office_id', 'reported_by', 'violation_type',
        'description', 'status', 'action_taken',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(ConsultingOffice::class, 'office_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
