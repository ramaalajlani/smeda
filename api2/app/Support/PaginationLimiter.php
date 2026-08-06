<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationLimiter
{
    public const DEFAULT = 20;

    public const MAX = 100;

    public static function perPage(Request $request, int $default = self::DEFAULT, int $max = self::MAX): int
    {
        return max(1, min((int) $request->integer('per_page', $default), $max));
    }

    public static function mapLimit(Request $request, int $default = 500, int $max = 500): int
    {
        return max(1, min((int) $request->integer('limit', $default), $max));
    }
}
