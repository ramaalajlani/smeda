<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingCourse;
use App\Models\TrainingCenter;
use App\Models\Trainer;
use App\Models\TrainingKit;
use App\Models\TrainingProgram;

class TrainingCourseSeeder extends Seeder
{
    public function run(): void
    {
        $centers = TrainingCenter::query()->get();
        $trainers = Trainer::query()->get();
        $kits = TrainingKit::query()->get();
        $programs = TrainingProgram::query()->get();

        if ($centers->isEmpty() || $trainers->isEmpty() || $kits->isEmpty()) {
            $this->command?->warn('Missing base data. TrainingCourseSeeder skipped.');
            return;
        }

        foreach (range(1, 10) as $i) {
            $trainer = $trainers->random();
            $center = $centers->firstWhere('id', $trainer->training_center_id) ?: $centers->random();
            $kit = $kits->random();
            $program = $programs->isNotEmpty() ? $programs->random() : null;

            $deliveryMode = $i % 2 === 0 ? 'offline' : 'online';

            TrainingCourse::updateOrCreate(
                ['course_code' => 'COURSE-' . $i],
                [
                    'training_center_id' => $center->id,
                    'trainer_id' => $trainer->id,
                    'training_kit_id' => $kit->id,
                    'training_program_id' => $program?->id,
                    'title' => 'Course ' . $i,
                    'delivery_mode' => $deliveryMode,
                    'approved_platform' => $deliveryMode === 'online' ? 'Zoom' : null,
                    'start_date' => now()->subDays(10)->toDateString(),
                    'end_date' => now()->subDays(5)->toDateString(),
                    'planned_hours' => 30,
                    'actual_hours' => 30,
                    'capacity' => 20,
                    'status' => 'completed',
                    'notes' => 'Seeded training course',
                ]
            );
        }
    }
}