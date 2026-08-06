<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitMaterial extends Model
{
    protected $table = 'kit_materials';

    protected $fillable = [
        'training_kit_id', 'title', 'hours', 'sort_order', 'objectives', 'evaluation_method', 'notes',
    ];

    protected $casts = ['hours' => 'integer', 'sort_order' => 'integer'];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(TrainingKit::class, 'training_kit_id');
    }
}
