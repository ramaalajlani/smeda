<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingCenter;
use App\Models\Trainer;
use App\Models\TrainingKit;
use App\Models\TrainingCourse;
use App\Models\Trainee;

class IdlibTrainingDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Training Center: MZ Group - Idlib
        $center = TrainingCenter::updateOrCreate(
            ['code' => 'TC-MZG-IDL'],
            [
                'name'                    => 'مؤسسة MZ Group',
                'city'                    => 'إدلب',
                'address'                 => 'إدلب',
                'accreditation_status'    => 'approved',
                'supports_offline_training' => true,
                'supports_online_training'  => false,
                'is_active'               => true,
                'notes'                   => 'مؤسسة MZ Group - إدلب',
            ]
        );

        // 2. Trainer linked to MZ Group
        $trainer = Trainer::updateOrCreate(
            ['trainer_code' => 'TRN-MZG-001'],
            [
                'training_center_id'      => $center->id,
                'name'                    => 'مؤسسة MZ Group',
                'specialization'          => 'تدريب تقني ومهني',
                'classification'          => 'senior',
                'has_tot'                 => true,
                'tot_certificate_number'  => 'TOT-MZG-001',
                'tot_certificate_source'  => 'SMEDA',
                'tot_issue_date'          => '2025-01-01',
                'tot_expiry_date'         => '2027-01-01',
                'can_train'               => true,
                'can_evaluate'            => true,
                'status'                  => 'active',
                'accreditation_start_date'=> '2025-01-01',
                'accreditation_end_date'  => '2027-01-01',
                'bio'                     => 'مؤسسة MZ Group للتدريب والتطوير',
            ]
        );

        // 3. Training Kits (one per course type)
        $kitsData = [
            'KIT-PHOTOSHOP' => [
                'name'     => 'Photoshop',
                'sector'   => 'digital',
                'category' => 'design',
                'level'    => 'intermediate',
                'hours'    => 30,
            ],
            'KIT-AUTOCAD' => [
                'name'     => 'AutoCAD',
                'sector'   => 'engineering',
                'category' => 'design',
                'level'    => 'intermediate',
                'hours'    => 40,
            ],
            'KIT-HENDASSI' => [
                'name'     => 'الإعداد الهندسي (ربط الدراسة بالتطبيق)',
                'sector'   => 'engineering',
                'category' => 'professional',
                'level'    => 'intermediate',
                'hours'    => 30,
            ],
            'KIT-DIGITAL-SEC' => [
                'name'     => 'الأمن الرقمي',
                'sector'   => 'digital',
                'category' => 'security',
                'level'    => 'beginner',
                'hours'    => 8,
            ],
        ];

        $kits = [];
        foreach ($kitsData as $code => $data) {
            $kit = TrainingKit::updateOrCreate(
                ['code' => $code],
                array_merge($data, ['status' => 'active', 'is_active' => true])
            );
            // Link trainer to kit
            DB::table('trainer_training_kit')->updateOrInsert(
                ['trainer_id' => $trainer->id, 'training_kit_id' => $kit->id],
                ['is_authorized' => true, 'authorized_from' => '2025-01-01', 'created_at' => now(), 'updated_at' => now()]
            );
            $kits[$code] = $kit;
        }

        // 4. Four Training Courses
        $coursesData = [
            [
                'course_code'  => 'CRS-IDL-PHOTO-2026',
                'kit_code'     => 'KIT-PHOTOSHOP',
                'title'        => 'دورة Photoshop',
                'start_date'   => '2026-05-10',
                'end_date'     => '2026-06-09',
                'planned_hours'=> 30,
                'actual_hours' => 30,
                'capacity'     => 20,
            ],
            [
                'course_code'  => 'CRS-IDL-AUTOCAD-2026',
                'kit_code'     => 'KIT-AUTOCAD',
                'title'        => 'دورة AutoCAD',
                'start_date'   => '2026-05-10',
                'end_date'     => '2026-06-08',
                'planned_hours'=> 40,
                'actual_hours' => 40,
                'capacity'     => 20,
            ],
            [
                'course_code'  => 'CRS-IDL-HEND-2026',
                'kit_code'     => 'KIT-HENDASSI',
                'title'        => 'الإعداد الهندسي (ربط الدراسة بالتطبيق)',
                'start_date'   => '2026-05-11',
                'end_date'     => '2026-06-09',
                'planned_hours'=> 30,
                'actual_hours' => 30,
                'capacity'     => 40,
            ],
            [
                'course_code'  => 'CRS-IDL-DIGSEC-2026',
                'kit_code'     => 'KIT-DIGITAL-SEC',
                'title'        => 'الأمن الرقمي',
                'start_date'   => '2026-04-29',
                'end_date'     => '2026-04-30',
                'planned_hours'=> 8,
                'actual_hours' => 8,
                'capacity'     => 40,
            ],
        ];

        $courses = [];
        foreach ($coursesData as $cd) {
            $course = TrainingCourse::updateOrCreate(
                ['course_code' => $cd['course_code']],
                [
                    'training_center_id' => $center->id,
                    'trainer_id'         => $trainer->id,
                    'training_kit_id'    => $kits[$cd['kit_code']]->id,
                    'title'              => $cd['title'],
                    'delivery_mode'      => 'offline',
                    'start_date'         => $cd['start_date'],
                    'end_date'           => $cd['end_date'],
                    'planned_hours'      => $cd['planned_hours'],
                    'actual_hours'       => $cd['actual_hours'],
                    'capacity'           => $cd['capacity'],
                    'status'             => 'completed',
                    'notes'              => 'مؤسسة MZ Group - فرع إدلب',
                ]
            );
            $courses[$cd['course_code']] = $course;
        }

        // 5. Trainees for الإعداد الهندسي (33 total — 18 from Excel, rest placeholders)
        $hendTrainees = [
            ['name' => 'براء صفوان بلق',            'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'مصطفى حسان زيداني',          'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'طلم جاسم أحمد',              'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'علي أحمد حلا موسى',          'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'عبد الرحمن أحمد عز الدين',   'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'أنس مصطفى نحاس',             'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'مهند أحمد أشقرو',            'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'مادل زكريا مراد',             'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'نور محمد وضاح عاشور',        'gender' => 'female', 'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'بيان خالد جمال',             'gender' => 'female', 'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'لانا أحمد عبد اللطيف',       'gender' => 'female', 'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'جنى محمد فايز عرجا',         'gender' => 'female', 'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'كرم محمود غزيرة',            'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'محمد عبد الرحمن عوض',        'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'عبد الله محمد نصر سالم',     'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'أحمد مهند دخان',             'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
            ['name' => 'بسملة محمد خير عفاش',        'gender' => 'female', 'education_level' => 'university', 'notes' => 'كلية الهندسة المعمارية - جامعة إدلب'],
            ['name' => 'عبد الهادي محمود الطه',      'gender' => 'male',   'education_level' => 'university', 'notes' => 'كلية الهندسة المدنية - جامعة إدلب'],
        ];

        $hendCourse = $courses['CRS-IDL-HEND-2026'];

        foreach ($hendTrainees as $index => $td) {
            $code = 'TR-IDL-HEND-' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT);
            $trainee = Trainee::updateOrCreate(
                ['trainee_code' => $code],
                [
                    'name'            => $td['name'],
                    'governorate'     => 'إدلب',
                    'city'            => 'إدلب',
                    'gender'          => $td['gender'],
                    'education_level' => $td['education_level'],
                    'status'          => 'active',
                    'notes'           => $td['notes'],
                ]
            );

            DB::table('training_course_trainee')->updateOrInsert(
                ['training_course_id' => $hendCourse->id, 'trainee_id' => $trainee->id],
                [
                    'attendance_status' => 'completed',
                    'result'            => 'passed',
                    'attended_hours'    => 30,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );
        }

        $this->command?->info('✓ تم إدخال بيانات تدريبات إدلب بنجاح');
        $this->command?->info("  - مركز التدريب: {$center->name} (ID: {$center->id})");
        $this->command?->info("  - المدرب: {$trainer->name} (ID: {$trainer->id})");
        $this->command?->info('  - الدورات: ' . count($courses));
        $this->command?->info('  - المتدربون (الإعداد الهندسي): ' . count($hendTrainees));
    }
}
