<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CourseGroup;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\TrainingKit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * بيانات وهمية شاملة لاختبار: المراكز، المدربين، المتدربين، الحقائب،
 * الصفوف، الشهادات، الخريطة، وتكليف حقيبة↔مركز.
 *
 * التشغيل: php artisan db:seed --class=DemoTrainingSmokeSeeder
 */
class DemoTrainingSmokeSeeder extends Seeder
{
    private array $centerCoords = [
        'TC-001' => ['governorate' => 'دمشق', 'lat' => 33.5138, 'lng' => 36.2765],
        'TC-002' => ['governorate' => 'حلب', 'lat' => 36.2021, 'lng' => 37.1343],
        'DEMO-TC-DMS' => ['governorate' => 'دمشق', 'lat' => 33.5102, 'lng' => 36.2913],
        'DEMO-TC-HMS' => ['governorate' => 'حمص', 'lat' => 34.7268, 'lng' => 36.7234],
    ];

    private array $arabicTrainees = [
        ['name' => 'أحمد محمد الخطيب', 'mother' => 'فاطمة حسن', 'gender' => 'male'],
        ['name' => 'سارة وليد العلي', 'mother' => 'منى خليل', 'gender' => 'female'],
        ['name' => 'عمر ياسين نجار', 'mother' => 'هند سمير', 'gender' => 'male'],
        ['name' => 'ليان كريم درويش', 'mother' => 'رباب فادي', 'gender' => 'female'],
        ['name' => 'حسن مروان سعيد', 'mother' => 'نادية أحمد', 'gender' => 'male'],
        ['name' => 'رغد سامر عبدو', 'mother' => 'سمر جميل', 'gender' => 'female'],
        ['name' => 'كرم فادي حلاق', 'mother' => 'لمى رامي', 'gender' => 'male'],
        ['name' => 'مايا نبيل شاهين', 'mother' => 'غادة وليد', 'gender' => 'female'],
        ['name' => 'يزن أنس قاسم', 'mother' => 'ريما طارق', 'gender' => 'male'],
        ['name' => 'جود بشار عيسى', 'mother' => 'هبة نزار', 'gender' => 'female'],
        ['name' => 'تيم رامي خضر', 'mother' => 'إيناس عدنان', 'gender' => 'male'],
        ['name' => 'سلمى عماد زين', 'mother' => 'دينا باسل', 'gender' => 'female'],
    ];

    public function run(): void
    {
        $this->command?->info('🔧 تجهيز بيانات وهمية للتدريب...');

        $centers = $this->ensureCenters();
        $kits = $this->ensureKits();
        $trainers = $this->ensureTrainers($centers);
        $this->assignKitsToCentersAndTrainers($centers, $kits, $trainers);
        $courses = $this->ensureCourses($centers, $trainers, $kits);
        $trainees = $this->ensureTrainees($centers);
        $this->enrollAndGroup($courses, $trainees);
        $this->ensureCertificates($courses);
        $this->ensureDemoUsers($centers->first(), $trainers->first(), $trainees->first());

        $this->printSummary();
    }

    private function ensureCenters()
    {
        $defs = [
            [
                'code' => 'DEMO-TC-DMS',
                'name' => 'مركز دمشق التجريبي للتدريب',
                'city' => 'دمشق',
                'address' => 'المزة — شارع الثورة',
                'phone' => '0112345678',
                'email' => 'demo.damascus@system.com',
            ],
            [
                'code' => 'DEMO-TC-HMS',
                'name' => 'مركز حمص التجريبي للتدريب',
                'city' => 'حمص',
                'address' => 'الحميدية — ساحة الساعة',
                'phone' => '0312345678',
                'email' => 'demo.homs@system.com',
            ],
        ];

        foreach ($defs as $def) {
            $coords = $this->centerCoords[$def['code']];
            TrainingCenter::updateOrCreate(
                ['code' => $def['code']],
                array_merge($def, [
                    'governorate' => $coords['governorate'],
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lng'],
                    'location_visibility' => 'public',
                    'classification' => 'first_class',
                    'accreditation_status' => 'approved',
                    'supports_offline_training' => true,
                    'supports_online_training' => true,
                    'accreditation_start_date' => now()->subYear()->toDateString(),
                    'accreditation_end_date' => now()->addYears(2)->toDateString(),
                    'is_active' => true,
                    'notes' => 'Demo smoke center',
                ])
            );
        }

        // Ensure existing seeded centers also have map coords.
        foreach ($this->centerCoords as $code => $coords) {
            TrainingCenter::where('code', $code)->update([
                'governorate' => $coords['governorate'],
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_visibility' => 'public',
                'accreditation_status' => 'approved',
                'is_active' => true,
            ]);
        }

        return TrainingCenter::query()
            ->whereIn('code', array_keys($this->centerCoords))
            ->orderBy('id')
            ->get();
    }

