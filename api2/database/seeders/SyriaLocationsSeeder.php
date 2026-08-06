<?php

namespace Database\Seeders;

use App\Services\Gis\SyriaLocationsImportService;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput;

class SyriaLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $importer = app(SyriaLocationsImportService::class);
        $file = $importer->defaultFilePath();

        if (!is_file($file)) {
            $this->command?->error("File not found: {$file}");
            $this->command?->line("Place 'Syria Location Int.xlsx' or 'Syria Location Int.csv' in storage/geo/ and retry.");
            $this->command?->line('Or run: php artisan syria-locations:import');

            return;
        }

        $output = $this->command?->getOutput() ?? new ConsoleOutput();
        $importer->import($file, $output);
    }
}
