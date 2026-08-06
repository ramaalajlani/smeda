<?php



namespace App\Policies;



use App\Models\Agreement;

use App\Models\User;

use App\Policies\Concerns\GrantsPlatformAdminFullAccess;

use App\Support\AccessControlGuard;

use App\Support\BranchDataScope;



class AgreementPolicy

{

    use GrantsPlatformAdminFullAccess;



    public function viewAny(?User $user): bool

    {

        if (!$user) {

            return false;

        }



        return AccessControlGuard::isNationalAdministrator($user)

            || AccessControlGuard::isNationalExecutive($user)

            || $user->hasPermissionTo('manage_agreements')

            || BranchDataScope::isBranchManager($user);

    }



    public function view(?User $user, Agreement $agreement): bool

    {

        if (!$user) {

            return false;

        }



        if (AccessControlGuard::isNationalAdministrator($user)

            || AccessControlGuard::isNationalExecutive($user)

            || $user->hasPermissionTo('manage_agreements')) {

            return true;

        }



        if (BranchDataScope::isBranchManager($user)) {

            return $agreement->isBranchScoped()

                && (int) $agreement->branch_id === (int) $user->branch_id;

        }



        return false;

    }



    public function create(?User $user): bool

    {

        return $user && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('manage_agreements')

        );

    }



    public function update(?User $user, Agreement $agreement): bool

    {

        return $this->create($user);

    }



    public function approve(?User $user, Agreement $agreement): bool

    {

        if (!$user || !$this->view($user, $agreement)) {

            return false;

        }



        if (AccessControlGuard::isNationalAdministrator($user)) {

            return true;

        }



        return AccessControlGuard::isNationalExecutive($user)

            && $user->hasPermissionTo('approve_agreements');

    }

}

