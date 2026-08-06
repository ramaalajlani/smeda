<?php

namespace App\Services\Finance;

use App\Models\Branch;
use App\Models\FundingApplication;
use App\Models\User;
use Illuminate\Support\Str;

class FundingApplicationNumberGenerator
{
    public function next(): string
    {
        return 'FND-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
