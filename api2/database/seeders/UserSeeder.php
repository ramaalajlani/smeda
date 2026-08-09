<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!$this->shouldSeedDemoUsers()) {
            $this->command?->warn('UserSeeder skipped: demo users are disabled outside local/testing. Set ALLOW_DEMO_USERS=true to enable.');

            return;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $firstCenter = TrainingCenter::query()->first();
        $firstTrainer = Trainer::query()->first();
        $firstTrainee = Trainee::query()->first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('12345678'),
                'entity_type' => 'admin',
                'parent_user_id' => null,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin', 'general_director']);

        User::updateOrCreate(
            ['email' => 'general@system.com'],
            [
                'name' => 'General Director',
                'password' => bcrypt('12345678'),
                'entity_type' => 'admin',
                'parent_user_id' => null,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'governorate_id' => null,
                'branch_id' => null,
                'is_active' => true,
            ]
        )->syncRoles(['general_director']);

        $damascusBranch = Branch::query()->where('code', 'BR-DAMASCUS')->first();
        $aleppoBranch = Branch::query()->where('code', 'BR-ALEPPO')->first();

        if ($damascusBranch) {
            User::updateOrCreate(
                ['email' => 'branch.damascus@system.com'],
                [
                    'name' => 'Branch Manager Damascus',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'branch_manager',
                    'parent_user_id' => $admin->id,
                    'governorate_id' => $damascusBranch->governorate_id,
                    'branch_id' => $damascusBranch->id,
                    'is_active' => true,
                ]
            )->syncRoles(['branch_manager']);
        }

        if ($aleppoBranch) {
            User::updateOrCreate(
                ['email' => 'branch.aleppo@system.com'],
                [
                    'name' => 'Branch Manager Aleppo',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'branch_manager',
                    'parent_user_id' => $admin->id,
                    'governorate_id' => $aleppoBranch->governorate_id,
                    'branch_id' => $aleppoBranch->id,
                    'is_active' => true,
                ]
            )->syncRoles(['branch_manager']);
        }

        $tartusBranch = Branch::query()->where('code', 'BR-TARTUS')->first();

        if ($tartusBranch) {
            User::updateOrCreate(
                ['email' => 'governor.tartus@system.com'],
                [
                    'name' => 'محافظ طرطوس',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'governor',
                    'parent_user_id' => $admin->id,
                    'governorate_id' => $tartusBranch->governorate_id,
                    'branch_id' => $tartusBranch->id,
                    'training_center_id' => null,
                    'trainer_id' => null,
                    'trainee_id' => null,
                    'is_active' => true,
                ]
            )->syncRoles(['governor']);
        }

        $manager = User::updateOrCreate(
            ['email' => 'manager@system.com'],
            [
                'name' => 'Training Manager',
                'password' => bcrypt('12345678'),
                'entity_type' => 'training_manager',
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
            ]
        );
        $manager->syncRoles(['training_manager']);

        $deputy = User::updateOrCreate(
            ['email' => 'deputy@system.com'],
            [
                'name' => 'Deputy Director',
                'password' => bcrypt('12345678'),
                'entity_type' => 'deputy_director',
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
            ]
        );
        $deputy->syncRoles(['deputy_director', 'deputy_general_director']);

        $centerUser = null;
        if ($firstCenter) {
            $centerUser = User::updateOrCreate(
                ['email' => 'center@system.com'],
                [
                    'name' => 'Training Center User',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'center_user',
                    'parent_user_id' => $manager->id,
                    'training_center_id' => $firstCenter->id,
                    'trainer_id' => null,
                    'trainee_id' => null,
                    'is_active' => true,
                ]
            );
            $centerUser->syncRoles(['center_user']);
        }

        $trainerUser = null;
        if ($firstTrainer) {
            $trainerUser = User::updateOrCreate(
                ['email' => 'trainer@system.com'],
                [
                    'name' => 'Trainer User',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'trainer_user',
                    'parent_user_id' => $centerUser?->id,
                    'training_center_id' => $firstTrainer->training_center_id,
                    'trainer_id' => $firstTrainer->id,
                    'trainee_id' => null,
                    'is_active' => true,
                ]
            );
            $trainerUser->syncRoles(['trainer_user']);
        }

        if ($firstTrainee) {
            $traineeUser = User::updateOrCreate(
                ['email' => 'trainee@system.com'],
                [
                    'name' => 'Trainee User',
                    'password' => bcrypt('12345678'),
                    'entity_type' => 'trainee_user',
                    'parent_user_id' => $trainerUser?->id ?? $centerUser?->id,
                    'training_center_id' => null,
                    'trainer_id' => null,
                    'trainee_id' => $firstTrainee->id,
                    'is_active' => true,
                ]
            );
            $traineeUser->syncRoles(['trainee_user']);
        }

        $auditor = User::updateOrCreate(
            ['email' => 'auditor@system.com'],
            [
                'name' => 'System Auditor',
                'password' => bcrypt('12345678'),
                'entity_type' => 'auditor',
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
            ]
        );
        $auditor->syncRoles(['auditor']);

        User::updateOrCreate(
            ['email' => 'projects@system.com'],
            [
                'name' => 'مدير خدمات المشروعات',
                'password' => bcrypt('12345678'),
                'entity_type' => 'project_services_manager',
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'governorate_id' => null,
                'branch_id' => null,
                'is_active' => true,
            ]
        )->syncRoles(['project_services_manager']);

        User::updateOrCreate(
            ['email' => 'central.bank@system.com'],
            [
                'name' => 'Central Bank Admin',
                'password' => bcrypt('12345678'),
                'entity_type' => 'central_bank_admin',
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'governorate_id' => null,
                'branch_id' => null,
                'is_active' => true,
            ]
        )->syncRoles(['central_bank_admin']);

        User::updateOrCreate(
            ['email' => 'finance.manager@system.com'],
            [
                'name' => 'Finance Manager',
                'password' => bcrypt('12345678'),
                'entity_type' => 'finance_manager',
                'parent_user_id' => $admin->id,
                'is_active' => true,
            ]
        )->syncRoles(['finance_manager']);

        $damascusGovId = $damascusBranch?->governorate_id;
        $damascusBranchId = $damascusBranch?->id;

        $extraDemoUsers = [
            ['email' => 'finance.officer@system.com', 'name' => 'Finance Officer', 'role' => 'finance_officer', 'entity_type' => 'finance_officer', 'branch' => true],
            ['email' => 'funding.partner@system.com', 'name' => 'Funding Partner', 'role' => 'funding_partner', 'entity_type' => 'funding_partner'],
            ['email' => 'consultant.office@system.com', 'name' => 'Consultant Office', 'role' => 'consultant_office', 'entity_type' => 'consultant_office'],
            ['email' => 'consultant.union@system.com', 'name' => 'Consultant Union Admin', 'role' => 'consultant_union_admin', 'entity_type' => 'consultant_union_admin'],
            ['email' => 'media@system.com', 'name' => 'Media Manager', 'role' => 'media_manager', 'entity_type' => 'media_manager'],
            ['email' => 'incubator.manager@system.com', 'name' => 'Incubator Manager', 'role' => 'incubator_manager', 'entity_type' => 'incubator_manager'],
            ['email' => 'incubator.mentor@system.com', 'name' => 'Incubator Mentor', 'role' => 'incubator_mentor', 'entity_type' => 'incubator_mentor'],
            ['email' => 'entrepreneur.manager@system.com', 'name' => 'Entrepreneur Manager', 'role' => 'entrepreneur_manager', 'entity_type' => 'entrepreneur_manager'],
            ['email' => 'system.admin@system.com', 'name' => 'System Admin Access', 'role' => 'system_admin', 'entity_type' => 'system_admin'],
            ['email' => 'super.admin@system.com', 'name' => 'Super Admin', 'role' => 'super_admin', 'entity_type' => 'admin'],
            ['email' => 'branch.officer.damascus@system.com', 'name' => 'Branch Officer Damascus', 'role' => 'branch_officer', 'entity_type' => 'branch_officer', 'branch' => true],
            ['email' => 'workforce@system.com', 'name' => 'Workforce Manager', 'role' => 'workforce_manager', 'entity_type' => 'workforce_manager'],
            ['email' => 'training.supervisor@system.com', 'name' => 'Training Supervisor', 'role' => 'training_supervisor', 'entity_type' => 'training_supervisor', 'branch' => true],
            ['email' => 'development@system.com', 'name' => 'Development Manager', 'role' => 'development_manager', 'entity_type' => 'development_manager'],
            ['email' => 'local.development@system.com', 'name' => 'Local Development Manager', 'role' => 'local_development_manager', 'entity_type' => 'local_development_manager'],
            ['email' => 'project.owner@system.com', 'name' => 'Project Owner', 'role' => 'project_owner', 'entity_type' => 'project_owner'],
            ['email' => 'data-entry.damascus@system.com', 'name' => 'Data Entry Damascus', 'role' => 'data_entry', 'entity_type' => 'data_entry', 'branch' => true],
            ['email' => 'data-reviewer.damascus@system.com', 'name' => 'Data Reviewer Damascus', 'role' => 'data_reviewer', 'entity_type' => 'data_reviewer', 'branch' => true],
        ];

        foreach ($extraDemoUsers as $demo) {
            $payload = [
                'name' => $demo['name'],
                'password' => bcrypt('12345678'),
                'entity_type' => $demo['entity_type'],
                'parent_user_id' => $admin->id,
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'governorate_id' => !empty($demo['branch']) ? $damascusGovId : null,
                'branch_id' => !empty($demo['branch']) ? $damascusBranchId : null,
                'is_active' => true,
            ];

            User::updateOrCreate(['email' => $demo['email']], $payload)
                ->syncRoles([$demo['role']]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function shouldSeedDemoUsers(): bool
    {
        if (filter_var(env('ALLOW_DEMO_USERS', false), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return app()->environment(['local', 'testing']);
    }
}