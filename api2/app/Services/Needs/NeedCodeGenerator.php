<?php

namespace App\Services\Needs;

use App\Models\Need;
use Illuminate\Support\Facades\DB;

class NeedCodeGenerator
{
    public function next(): string
    {
        $prefix = 'NEED-' . now()->format('Ymd');

        $query = Need::query()
            ->where('need_code', 'like', $prefix . '-%')
            ->orderByDesc('id');

        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $last = $query->value('need_code');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $seq);
    }
}
