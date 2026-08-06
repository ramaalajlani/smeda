<?php

namespace App\Support;

use App\Models\TrainingSupervisor;

class TrainingSupervisorResolver
{
    public const DEFAULT_INTERNAL_CODE = 'MOIT-CENTRAL';

    public function ensureDefaultSupervisorId(): int
    {
        $row = TrainingSupervisor::query()->firstOrCreate(
            ['code' => self::DEFAULT_INTERNAL_CODE],
            [
                'name' => 'وزارة التجارة الداخلية وحماية المستهلك - الجهة المركزية',
                'type' => 'internal_entity',
                'is_active' => true,
                'notes' => 'جهة افتراضية للمراكز غير المرتبطة بجهة مشرفة محددة.',
            ]
        );

        if (!$row->is_active) {
            $row->update(['is_active' => true]);
        }

        return (int) $row->id;
    }

    public function resolveForScope(?int $branchId, ?int $governorateId): int
    {
        $query = TrainingSupervisor::query()->active()->where('type', 'directorate');

        if ($branchId) {
            $row = (clone $query)->where('branch_id', $branchId)->orderBy('id')->first();
            if ($row) {
                return (int) $row->id;
            }
        }

        if ($governorateId) {
            $row = (clone $query)->where('governorate_id', $governorateId)->orderBy('id')->first();
            if ($row) {
                return (int) $row->id;
            }
        }

        return $this->ensureDefaultSupervisorId();
    }
}
