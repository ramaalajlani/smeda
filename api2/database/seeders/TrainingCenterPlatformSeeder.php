<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingCenter;
use App\Models\TrainingCenterPlatform;

class TrainingCenterPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $center = TrainingCenter::query()
            ->where('code', 'TC-001')
            ->first();

        if (!$center) {
            $this->command?->warn('Training center TC-001 not found. TrainingCenterPlatformSeeder skipped.');
            return;
        }

        $rows = [
            [
                'platform_name' => 'Zoom',
                'platform_url' => 'https://zoom.us/',
                'status' => 'approved',
                'approved_at' => now()->subMonths(2)->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'notes' => 'Approved online platform',
            ],
            [
                'platform_name' => 'Google Meet',
                'platform_url' => 'https://meet.google.com/',
                'status' => 'approved',
                'approved_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addMonths(8)->toDateString(),
                'notes' => 'Approved backup platform',
            ],
        ];

        foreach ($rows as $row) {
            TrainingCenterPlatform::updateOrCreate(
                [
                    'training_center_id' => $center->id,
                    'platform_name' => $row['platform_name'],
                ],
                array_merge($row, [
                    'training_center_id' => $center->id,
                ])
            );
        }
    }
}