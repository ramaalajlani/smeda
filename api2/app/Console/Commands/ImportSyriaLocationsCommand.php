<?php

namespace App\Console\Commands;

use App\Services\Gis\SyriaLocationsImportService;
use Illuminate\Console\Command;

class ImportSyriaLocationsCommand extends Command
{
    protected $signature = 'syria-locations:import
                            {--file= : Path to XLSX or CSV file}
                            {--chunk=500 : Rows per read/upsert batch}';

    protected $description = 'Import Syria GIS location communities from Excel/CSV without loading the full file into memory';

    public function handle(SyriaLocationsImportService $importer): int
    {
        $file = $this->option('file') ?: $importer->defaultFilePath();
        $chunk = max(100, min(2000, (int) $this->option('chunk')));

        if (!is_file($file)) {
            $this->error("File not found: {$file}");
            $this->line("Place 'Syria Location Int.xlsx' or 'Syria Location Int.csv' in storage/geo/");

            return self::FAILURE;
        }

        try {
            $importer->import($file, $this->output, $chunk);
        } catch (\Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
