<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;

class TraineeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 50) as $i) {
            Trainee::updateOrCreate(
                ['trainee_code' => 'TR-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => "Trainee $i",
                    'national_id' => 'NID' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'phone' => '0933000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'email' => "trainee{$i}@example.com",
                    'city' => $i % 2 === 0 ? 'Damascus' : 'Aleppo',
                    'address' => 'Sample address',
                    'birth_date' => now()->subYears(rand(20, 35))->toDateString(),
                    'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'education_level' => 'university',
                    'status' => 'active',
                    'notes' => 'Seeded trainee',
                ]
            );
        }
    }
}