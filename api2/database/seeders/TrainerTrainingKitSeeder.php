<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainer;
use App\Models\TrainingKit;

class TrainerTrainingKitSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = Trainer::query()->get();
        $kits = TrainingKit::query()->get();

        if ($trainers->isEmpty() || $kits->isEmpty()) {
            $this->command?->warn('No trainers or training kits found. TrainerTrainingKitSeeder skipped.');
            return;
        }

        foreach ($trainers as $trainer) {
            $syncData = [];

            foreach ($kits as $kit) {
                $syncData[$kit->id] = [
                    'is_authorized' => true,
                    'authorized_from' => now()->subMonths(2)->toDateString(),
                    'authorized_to' => now()->addYear()->toDateString(),
                    'notes' => 'Authorized by seeder',
                ];
            }

            $trainer->kits()->sync($syncData);
        }
    }
}