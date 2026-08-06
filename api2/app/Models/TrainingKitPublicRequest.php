<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingKitPublicRequest extends Model
{
    protected $fillable = [
        'applicant_name',
        'applicant_email',
        'proposed_name',
        'city',
        'notes',
        'status',
    ];
}
