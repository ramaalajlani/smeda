<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class NotificationSummaryService
{
    /**
     * @return array{unread_count:int,group_unread_count:int,latest:\Illuminate\Support\Collection<int,\App\Models\Notification>}
     */
    public function summary(User $user): array
    {
        $unread = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $latest = Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $groupUserIds = $this->groupUserIds($user);

        $groupUnread = Notification::query()
            ->whereIn('user_id', $groupUserIds)
            ->where('is_read', false)
            ->count();

        return [
            'unread_count' => $unread,
            'group_unread_count' => $groupUnread,
            'latest' => $latest,
        ];
    }

    /**
     * @return list<int>
     */
    private function groupUserIds(User $user): array
    {
        $roleIds = $user->roles()->pluck('roles.id')->all();
        if ($roleIds === []) {
            return [(int) $user->id];
        }

        $query = User::query()
            ->select('users.id')
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('roles.id', $roleIds));

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->governorate_id) {
            $query->where('governorate_id', $user->governorate_id);
        }

        $ids = $query->pluck('users.id')->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            return [(int) $user->id];
        }

        return $ids;
    }
}
