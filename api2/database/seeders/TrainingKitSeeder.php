<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingKit;

class TrainingKitSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'KIT-001',
                'name' => 'Project Management',
                'sector' => 'business',
                'category' => 'management',
                'level' => 'advanced',
                'hours' => 40,
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'code' => 'KIT-002',
                'name' => 'Digital Marketing',
                'sector' => 'digital',
                'category' => 'marketing',
                'level' => 'intermediate',
                'hours' => 30,
                'status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            TrainingKit::updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}