    private function ensureKits()
    {
        $defs = [
            ['code' => 'DEMO-KIT-01', 'name' => 'حقيبة ريادة الأعمال', 'sector' => 'أعمال', 'category' => 'ريادة', 'level' => 'مبتدئ', 'hours' => 36],
            ['code' => 'DEMO-KIT-02', 'name' => 'حقيبة التسويق الرقمي', 'sector' => 'رقمي', 'category' => 'تسويق', 'level' => 'متوسط', 'hours' => 30],
            ['code' => 'DEMO-KIT-03', 'name' => 'حقيبة إدارة المشاريع', 'sector' => 'أعمال', 'category' => 'إدارة', 'level' => 'متقدم', 'hours' => 40],
        ];

        foreach ($defs as $def) {
            TrainingKit::updateOrCreate(
                ['code' => $def['code']],
                array_merge($def, [
                    'type' => 'تدريبية',
                    'status' => 'active',
                    'is_active' => true,
                    'objective' => 'حقيبة تجريبية للاختبار',
                    'description' => 'بيانات وهمية لاختبار التكليف والعرض',
                ])
            );
        }

        return TrainingKit::query()
            ->whereIn('code', collect($defs)->pluck('code'))
            ->orWhereIn('code', ['KIT-001', 'KIT-002'])
            ->get();
    }

    private function ensureTrainers($centers)
    {
        $defs = [
            ['code' => 'DEMO-TRN-01', 'name' => 'المدرب علي محمود', 'spec' => 'ريادة أعمال'],
            ['code' => 'DEMO-TRN-02', 'name' => 'المدربة لينا فهد', 'spec' => 'تسويق رقمي'],
            ['code' => 'DEMO-TRN-03', 'name' => 'المدرب سامر عيسى', 'spec' => 'إدارة مشاريع'],
        ];

        foreach ($defs as $i => $def) {
            $center = $centers[$i % $centers->count()];
            Trainer::updateOrCreate(
                ['trainer_code' => $def['code']],
                [
                    'name' => $def['name'],
                    'training_center_id' => $center->id,
                    'phone' => '09' . str_pad((string) (50000000 + $i), 8, '0', STR_PAD_LEFT),
                    'email' => strtolower($def['code']) . '@system.com',
                    'specialization' => $def['spec'],
                    'classification' => 'معتمد',
                    'has_tot' => true,
                    'can_train' => true,
                    'status' => 'active',
                    'governorate' => $center->governorate,
                    'city' => $center->city,
                    'location_visibility' => 'internal',
                ]
            );
        }

        return Trainer::query()
            ->whereIn('trainer_code', collect($defs)->pluck('code'))
            ->get();
    }

    private function assignKitsToCentersAndTrainers($centers, $kits, $trainers): void
    {
        $now = now()->toDateString();

        foreach ($centers as $ci => $center) {
            $sync = [];
            foreach ($kits as $ki => $kit) {
                // كل مركز يأخذ حقائب متداخلة
                if ($ki % 2 === $ci % 2 || $ki === 0) {
                    $sync[$kit->id] = [
                        'is_assigned' => true,
                        'assigned_from' => $now,
                        'assigned_to' => null,
                        'notes' => 'Demo center assignment',
                    ];
                }
            }
            if ($sync) {
                $center->kits()->syncWithoutDetaching($sync);
            }
        }

        foreach ($trainers as $ti => $trainer) {
            $sync = [];
            foreach ($kits as $ki => $kit) {
                if ($ki === $ti % max(1, $kits->count()) || $ki === 0) {
                    $sync[$kit->id] = [
                        'is_authorized' => true,
                        'authorized_from' => $now,
                        'authorized_to' => null,
                        'notes' => 'Demo trainer authorization',
                    ];
                }
            }
            if ($sync) {
                $trainer->kits()->syncWithoutDetaching($sync);
            }
        }
    }

