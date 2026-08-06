<?php



namespace App\Policies;



use App\Models\ConsultantOffice;

use App\Models\User;

use App\Policies\Concerns\GrantsPlatformAdminFullAccess;

use App\Support\AccessControlGuard;

use App\Support\InstitutionalPartnerScope;



class ConsultantOfficePolicy

{

    use GrantsPlatformAdminFullAccess;



    public function viewAny(?User $user): bool

    {

        return $user && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasRole(['finance_manager', 'consultant_union_admin', 'consultant_office'])

            || $user->hasPermissionTo('finance.consultants.view_all')

            || $user->hasPermissionTo('finance.consultants.view')

            || $user->hasPermissionTo('finance.applications.assign_consultant')

        );

    }



    public function view(?User $user, ConsultantOffice $office): bool

    {

        return $this->viewAny($user)

            && InstitutionalPartnerScope::canAccessConsultantOffice($user, $office);

    }



    public function create(?User $user): bool

    {

        return $user && !$user->hasRole('auditor') && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.consultants.create')

            || $user->hasPermissionTo('finance.consultants.manage')

        );

    }



    public function update(?User $user, ConsultantOffice $office): bool

    {

        if (!$this->view($user, $office) || $user->hasRole('auditor')) {

            return false;

        }



        return AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.consultants.update')

            || $user->hasPermissionTo('finance.consultants.manage');

    }



    public function approve(?User $user, ConsultantOffice $office): bool

    {

        return $this->view($user, $office)

            && !$user->hasRole('auditor')

            && (

                AccessControlGuard::isNationalAdministrator($user)

                || $user->hasPermissionTo('finance.consultants.approve')

            );

    }



    public function activate(?User $user, ConsultantOffice $office): bool

    {

        return $this->approve($user, $office)

            && $user->hasPermissionTo('finance.consultants.activate');

    }



    public function suspend(?User $user, ConsultantOffice $office): bool

    {

        return $this->approve($user, $office)

            && $user->hasPermissionTo('finance.consultants.suspend');

    }



    public function monitor(?User $user): bool

    {

        return $user && (

            AccessControlGuard::isNationalAdministrator($user)

            || $user->hasPermissionTo('finance.consultants.monitor')

        );

    }

}

