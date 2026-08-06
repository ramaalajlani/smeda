<?php

namespace App\Services;

use App\Models\StatusHistory;
use Illuminate\Database\Eloquent\Model;

class StatusHistoryService
{
    public function record(Model $model, ?string $fromStatus, string $toStatus, ?int $changedBy, ?string $reason = null): void
    {
        if ($fromStatus === $toStatus) {
            return;
        }

        StatusHistory::query()->create([
            'model_type' => $model->getMorphClass(),
            'model_id' => (int) $model->getKey(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