    private function ensureCourses($centers, $trainers, $kits)
    {
        $titles = [
            'دورة ريادة الأعمال — تجريبي',
            'دورة التسويق الرقمي — تجريبي',
            'دورة إدارة المشاريع — تجريبي',
        ];

        $courses = collect();
        foreach ($titles as $i => $title) {
            $center = $centers[$i % $centers->count()];
            $trainer = $trainers[$i % $trainers->count()];
            $kit = $kits[$i % $kits->count()];

            $course = TrainingCourse::updateOrCreate(
                ['course_code' => 'DEMO-CRS-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'training_center_id' => $center->id,
                    'trainer_id' => $trainer->id,
                    'training_kit_id' => $kit->id,
                    'title' => $title,
                    'delivery_mode' => 'offline',
                    'start_date' => now()->subDays(20)->toDateString(),
                    'end_date' => now()->addDays(10)->toDateString(),
                    'planned_hours' => 30,
                    'actual_hours' => 18,
                    'capacity' => 25,
                    'status' => 'ongoing',
                    'governorate' => $center->governorate,
                    'city' => $center->city,
                    'address' => $center->address,
                    'latitude' => $center->latitude,
                    'longitude' => $center->longitude,
                    'location_visibility' => 'public',
                    'venue_name' => $center->name,
                    'notes' => 'Demo smoke course',
                ]
            );
            $courses->push($course);
        }

        return $courses;
    }

    private function ensureTrainees($centers)
    {
        $trainees = collect();

        foreach ($this->arabicTrainees as $i => $row) {
            $center = $centers[$i % $centers->count()];
            $code = 'DEMO-TRA-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            $trainee = Trainee::updateOrCreate(
                ['trainee_code' => $code],
                [
                    'name' => $row['name'],
                    'mother_name' => $row['mother'],
                    'national_id' => '9' . str_pad((string) (200000000 + $i), 10, '0', STR_PAD_LEFT),
                    'phone' => '09' . str_pad((string) (70000000 + $i), 8, '0', STR_PAD_LEFT),
                    'email' => 'demo.trainee' . ($i + 1) . '@system.com',
                    'governorate' => $center->governorate,
                    'city' => $center->city,
                    'district' => 'تجريبي',
                    'address' => 'حي تجريبي ' . ($i + 1),
                    'location_visibility' => 'internal',
                    'birth_date' => sprintf('%04d-%02d-%02d', 1995 + ($i % 8), ($i % 12) + 1, ($i % 27) + 1),
                    'gender' => $row['gender'],
                    'education_level' => $i % 2 === 0 ? 'بكالوريوس' : 'معهد',
                    'status' => 'active',
                    'owned_training_center_id' => $center->id,
                    'notes' => 'Demo smoke trainee',
                ]
            );
            $trainees->push($trainee);
        }

        // Enrich any old seeded English trainees missing mother_name.
        Trainee::query()
            ->whereNull('mother_name')
            ->orWhere('mother_name', '')
            ->limit(50)
            ->get()
            ->each(function (Trainee $t, $idx) {
                $t->update([
                    'mother_name' => 'أم ' . ($t->name ?: ('متدرب ' . $t->id)),
                    'gender' => $t->gender ?: ($idx % 2 === 0 ? 'male' : 'female'),
                    'birth_date' => $t->birth_date ?: now()->subYears(22 + ($idx % 10))->toDateString(),
                ]);
            });

