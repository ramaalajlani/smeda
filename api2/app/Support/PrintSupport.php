<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class PrintSupport
{
    public static function formatDate(mixed $value, string $format = 'Y-m-d'): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return $value->format($format);
            }

            return Carbon::parse((string) $value)->format($format);
        } catch (\Throwable) {
            return '—';
        }
    }

    public static function yesNo(?bool $value): string
    {
        return $value ? 'نعم' : 'لا';
    }
}
