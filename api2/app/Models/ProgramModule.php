<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramModule extends Model
{
    protected $table = 'training_program_modules';

    protected $fillable = [
        'program_id', 'title', 'description', 'hours',
        'sort_order', 'objectives', 'activities',
        'required_tools', 'evaluation_method',
    ];

    protected $casts = ['hours' => 'integer', 'sort_order' => 'integer'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }
}