        return $trainees;
    }

    private function enrollAndGroup($courses, $trainees): void
    {
        foreach ($courses as $ci => $course) {
            $slice = $trainees->slice($ci * 4, 4);
            if ($slice->isEmpty()) {
                $slice = $trainees->take(4);
            }

            foreach ($slice as $ti => $trainee) {
                $exists = DB::table('training_course_trainee')
                    ->where('training_course_id', $course->id)
                    ->where('trainee_id', $trainee->id)
                    ->exists();

                $pivot = [
                    'attendance_status' => 'attended',
                    'result' => $ti % 3 === 0 ? 'passed' : 'pending',
                    'score' => 60 + ($ti * 7),
                    'attended_hours' => 12 + $ti,
                    'updated_at' => now(),
                ];

                if ($exists) {
                    DB::table('training_course_trainee')
                        ->where('training_course_id', $course->id)
                        ->where('trainee_id', $trainee->id)
                        ->update($pivot);
                } else {
                    DB::table('training_course_trainee')->insert(array_merge($pivot, [
                        'training_course_id' => $course->id,
                        'trainee_id' => $trainee->id,
                        'created_at' => now(),
                    ]));
                }
            }

            $groupA = CourseGroup::updateOrCreate(
                ['training_course_id' => $course->id, 'code' => 'G-A'],
                ['name' => 'الصف أ', 'capacity' => 15, 'notes' => 'صف تجريبي أ']
            );
            $groupB = CourseGroup::updateOrCreate(
                ['training_course_id' => $course->id, 'code' => 'G-B'],
                ['name' => 'الصف ب', 'capacity' => 15, 'notes' => 'صف تجريبي ب']
            );

            $ids = $slice->values();
            foreach ($ids as $gi => $trainee) {
                $gid = $gi < 2 ? $groupA->id : $groupB->id;
                DB::table('training_course_trainee')
                    ->where('training_course_id', $course->id)
                    ->where('trainee_id', $trainee->id)
                    ->update(['course_group_id' => $gid]);
            }
        }
    }

    private function ensureCertificates($courses): void
    {
        $n = 0;
        foreach ($courses as $course) {
            $rows = DB::table('training_course_trainee')
                ->where('training_course_id', $course->id)
                ->where('result', 'passed')
                ->limit(2)
                ->get();

            foreach ($rows as $row) {
                $n++;
                $number = 'DEMO-CERT-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
                Certificate::updateOrCreate(
                    ['certificate_number' => $number],
                    [
                        'trainee_id' => $row->trainee_id,
                        'training_center_id' => $course->training_center_id,
                        'trainer_id' => $course->trainer_id,
                        'training_kit_id' => $course->training_kit_id,
                        'training_course_id' => $course->id,
                        'certificate_type' => 'pass',
                        'result' => 'passed',
                        'score' => $row->score,
                        'hours_awarded' => (int) ($row->attended_hours ?: 20),
                        'status' => 'approved',
                        'issue_date' => now()->toDateString(),
                        'is_verified' => true,
                        'reference_number' => 'REF-DEMO-' . $n,
                        'verification_code' => 'VRDEMO' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                    ]
                );
            }
        }
    }

    private function ensureDemoUsers($center, $trainer, $trainee): void
    {
        if ($center) {
            User::updateOrCreate(
                ['email' => 'center@system.com'],
                [
                    'name' => 'Training Center User',
                    'password' => Hash::make('12345678'),
                    'entity_type' => 'center_user',
                    'training_center_id' => $center->id,
                    'trainer_id' => null,
                    'trainee_id' => null,
                    'is_active' => true,
                ]
            )->syncRoles(['center_user']);
        }

        if ($trainer) {
            User::updateOrCreate(
                ['email' => 'trainer@system.com'],
                [
                    'name' => 'Trainer User',
                    'password' => Hash::make('12345678'),
                    'entity_type' => 'trainer_user',
                    'training_center_id' => $trainer->training_center_id,
                    'trainer_id' => $trainer->id,
                    'trainee_id' => null,
                    'is_active' => true,
                ]
            )->syncRoles(['trainer_user']);
        }

        if ($trainee) {
            User::updateOrCreate(
                ['email' => 'trainee@system.com'],
                [
                    'name' => 'Trainee User',
                    'password' => Hash::make('12345678'),
                    'entity_type' => 'trainee_user',
                    'training_center_id' => null,
                    'trainer_id' => null,
                    'trainee_id' => $trainee->id,
                    'is_active' => true,
                ]
            )->syncRoles(['trainee_user']);
        }

        User::updateOrCreate(
            ['email' => 'projects@system.com'],
            [
                'name' => 'Project Services Manager',
                'password' => Hash::make('12345678'),
                'entity_type' => 'project_services_manager',
                'is_active' => true,
            ]
        )->syncRoles(['project_services_manager']);
    }

    private function printSummary(): void
    {
        $this->command?->newLine();
        $this->command?->info('✅ ملخص البيانات التجريبية:');
        $this->command?->line('  مراكز بإحداثيات: ' . TrainingCenter::whereNotNull('latitude')->count());
        $this->command?->line('  حقائب: ' . TrainingKit::count());
        $this->command?->line('  ربط مركز↔حقيبة: ' . DB::table('training_center_training_kit')->count());
        $this->command?->line('  مدربون: ' . Trainer::count());
        $this->command?->line('  ربط مدرب↔حقيبة: ' . DB::table('trainer_training_kit')->count());
        $this->command?->line('  متدربون (باسم أم): ' . Trainee::whereNotNull('mother_name')->where('mother_name', '!=', '')->count());
        $this->command?->line('  دورات DEMO: ' . TrainingCourse::where('course_code', 'like', 'DEMO-%')->count());
        $this->command?->line('  صفوف: ' . CourseGroup::count());
        $this->command?->line('  شهادات DEMO: ' . Certificate::where('certificate_number', 'like', 'DEMO-%')->count());
        $this->command?->newLine();
        $this->command?->line('حسابات الاختبار (كلمة المرور 12345678):');
        $this->command?->line('  projects@system.com | center@system.com | trainer@system.com | trainee@system.com');
    }
}
