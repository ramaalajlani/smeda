<?php

namespace App\Policies;

use App\Models\ConsultingRequest;
use App\Models\User;
use App\Policies\Concerns\GrantsPlatformAdminFullAccess;
use App\Support\ConsultingDataScope;

class ConsultingRequestPolicy
{
    use GrantsPlatformAdminFullAccess;

    public function viewAny(?User $user): bool
    {
        return $user && (
            ConsultingDataScope::hasNationalAccess($user)
            || $user->hasRole([
                'branch_manager', 'branch_officer', 'governor',
                'project_owner', 'consultant_office', 'project_services_manager',
            ])
        );
    }

    public function view(?User $user, ConsultingRequest $request): bool
    {
        return $this->viewAny($user) && ConsultingDataScope::canAccessRequest($user, $request);
    }

    public function create(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole([
            'project_owner',
            'admin',
            'super_admin',
            'system_admin',
            'general_director',
            'project_services_manager',
            'branch_manager',
            'governor',
        ]);
    }

    public function update(?User $user, ConsultingRequest $request): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
        ])) {
            return true;
        }

        return ConsultingDataScope::canAccessRequest($user, $request)
            && (int) $request->user_id === (int) $user->id;
    }

    public function acceptOffer(?User $user, ConsultingRequest $request): bool
    {
        return $this->update($user, $request);
    }

    public function sort(?User $user, ConsultingRequest $request): bool
    {
        if (!$user) {
            return false;
        }

        if (!$user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
            'branch_manager', 'branch_officer', 'governor', 'consultant_union_admin',
        ])) {
            return false;
        }

        return ConsultingDataScope::canAccessRequest($user, $request);
    }

    public function transfer(?User $user, ConsultingRequest $request): bool
    {
        if (!$user) {
            return false;
        }

        if (!$user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
            'branch_manager', 'governor',
        ])) {
            return false;
        }

        return ConsultingDataScope::canAccessRequest($user, $request);
    }

    public function delete(?User $user, ConsultingRequest $request): bool
    {
        return $user && $user->hasRole([
            'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
        ]);
    }
}
