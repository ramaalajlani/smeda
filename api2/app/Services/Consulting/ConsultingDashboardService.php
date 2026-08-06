<?php

namespace App\Services\Consulting;

use App\Models\ConsultingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConsultingDashboardService
{
    /** @param  callable(): Builder  $scopeFactory */
    public function stats(User $user, callable $scopeFactory): array
    {
        $cacheKey = sprintf(
            'consulting:stats:v1:u%d:b%s:g%s:r%s',
            $user->id,
            $user->branch_id ?? '0',
            $user->governorate_id ?? '0',
            md5($user->getRoleNames()->sort()->implode(','))
        );

        return Cache::remember($cacheKey, 90, function () use ($scopeFactory) {
            $base = $scopeFactory();

            $byStatus = (clone $base)
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->map(fn ($count) => (int) $count)
                ->all();

            $byCategory = (clone $base)
                ->select('category_code', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('category_code')
                ->pluck('aggregate', 'category_code')
                ->map(fn ($count) => (int) $count)
                ->all();

            $total = array_sum($byStatus);

            return [
                'total' => $total,
                'completed' => $byStatus['completed'] ?? 0,
                'in_progress' => $byStatus['in_progress'] ?? 0,
                'pending' => ($byStatus['submitted'] ?? 0) + ($byStatus['needs_info'] ?? 0),
                'rejected' => $byStatus['rejected'] ?? 0,
                'by_status' => $byStatus,
                'by_category' => $byCategory,
                'recent' => (clone $base)
                    ->select(['id', 'request_code', 'title', 'category_code', 'status', 'created_at'])
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(),
                'pending_actions' => (clone $base)
                    ->whereIn('status', ['submitted', 'report_submitted', 'needs_info'])
                    ->select(['id', 'request_code', 'title', 'status', 'created_at'])
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get(),
            ];
        });
    }
}
