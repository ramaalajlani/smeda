<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incubator extends Model
{
    protected $fillable = [
        'name', 'code', 'description', 'sector', 'location',
        'governorate_id', 'branch_id', 'manager_user_id',
        'phone', 'email', 'capacity', 'status', 'created_by',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(IncubationProgram::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IncubationApplication::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(IncubatedProject::class);
    }
}
