<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NeedLookup extends Model
{
    protected $fillable = ['lookup_type', 'value', 'label', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
