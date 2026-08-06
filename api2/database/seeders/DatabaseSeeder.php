<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GovernorateBranchSeeder::class,
            TrainingSupervisorSeeder::class,

            TrainingCenterSeeder::class,
            TrainingCenterPlatformSeeder::class,

            TrainingKitSeeder::class,
            TrainingProgramSeeder::class,

            TrainerSeeder::class,
            TrainerTrainingKitSeeder::class,

            TraineeSeeder::class,
            TrainingCourseSeeder::class,
            TrainingCourseTraineeSeeder::class,

            UserSeeder::class,
            ExecutiveSignerProfileSeeder::class,
            CertificateSeeder::class,
            BranchDataBackfillSeeder::class,

            IdlibTrainingDataSeeder::class,
            NeedTaxonomySeeder::class,
            ConsultingCategorySeeder::class,
            IncubatorSeeder::class,
            EntrepreneurDemoSeeder::class,
            ConsultingDemoSeeder::class,
            ...(app()->environment('testing')
                ? [SyriaLocationsTestSeeder::class]
                : [SyriaLocationsSeeder::class]),
        ]);
    }
}