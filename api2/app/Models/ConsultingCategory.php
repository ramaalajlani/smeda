<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultingCategory extends Model
{
    protected $fillable = [
        'code', 'name_ar', 'name_en', 'description',
        'applicable_sectors', 'requires_isic4', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'applicable_sectors' => 'array',
        'requires_isic4'     => 'boolean',
        'is_active'          => 'boolean',
    ];
}
