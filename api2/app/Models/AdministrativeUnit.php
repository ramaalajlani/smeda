<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdministrativeUnit extends Model
{
    protected $fillable = [
        'governorate_id', 'branch_id', 'district_name', 'unit_name',
        'countryside_name', 'locality_name', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
