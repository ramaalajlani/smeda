<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncubationProgram extends Model
{
    protected $fillable = [
        'incubator_id', 'name', 'description', 'duration_months',
        'seats', 'start_date', 'end_date', 'status', 'requirements',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IncubationApplication::class, 'program_id');
    }
}
