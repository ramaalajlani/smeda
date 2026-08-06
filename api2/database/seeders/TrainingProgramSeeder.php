<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingProgram;
use App\Models\TrainingKit;

class TrainingProgramSeeder extends Seeder
{
    public function run(): void
    {
        $program = TrainingProgram::updateOrCreate(
            ['code' => 'TP-001'],
            [
                'name' => 'Entrepreneurship Program',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        $kits = TrainingKit::query()->pluck('id')->all();

        $syncData = [];
        foreach ($kits as $index => $kitId) {
            $syncData[$kitId] = [
                'sort_order' => $index + 1,
                'is_required' => true,
            ];
        }

        if (!empty($syncData)) {
            $program->kits()->sync($syncData);
        }
    }
}