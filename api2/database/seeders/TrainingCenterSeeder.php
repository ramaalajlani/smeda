<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingCenter;
use App\Models\TrainingSupervisor;

class TrainingCenterSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSupervisorId = TrainingSupervisor::query()
            ->where('code', 'MOIT-CENTRAL')
            ->value('id');

        $rows = [
            [
                'code' => 'TC-001',
                'name' => 'Damascus Training Center',
                'city' => 'Damascus',
                'address' => 'Damascus - Main Street',
                'phone' => '0111111111',
                'email' => 'damascus.center@example.com',
                'classification' => 'first_class',
                'accreditation_status' => 'approved',
                'supports_offline_training' => true,
                'supports_online_training' => true,
                'accreditation_start_date' => now()->subMonths(6)->toDateString(),
                'accreditation_end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
                'supervisor_id' => $defaultSupervisorId,
                'notes' => 'Seeded training center',
            ],
            [
                'code' => 'TC-002',
                'name' => 'Aleppo Skills Hub',
                'city' => 'Aleppo',
                'address' => 'Aleppo - Business District',
                'phone' => '0222222222',
                'email' => 'aleppo.center@example.com',
                'classification' => 'second_class',
                'accreditation_status' => 'approved',
                'supports_offline_training' => true,
                'supports_online_training' => false,
                'accreditation_start_date' => now()->subMonths(4)->toDateString(),
                'accreditation_end_date' => now()->addMonths(10)->toDateString(),
                'is_active' => true,
                'supervisor_id' => $defaultSupervisorId,
                'notes' => 'Seeded training center',
            ],
        ];

        foreach ($rows as $row) {
            TrainingCenter::updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}