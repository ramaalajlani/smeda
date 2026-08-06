<?php

namespace Database\Seeders;

use App\Models\ProgramModule;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use Illuminate\Database\Seeder;

/**
 * يربط دورات مركز تدريبي ببرامج لها محاور (لتفعيل تبويب الدرجات لكل دورة).
 * ينشئ لكل دورة بلا برنامج برنامجاً بعنوانها ومحاور مناسبة.
 *
 * التشغيل:  php artisan db:seed --class=LinkCenterCoursesToProgramsSeeder
 */
class LinkCenterCoursesToProgramsSeeder extends Seeder
{
    /** رقم المركز المستهدف (MZ Group). غيّره إذا أردت مركزاً آخر. */
    private int $centerId = 3;

    /** محاور مخصّصة حسب عنوان الدورة، مع محاور افتراضية للبقية. */
    private array $modulesByTitle = [
        'دورة AutoCAD'   => ['مقدمة وواجهة البرنامج', 'الرسم ثنائي الأبعاد 2D', 'النمذجة ثلاثية الأبعاد 3D', 'مشروع تطبيقي'],
        'دورة Photoshop' => ['الأدوات والواجهة', 'الطبقات والأقنعة', 'التعديل والتأثيرات', 'مشروع تصميم تطبيقي'],
        'الأمن الرقمي'    => ['أساسيات الأمن الرقمي', 'التشفير وحماية البيانات', 'أمن الشبكات', 'الاستجابة للحوادث'],
    ];

    public function run(): void
    {
        $courses = TrainingCourse::where('training_center_id', $this->centerId)->get();

        if ($courses->isEmpty()) {
            $this->command?->warn("لا توجد دورات للمركز #{$this->centerId}.");

            return;
        }

        $linked = 0;

        foreach ($courses as $course) {
            if ($course->training_program_id) {
                $this->command?->line("• «{$course->title}» مربوطة مسبقاً — تخطّي.");
                continue;
            }

            $program = TrainingProgram::firstOrCreate(
                ['code' => 'PRG-C' . $course->id],
                [
                    'name'      => 'برنامج ' . $course->title,
                    'status'    => 'active',
                    'is_active' => 1,
                ]
            );

            $titles = $this->modulesByTitle[$course->title] ?? ['المحور الأول', 'المحور الثاني', 'مشروع تطبيقي'];

            foreach ($titles as $i => $title) {
                ProgramModule::firstOrCreate(
                    ['program_id' => $program->id, 'title' => $title],
                    [
                        'hours'             => 10,
                        'sort_order'        => $i + 1,
                        'evaluation_method' => 'اختبار عملي',
                    ]
                );
            }

            $course->training_program_id = $program->id;
            $course->save();
            $linked++;

            $this->command?->info("✔ «{$course->title}» ← برنامج #{$program->id} (" . count($titles) . ' محاور)');
        }

        $this->command?->line("تم ربط {$linked} دورة.");
    }
}
