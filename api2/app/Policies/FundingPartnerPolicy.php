<?php



namespace App\Policies;



use App\Models\FundingPartner;

use App\Models\User;

use App\Policies\Concerns\GrantsPlatformAdminFullAccess;

use App\Support\AccessControlGuard;

use App\Support\InstitutionalPartnerScope;



class FundingPartnerPolicy

{

    use GrantsPlatformAdminFullAccess;



    public function viewAny(?User $user): bool

    {

        return $user && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasRole(['finance_manager', 'central_bank_admin', 'funding_partner'])

            || $user->hasPermissionTo('finance.partners.view_all')

            || $user->hasPermissionTo('finance.partners.view')

            || $user->hasPermissionTo('finance.applications.assign_partner')

        );

    }



    public function view(?User $user, FundingPartner $partner): bool

    {

        return $this->viewAny($user)

            && InstitutionalPartnerScope::canAccessFundingPartner($user, $partner);

    }



    public function create(?User $user): bool

    {

        return $user && !$user->hasRole('auditor') && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.partners.create')

            || $user->hasPermissionTo('finance.partners.manage')

        );

    }



    public function update(?User $user, FundingPartner $partner): bool

    {

        if (!$this->view($user, $partner) || $user->hasRole('auditor')) {

            return false;

        }



        return AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.partners.update')

            || $user->hasPermissionTo('finance.partners.manage');

    }



    public function approve(?User $user, FundingPartner $partner): bool

    {

        return $this->view($user, $partner)

            && !$user->hasRole('auditor')

            && (

                AccessControlGuard::isNationalAdministrator($user)

                || $user->hasPermissionTo('finance.partners.approve')

            );

    }



    public function activate(?User $user, FundingPartner $partner): bool

    {

        return $this->approve($user, $partner)

            && $user->hasPermissionTo('finance.partners.activate');

    }



    public function suspend(?User $user, FundingPartner $partner): bool

    {

        return $this->approve($user, $partner)

            && $user->hasPermissionTo('finance.partners.suspend');

    }



    public function monitor(?User $user): bool

    {

        return $user && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.partners.monitor')

        );

    }

}

