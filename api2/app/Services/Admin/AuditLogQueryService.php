<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuditLogQueryService
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = AuditLog::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        return $query->paginate(max(1, min($perPage, 100)));
    }

    public function forUser(User $user, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $filters['user_id'] = $user->id;

        return $this->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): AuditLog
    {
        return AuditLog::query()
            ->with(['user:id,name,email'])
            ->findOrFail($id);
    }

    /** @return \Illuminate\Support\Collection<int, AuditLog> */
    public function export(array $filters = [], int $limit = 5000): \Illuminate\Support\Collection
    {
        $query = AuditLog::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        return $query->limit(max(1, min($limit, 10000)))->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (!empty($filters['entity_type'])) {
            $query->where('auditable_type', 'like', '%' . $filters['entity_type'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->whereHas('user', fn (Builder $q) => $q->where('email', 'like', '%' . $filters['email'] . '%'));
        }

        if (!empty($filters['ip'])) {
            $query->where('ip_address', $filters['ip']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $nested) use ($search) {
                $nested->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $q) => $q
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
