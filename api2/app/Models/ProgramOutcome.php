<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramOutcome extends Model
{
    protected $table = 'training_program_outcomes';

    protected $fillable = ['program_id', 'title', 'description', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }
}
