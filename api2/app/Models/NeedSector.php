<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NeedSector extends Model
{
    protected $fillable = ['code', 'name_ar', 'name_en', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function needs(): BelongsToMany
    {
        return $this->belongsToMany(Need::class, 'need_need_sector');
    }
}
