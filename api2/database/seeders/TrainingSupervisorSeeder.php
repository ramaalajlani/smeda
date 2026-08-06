<?php

namespace Database\Seeders;

use App\Models\TrainingSupervisor;
use Illuminate\Database\Seeder;

class TrainingSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        $ministries = [
            [
                'name' => 'وزارة التربية',
                'code' => 'MOE',
                'type' => 'ministry',
                'is_active' => true,
                'notes' => 'الجهة الوزارية المشرفة على التدريب التربوي.',
            ],
            [
                'name' => 'وزارة الصناعة',
                'code' => 'MOI-IND',
                'type' => 'ministry',
                'is_active' => true,
                'notes' => 'الجهة الوزارية المشرفة على التدريب الصناعي.',
            ],
            [
                'name' => 'وزارة التجارة الداخلية وحماية المستهلك',
                'code' => 'MOIT',
                'type' => 'ministry',
                'is_active' => true,
                'notes' => 'الجهة الوزارية المشرفة على التدريب المرتبط بالتجارة الداخلية.',
            ],
        ];

        foreach ($ministries as $ministryData) {
            $ministry = TrainingSupervisor::query()->updateOrCreate(
                ['code' => $ministryData['code']],
                $ministryData
            );

            TrainingSupervisor::query()->updateOrCreate(
                ['code' => $ministryData['code'] . '-DIR-TRAINING'],
                [
                    'name' => 'مديرية التدريب - ' . $ministryData['name'],
                    'type' => 'directorate',
                    'parent_id' => $ministry->id,
                    'is_active' => true,
                    'notes' => 'مديرية تدريب تابعة للوزارة.',
                ]
            );
        }

        TrainingSupervisor::query()->updateOrCreate(
            ['code' => 'MOIT-CENTRAL'],
            [
                'name' => 'وزارة التجارة الداخلية وحماية المستهلك - الجهة المركزية',
                'type' => 'internal_entity',
                'parent_id' => TrainingSupervisor::query()->where('code', 'MOIT')->value('id'),
                'is_active' => true,
                'notes' => 'جهة افتراضية للمراكز غير المرتبطة بجهة مشرفة محددة.',
            ]
        );
    }
}
