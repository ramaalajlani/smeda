<?php

namespace Database\Seeders;

use App\Models\CourseSession;
use App\Models\ProgramModule;
use App\Models\SessionAttendance;
use App\Models\Trainee;
use App\Models\TraineeModuleScore;
use App\Models\TrainingCourse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * يملأ دورات المركز ببيانات كاملة (متدربون + درجات المحاور + جلسات وحضور)
 * حتى لا تبقى أي شاشة فارغة. لا يمسّ الدورات التي فيها متدربون مسبقاً.
 *
 * التشغيل:  php artisan db:seed --class=FillCenterCoursesDataSeeder
 */
class FillCenterCoursesDataSeeder extends Seeder
{
    private int $centerId = 3;
    private int $perCourse = 10;

    private array $names = [
        'محمد أحمد العلي', 'سارة خالد حمود', 'عمر ياسين نجار', 'ليان وليد سعيد', 'حسن مروان درويش',
        'رغد سامر عبدو', 'كرم فادي حلاق', 'مايا نبيل شاهين', 'يزن أنس قاسم', 'جود بشار عيسى',
        'تيم رامي خضر', 'سلمى عماد زين', 'أدهم غسان طه', 'ريم هاني صالح', 'باسل نزار مراد',
    ];

    public function run(): void
    {
        $courses = TrainingCourse::where('training_center_id', $this->centerId)->get();
        if ($courses->isEmpty()) { $this->command?->warn("لا دورات للمركز #{$this->centerId}."); return; }

        foreach ($courses as $course) {
            if ($course->trainees()->count() > 0) {
                $this->command?->line("• «{$course->title}» فيها متدربون — تخطّي.");
                continue;
            }

            $modules = $course->training_program_id
                ? ProgramModule::where('program_id', $course->training_program_id)->orderBy('sort_order')->get()
                : collect();

            DB::transaction(function () use ($course, $modules) {
                $traineeIds = [];

                for ($i = 0; $i < $this->perCourse; $i++) {
                    $name = $this->names[$i % count($this->names)] . ' ' . ($i + 1);
                    $trainee = Trainee::create([
                        'name'            => $name,
                        'trainee_code'    => 'TR-C' . $course->id . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                        'national_id'     => (string) (11000000000 + $course->id * 1000 + $i),
                        'phone'           => '09' . str_pad((string) (10000000 + $course->id * 100 + $i), 8, '0', STR_PAD_LEFT),
                        'gender'          => $i % 2 === 0 ? 'male' : 'female',
                        'birth_date'      => sprintf('%04d-%02d-%02d', 1998 + ($i % 6), ($i % 12) + 1, ($i % 27) + 1),
                        'education_level' => 'بكالوريوس',
                        'status'          => 'active',
                        'governorate_id'  => $course->governorate_id,
                        'branch_id'       => $course->branch_id,
                    ]);
                    $traineeIds[] = $trainee->id;

                    // تسجيل في الدورة بحالة حضور وساعات
                    $course->trainees()->attach($trainee->id, [
                        'attendance_status' => 'attended',
                        'result'            => 'pending',
                        'attended_hours'    => 25 + ($i % 6),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    // درجات لكل محور (أعمال السنة + امتحان) متفاوتة
                    foreach ($modules as $m) {
                        $cw = 20 + (($i * 3 + $m->id) % 21);          // 20..40
                        $ex = 30 + (($i * 5 + $m->id * 2) % 31);      // 30..60
                        $total = $cw + $ex;
                        TraineeModuleScore::updateOrCreate(
                            ['training_course_id' => $course->id, 'trainee_id' => $trainee->id, 'program_module_id' => $m->id],
                            ['coursework_score' => $cw, 'exam_score' => $ex, 'score' => $total, 'max_score' => 100, 'pass_mark' => 60,
                             'result' => $total >= 60 ? 'passed' : 'failed'],
                        );
                    }
                }

                // النتيجة النهائية في pivot (معدّل المحاور)
                if ($modules->isNotEmpty()) {
                    $byTrainee = TraineeModuleScore::where('training_course_id', $course->id)->get()->groupBy('trainee_id');
                    foreach ($byTrainee as $tid => $scores) {
                        $pct = $scores->avg(fn ($s) => $s->max_score > 0 ? ((float) $s->score / (float) $s->max_score * 100) : 0);
                        $course->trainees()->updateExistingPivot((int) $tid, [
                            'score' => round($pct, 2), 'result' => $pct >= 60 ? 'passed' : 'failed',
                        ]);
                    }
                }

                // جلستان + حضور
                for ($s = 1; $s <= 2; $s++) {
                    $session = CourseSession::create([
                        'training_course_id' => $course->id,
                        'program_module_id'  => $modules->get($s - 1)->id ?? null,
                        'session_no'         => $s,
                        'session_date'       => now()->subDays(10 - $s * 3)->format('Y-m-d'),
                        'title'              => 'الجلسة ' . $s,
                        'status'             => 'held',
                    ]);
                    foreach ($traineeIds as $idx => $tid) {
                        $st = ($idx % 7 === 0) ? 'absent' : (($idx % 5 === 0) ? 'late' : 'present');
                        SessionAttendance::updateOrCreate(
                            ['course_session_id' => $session->id, 'trainee_id' => $tid],
                            ['status' => $st, 'minutes_attended' => $st === 'absent' ? 0 : 180],
                        );
                    }
                }
            });

            $this->command?->info("✔ «{$course->title}» ← {$this->perCourse} متدرب + درجات + جلستان حضور.");
        }
    }
}
