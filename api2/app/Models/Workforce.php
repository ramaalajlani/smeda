<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Workforce extends Model
{
    protected $fillable = [
        'trainee_id',
        'workforce_code',
        'status',
        'joined_at',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'date',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('workforce_code', 'like', "%{$value}%")
              ->orWhere('status', 'like', "%{$value}%")
              ->orWhere('notes', 'like', "%{$value}%")
              ->orWhereHas('trainee', function (Builder $traineeQuery) use ($value) {
                  $traineeQuery->where('name', 'like', "%{$value}%")
                      ->orWhere('trainee_code', 'like', "%{$value}%")
                      ->orWhere('national_id', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%");
              });
        });
    }
}