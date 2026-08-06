<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchDataBackfillSeeder extends Seeder
{
    /** @var list<string> */
    private array $tables = [
        'training_centers',
        'trainers',
        'trainees',
        'training_courses',
        'certificates',
    ];

    public function run(): void
    {
        $damascusBranch = Branch::query()->where('code', 'BR-DAMASCUS')->first();
        if (!$damascusBranch) {
            return;
        }

        foreach ($this->tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            DB::table($table)
                ->whereNull('branch_id')
                ->update([
                    'branch_id' => $damascusBranch->id,
                    'governorate_id' => $damascusBranch->governorate_id,
                ]);
        }
    }
}
