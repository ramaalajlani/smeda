<?php

namespace App\Support;

use App\Models\Branch;
use Illuminate\Validation\ValidationException;

class ActiveBranchGuard
{
    public static function assertAllowsOperations(?int $branchId): void
    {
        if (!$branchId) {
            return;
        }

        $branch = Branch::query()->find($branchId);

        if ($branch && !$branch->is_active) {
            throw ValidationException::withMessages([
                'branch_id' => ['الفرع معطل ولا يمكن إنشاء عمليات جديدة عليه.'],
            ]);
        }
    }

    public static function assertCourseBranchActive(?int $branchId): void
    {
        self::assertAllowsOperations($branchId);
    }
}
