<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Certificate;
use App\Models\Governorate;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Support\BranchDataScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateBranchSeeder extends Seeder
{
    public function run(): void
    {
        foreach (BranchDataScope::SYRIAN_GOVERNORATES as $gov) {
            $governorate = Governorate::query()->updateOrCreate(
                ['code' => $gov['code']],
                [
                    'name_ar' => $gov['name_ar'],
                    'name_en' => $gov['name_en'],
                    'is_active' => true,
                ]
            );

            Branch::query()->updateOrCreate(
                ['code' => 'BR-' . strtoupper($gov['code'])],
                [
                    'governorate_id' => $governorate->id,
                    'name' => 'فرع ' . $gov['name_ar'],
                    'is_active' => true,
                ]
            );
        }
    }
}
