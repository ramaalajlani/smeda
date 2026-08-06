<?php

namespace Database\Seeders;

use App\Models\Trainee;
use App\Models\TrainingCourse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingCourseTraineeSeeder extends Seeder
{
    public function run(): void
    {
        $courses = TrainingCourse::query()->pluck('id')->all();
        $trainees = Trainee::query()->pluck('id')->all();

        if (empty($courses) || empty($trainees)) {
            $this->command?->warn('No training courses or trainees found. Seeder skipped.');
            return;
        }

        DB::table('training_course_trainee')->truncate();

        $rows = [];
        $now = now();

        foreach ($courses as $courseId) {
            $selectedTrainees = collect($trainees)
                ->shuffle()
                ->take(rand(3, min(8, count($trainees))))
                ->values();

            foreach ($selectedTrainees as $traineeId) {
                $attendanceStatus = rand(1, 100) <= 80 ? 'attended' : 'absent';

                if ($attendanceStatus === 'attended') {
                    $score = rand(50, 100);
                    $attendedHours = rand(20, 40);
                    $result = $score >= 60 ? 'passed' : 'failed';
                } else {
                    $score = 0;
                    $attendedHours = 0;
                    $result = 'failed';
                }

                $rows[] = [
                    'training_course_id' => $courseId,
                    'trainee_id' => $traineeId,
                    'attendance_status' => $attendanceStatus,
                    'result' => $result,
                    'score' => $score,
                    'attended_hours' => $attendedHours,
                    'notes' => 'بيانات تجريبية',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('training_course_trainee')->insert($rows);

        $this->command?->info('Training course trainees seeded successfully.');
    }
}