<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainer;
use App\Models\TrainingCenter;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $centers = TrainingCenter::query()->pluck('id');

        if ($centers->isEmpty()) {
            $this->command?->warn('No training centers found. TrainerSeeder skipped.');
            return;
        }

        foreach (range(1, 10) as $i) {
            Trainer::updateOrCreate(
                ['trainer_code' => 'TRN-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT)],
                [
                    'training_center_id' => $centers->random(),
                    'name' => "Trainer $i",
                    'phone' => '0999000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'email' => "trainer{$i}@example.com",
                    'specialization' => $i % 2 === 0 ? 'Management' : 'Marketing',
                    'classification' => $i % 2 === 0 ? 'senior' : 'junior',
                    'has_tot' => true,
                    'tot_certificate_number' => 'TOT-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'tot_certificate_source' => 'Authority',
                    'tot_issue_date' => now()->subMonths(6)->toDateString(),
                    'tot_expiry_date' => now()->addYears(2)->toDateString(),
                    'can_train' => true,
                    'can_evaluate' => true,
                    'status' => 'active',
                    'accreditation_start_date' => now()->subMonths(3)->toDateString(),
                    'accreditation_end_date' => now()->addYear()->toDateString(),
                    'bio' => 'Sample trainer bio',
                    'notes' => 'Seeded trainer',
                ]
            );
        }
    }
}