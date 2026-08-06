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