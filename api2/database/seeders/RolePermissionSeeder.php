<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'sanctum';

        $permissions = [
            // Trainers
            'view_trainers',
            'manage_trainers',
            'view_trainer_profiles',
            'edit_own_trainer_profile',

            // Centers
            'view_centers',
            'manage_centers',

            // Kits
            'view_kits',
            'manage_kits',
            'nominate_training_kits',
            'review_training_kit_nominations',

            // Programs
            'view_programs',
            'manage_programs',

            // Program Bank
            'program_bank.view',
            'program_bank.create',
            'program_bank.update',
            'program_bank.delete',
            'program_bank.approve',
            'program_bank.reports',

            // Courses
            'view_courses',
            'manage_courses',
            'view_course_details',

            // Trainees
            'view_trainees',
            'manage_trainees',

            // Certificates
            'view_certificates',
            'issue_certificates',

            // Certificate approvals
            'view_certificate_approvals',
            'approve_center_certificates',
            'approve_training_certificates',
            'approve_deputy_certificates',
            'approve_general_director_certificates',

            // Certificate actions
            'print_certificates',
            'verify_certificates',

            // Reports / Audit
            'view_reports',
            'view_audit',

            // Registration Requests
            'view_registration_requests',
            'create_center_registration_requests',
            'review_center_registration_requests',
            'create_trainer_registration_requests',
            'review_trainer_registration_requests',
            'create_trainee_registration_requests',
            'review_trainee_registration_requests',
            'create_course_registration_requests',
            'confirm_course_registration_requests',
            'complete_course_registration_requests',

            // Access / System Administration
            'manage_roles',
            'view_roles',
            'create_roles',
            'update_roles',
            'delete_roles',
            'manage_permissions',
            'view_permissions',
            'create_permissions',
            'update_permissions',
            'delete_permissions',
            'assign_roles',
            'revoke_roles',
            'assign_permissions',
            'revoke_permissions',
            'view_users',
            'manage_user_access',

            // National / Branch hierarchy
            'view_governorates',
            'view_branches',
            'manage_branches',
            'manage_branch_managers',
            'view_national_reports',
            'view_branch_reports',
            'manage_agreements',
            'approve_agreements',
            'view_finance',
            'manage_finance',
            'approve_finance',

            // Funding platform (dot notation)
            'finance.applications.view',
            'finance.applications.create',
            'finance.applications.update',
            'finance.applications.submit',
            'finance.applications.review_branch',
            'finance.applications.request_completion',
            'finance.applications.assign_consultant',
            'finance.applications.assign_partner',
            'finance.applications.approve',
            'finance.applications.reject',
            'finance.consultants.view',
            'finance.consultants.manage',
            'finance.consultants.assign',
            'finance.consultants.submit_price',
            'finance.consultants.approve_price',
            'finance.consultants.submit_report',
            'finance.partners.view',
            'finance.partners.manage',
            'finance.partners.review',
            'finance.partners.decide',
            'finance.loans.view',
            'finance.loans.manage',
            'finance.loans.payments',
            'finance.loans.defaulted',
            'finance.loans.close',
            'finance.metrics.view',
            'finance.metrics.national',
            'finance.metrics.branch',

            // Consultant union (Free Economists Union)
            'finance.consultant_union.dashboard',
            'finance.consultants.view_all',
            'finance.consultants.create',
            'finance.consultants.update',
            'finance.consultants.approve',
            'finance.consultants.activate',
            'finance.consultants.suspend',
            'finance.consultants.monitor',
            'finance.consultants.reports.view',
            'finance.consultants.price_offers.view',

            // Consultant office user
            'finance.consultant_office.dashboard',
            'finance.consultant_assignments.view_own',
            'finance.consultant_assignments.accept',
            'finance.consultant_assignments.reject',
            'finance.consultant_assignments.submit_price',
            'finance.consultant_reports.create',
            'finance.consultant_reports.update_own',
            'finance.consultant_reports.view_own',

            // Central bank supervision
            'finance.central_bank.dashboard',
            'finance.partners.view_all',
            'finance.partners.create',
            'finance.partners.update',
            'finance.partners.approve',
            'finance.partners.activate',
            'finance.partners.suspend',
            'finance.partners.monitor',
            'finance.partner_decisions.view_all',
            'finance.bank_metrics.view',

            // Funding partner (bank) user
            'finance.funding_partner.dashboard',
            'finance.partner_assignments.view_own',
            'finance.partner_assignments.review',
            'finance.partner_assignments.decide',
            'finance.partner_assignments.approve_amount',
            'finance.loans.view_own',
            'finance.loans.update_own_status',

            // GIS / Needs module
            'needs.view',
            'needs.view_all',
            'needs.view_branch',
            'needs.create',
            'needs.create_citizen',
            'needs.create_state',
            'needs.update',
            'needs.review',
            'needs.approve',
            'needs.reject',
            'needs.return',
            'needs.classify',
            'needs.resolve',
            'needs.export',
            'needs.dashboard',
            'needs.map',
            'needs.manage_lookups',
            'needs.manage_admin_units',
            'needs.view_state_only',

            // Workforce jobs platform
            'workforce.jobs.view',
            'workforce.jobs.create',
            'workforce.jobs.manage',
            'workforce.applications.create',
            'workforce.applications.view',
            'workforce.training_requests.create',
            'workforce.training_requests.view',

            // Incubation / Entrepreneurship / Media
            'incubation.view',
            'incubation.manage',
            'incubation.mentor',
            'entrepreneur.manage',
            'story.manage',
            'news.manage',
        ];

        $accessPermissions = [
            'manage_roles', 'view_roles', 'create_roles', 'update_roles', 'delete_roles',
            'manage_permissions', 'view_permissions', 'create_permissions', 'update_permissions', 'delete_permissions',
            'assign_roles', 'revoke_roles', 'assign_permissions', 'revoke_permissions',
            'view_users', 'manage_user_access',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $needsNational = [
            'needs.view', 'needs.view_all', 'needs.create', 'needs.create_citizen', 'needs.create_state',
            'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
            'needs.classify', 'needs.resolve', 'needs.export', 'needs.dashboard', 'needs.map',
            'needs.manage_lookups', 'needs.manage_admin_units',
        ];

        $needsBranchOfficer = [
            'needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen',
            'needs.update', 'needs.map',
        ];

        $financeBranchOfficer = [
            'finance.applications.view',
            'finance.consultants.view',
        ];

        $branchOfficerBase = [
            'view_branch_reports', 'view_governorates', 'view_branches',
            'view_trainers', 'view_trainer_profiles', 'view_centers',
            'view_courses', 'view_course_details', 'view_trainees',
            'view_certificates', 'view_registration_requests',
        ];

        $needsBranchManager = [
            'needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen', 'needs.create_state',
            'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
            'needs.export', 'needs.dashboard', 'needs.map',
        ];

        $needsDataEntry = [
            'needs.view', 'needs.view_branch', 'needs.create', 'needs.create_citizen',
            'needs.update', 'needs.map', 'view_governorates',
        ];

        $needsDataReviewer = [
            'needs.view', 'needs.view_branch', 'needs.review', 'needs.return', 'needs.map',
            'view_governorates',
        ];

        $needsProjectServices = [
            'needs.view', 'needs.view_all', 'needs.create', 'needs.create_citizen', 'needs.create_state',
            'needs.update', 'needs.review', 'needs.approve', 'needs.reject', 'needs.return',
            'needs.classify', 'needs.resolve', 'needs.export', 'needs.dashboard', 'needs.map',
            'needs.manage_lookups', 'needs.manage_admin_units',
        ];

        $projectServicesTraining = [
            'view_trainers', 'manage_trainers', 'view_trainer_profiles',
            'view_centers', 'manage_centers',
            'view_kits', 'manage_kits', 'review_training_kit_nominations',
            'view_programs', 'manage_programs',
            'program_bank.view', 'program_bank.create', 'program_bank.update',
            'program_bank.delete', 'program_bank.approve', 'program_bank.reports',
            'view_courses', 'manage_courses', 'view_course_details',
            'view_trainees', 'manage_trainees',
            'view_certificates', 'issue_certificates', 'view_certificate_approvals',
            'approve_training_certificates', 'print_certificates', 'verify_certificates',
            'view_reports', 'view_registration_requests',
            'review_center_registration_requests',
            'review_trainer_registration_requests',
            'review_trainee_registration_requests',
        ];

        $projectServicesAdmin = [
            'view_users', 'manage_user_access',
            'assign_roles', 'revoke_roles',
            'view_governorates', 'view_branches', 'manage_branches', 'manage_branch_managers',
            'view_national_reports', 'view_branch_reports',
        ];

        $needsDevelopment = [
            'needs.view', 'needs.view_all', 'needs.create_state', 'needs.classify',
            'needs.dashboard', 'needs.map', 'needs.export',
        ];

        $needsAuditor = ['needs.view', 'needs.view_all', 'needs.dashboard', 'needs.map'];

        $needsGovernor = [
            'needs.view', 'needs.view_branch', 'needs.view_state_only',
            'needs.create_state', 'needs.update',
            'needs.dashboard', 'needs.map', 'needs.export',
        ];

        $workforceBasic = [
            'workforce.jobs.view',
            'workforce.applications.create',
            'workforce.training_requests.create',
        ];

        $workforceEmployer = array_merge($workforceBasic, [
            'workforce.jobs.create',
        ]);

        $workforceManager = array_merge($workforceEmployer, [
            'workforce.jobs.manage',
            'workforce.applications.view',
            'workforce.training_requests.view',
        ]);

        $workforceManagerRole = array_merge($workforceManager, [
            'view_reports',
        ]);

        $incubationView = ['incubation.view'];
        $incubationManage = ['incubation.view', 'incubation.manage', 'story.manage'];
        $incubationMentor = ['incubation.view', 'incubation.mentor'];
        $entrepreneurManage = ['incubation.view', 'entrepreneur.manage', 'story.manage'];
        $mediaManage = ['news.manage', 'story.manage'];

        $financeViewOnly = [
            'finance.applications.view',
            'finance.consultants.view',
            'finance.partners.view',
            'finance.loans.view',
            'finance.metrics.view',
        ];

        $financeNationalExecutive = array_merge($financeViewOnly, [
            'finance.applications.approve',
            'finance.applications.reject',
            'finance.metrics.national',
        ]);

        $financeBranchManager = [
            'finance.applications.view',
            'finance.applications.review_branch',
            'finance.applications.request_completion',
            'finance.applications.assign_consultant',
            'finance.consultants.view',
            'finance.consultants.approve_price',
            'finance.loans.view',
            'finance.metrics.view',
            'finance.metrics.branch',
        ];

        $financeConsultantOffice = [
            'finance.applications.view',
            'finance.consultants.view',
            'finance.consultant_office.dashboard',
            'finance.consultant_assignments.view_own',
            'finance.consultant_assignments.accept',
            'finance.consultant_assignments.reject',
            'finance.consultant_assignments.submit_price',
            'finance.consultant_reports.create',
            'finance.consultant_reports.view_own',
            'finance.consultants.submit_price',
            'finance.consultants.submit_report',
        ];

        $financePartnerRole = [
            'finance.applications.view',
            'finance.partners.view',
            'finance.funding_partner.dashboard',
            'finance.partner_assignments.view_own',
            'finance.partner_assignments.review',
            'finance.partner_assignments.decide',
            'finance.partner_assignments.approve_amount',
            'finance.partners.review',
            'finance.partners.decide',
            'finance.loans.view',
            'finance.loans.view_own',
        ];

        $consultantUnionAdmin = [
            'finance.consultant_union.dashboard',
            'finance.consultants.view_all',
            'finance.consultants.create',
            'finance.consultants.update',
            'finance.consultants.approve',
            'finance.consultants.activate',
            'finance.consultants.suspend',
            'finance.consultants.monitor',
            'finance.consultants.reports.view',
            'finance.consultants.price_offers.view',
            'finance.applications.view',
            'finance.consultants.view',
        ];

        $centralBankAdmin = [
            'finance.central_bank.dashboard',
            'finance.partners.view_all',
            'finance.partners.create',
            'finance.partners.update',
            'finance.partners.approve',
            'finance.partners.activate',
            'finance.partners.suspend',
            'finance.partners.monitor',
            'finance.partner_decisions.view_all',
            'finance.bank_metrics.view',
            'finance.applications.view',
            'finance.partners.view',
            'finance.loans.view',
        ];

        $financeProjectOwner = [
            'finance.applications.view',
            'finance.applications.create',
            'finance.applications.update',
            'finance.applications.submit',
        ];

        $financeManagerRole = array_filter($permissions, fn ($p) => str_starts_with($p, 'finance.'));

        $roles = [
            'general_director' => $permissions,
            'admin' => $permissions,

            'deputy_general_director' => $permissions,

            'governor' => array_merge([
                'view_branch_reports', 'view_governorates', 'view_branches', 'view_national_reports',
                'view_trainers', 'view_trainer_profiles', 'view_centers',
                'view_courses', 'view_course_details', 'view_trainees',
                'view_certificates', 'view_certificate_approvals',
                'view_registration_requests', 'view_reports',
            ], $needsGovernor),

            'branch_manager' => array_merge([
                'view_branch_reports', 'view_governorates', 'view_branches',
                'view_trainers', 'manage_trainers', 'view_trainer_profiles',
                'view_centers', 'manage_centers',
                'view_courses', 'manage_courses', 'view_course_details',
                'view_trainees', 'manage_trainees',
                'view_certificates', 'issue_certificates', 'view_certificate_approvals',
                'approve_center_certificates', 'print_certificates', 'verify_certificates',
                'view_registration_requests', 'review_center_registration_requests',
                'review_trainer_registration_requests', 'review_trainee_registration_requests',
                'review_training_kit_nominations',
            ], $financeBranchManager, $needsBranchManager, $workforceManager),

            'branch_officer' => array_merge($branchOfficerBase, $financeBranchOfficer, $needsBranchOfficer, $workforceBasic),

            'workforce_manager' => $workforceManagerRole,

            'training_manager' => array_merge([
                'view_trainers',
                'manage_trainers',
                'view_trainer_profiles',

                'view_centers',
                'manage_centers',

                'view_kits',
                'manage_kits',
                'review_training_kit_nominations',

                'view_programs',
                'manage_programs',
                'program_bank.view',
                'program_bank.create',
                'program_bank.update',
                'program_bank.delete',
                'program_bank.approve',
                'program_bank.reports',

                'view_courses',
                'manage_courses',
                'view_course_details',

                'view_trainees',
                'manage_trainees',

                'view_certificates',
                'issue_certificates',
                'view_certificate_approvals',
                'approve_training_certificates',
                'print_certificates',
                'verify_certificates',

                'view_reports',
                'view_registration_requests',
                'review_center_registration_requests',
                'review_trainer_registration_requests',
                'review_trainee_registration_requests',
            ], $workforceManager),

            'training_supervisor' => array_merge([
                'view_trainers',
                'view_trainer_profiles',
                'view_centers',
                'view_kits',
                'view_programs',
                'view_courses',
                'view_course_details',
                'view_trainees',
                'view_certificates',
                'view_certificate_approvals',
                'print_certificates',
                'verify_certificates',
                'view_registration_requests',
                'review_center_registration_requests',
                'review_trainer_registration_requests',
                'review_trainee_registration_requests',
                'review_training_kit_nominations',
                'view_reports',
            ], $workforceBasic),

            'deputy_director' => $permissions,

            'center_user' => array_merge([
                'view_centers',
                'manage_centers',

                'view_kits',
                'manage_kits',
                'view_trainers',
                'manage_trainers',

                'view_courses',
                'manage_courses',
                'view_course_details',

                'view_trainees',
                'manage_trainees',

                'view_certificates',
                'issue_certificates',
                'view_certificate_approvals',
                'approve_center_certificates',
                'print_certificates',
                'verify_certificates',

                'view_registration_requests',
                'create_center_registration_requests',
                'create_trainer_registration_requests',
                'create_trainee_registration_requests',
                'complete_course_registration_requests',
            ], $workforceEmployer),

            'trainer_user' => [
                'view_courses',
                'view_course_details',
                'view_trainees',
                'view_certificates',
                'print_certificates',

                'view_trainer_profiles',
                'edit_own_trainer_profile',

                'nominate_training_kits',
                'create_trainer_registration_requests',
                'create_course_registration_requests',
                'confirm_course_registration_requests',
            ],

            'trainee_user' => array_merge([
                'view_courses',
                'view_course_details',
                'view_certificates',
                'print_certificates',
                'create_trainee_registration_requests',
                'create_course_registration_requests',
                'confirm_course_registration_requests',
            ], $workforceBasic),

            'auditor' => array_merge([
                'view_trainers',
                'view_trainer_profiles',
                'view_centers',
                'view_kits',
                'view_programs',
                'program_bank.view',
                'program_bank.reports',
                'view_courses',
                'view_course_details',
                'view_trainees',
                'view_certificates',
                'view_certificate_approvals',
                'view_audit',
                'view_reports',
                'verify_certificates',
                'view_registration_requests',
                'view_finance',
            ], $financeViewOnly, $needsAuditor),

            'data_entry' => $needsDataEntry,

            'data_reviewer' => $needsDataReviewer,

            'project_services_manager' => array_merge(
                $needsProjectServices,
                $projectServicesTraining,
                $projectServicesAdmin,
                $workforceManager
            ),

            'development_manager' => $needsDevelopment,

            'local_development_manager' => $needsDevelopment,

            'finance_manager' => $financeManagerRole,

            'finance_officer' => [
                'finance.applications.view',
                'finance.applications.create',
                'finance.applications.update',
                'finance.applications.review_branch',
                'finance.consultants.view',
                'finance.partners.view',
                'finance.loans.view',
                'finance.metrics.view',
                'finance.metrics.national',
            ],

            'consultant_office' => $financeConsultantOffice,

            'funding_partner' => $financePartnerRole,

            'consultant_union_admin' => $consultantUnionAdmin,

            'central_bank_admin' => $centralBankAdmin,

            'project_owner' => array_merge($financeProjectOwner, $workforceEmployer, $incubationView),

            'incubator_manager' => array_merge($incubationManage, [
                'view_reports',
                'view_governorates',
                'view_branches',
            ]),

            'incubator_mentor' => $incubationMentor,

            'entrepreneur_manager' => array_merge($entrepreneurManage, [
                'view_reports',
                'view_governorates',
            ]),

            'media_manager' => array_merge($mediaManage, [
                'view_reports',
            ]),

            'super_admin' => $permissions,

            'system_admin' => $accessPermissions,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($rolePermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}