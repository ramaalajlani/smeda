<?php

namespace App\Console\Commands;

use App\Services\OldDataImport\OldDataImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OldDataImportCommand extends Command
{
    protected $signature = 'old-data:import
                            {--dry-run : Analyze and simulate import without writing}
                            {--analyze-sql : Analyze SQL dump files only (no DB required)}
                            {--source=both : authority3|entrep|both}
                            {--backup : Remind to backup target DB before import}';

    protected $description = 'Safely import legacy data from old_authority3 and old_entrep databases';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $analyzeSql = (bool) $this->option('analyze-sql');

        if ($this->option('backup') && ! $dryRun && ! $analyzeSql) {
            $this->warn('Ensure you have a backup of the target database before continuing.');
            if (! $this->confirm('Did you backup the target database?', false)) {
                $this->error('Aborted. Create a backup first.');

                return self::FAILURE;
            }
        }

        $importer = OldDataImporter::make($dryRun || $analyzeSql);

        if ($analyzeSql) {
            $this->info('Analyzing SQL dump files...');
            $importer->analyzeSqlDumps();
            $this->printReport($importer, true);

            return self::SUCCESS;
        }

        if (! $this->verifyOldConnections()) {
            return self::FAILURE;
        }

        if (! Schema::hasTable('legacy_import_id_mappings')) {
            $this->error('Run migrations first: php artisan migrate');

            return self::FAILURE;
        }

        $this->info($dryRun ? 'DRY RUN — no data will be written.' : 'LIVE IMPORT — inserting data...');

        $runId = null;
        if (! $dryRun) {
            $runId = DB::table('legacy_import_runs')->insertGetId([
                'source' => $this->option('source'),
                'dry_run' => false,
                'status' => 'running',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            $importer->run();
        } catch (\Throwable $e) {
            if ($runId) {
                DB::table('legacy_import_runs')->where('id', $runId)->update([
                    'status' => 'failed',
                    'errors_count' => 1,
                    'report' => json_encode(['fatal' => $e->getMessage()]),
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $report = $importer->report()->toArray();

        if ($runId) {
            DB::table('legacy_import_runs')->where('id', $runId)->update([
                'status' => 'completed',
                'tables_mapped' => $report['summary']['tables_mapped'] ?? 0,
                'tables_unmapped' => $report['summary']['tables_unmapped'] ?? 0,
                'rows_read' => $report['summary']['rows_read'] ?? 0,
                'rows_inserted' => $report['summary']['rows_inserted'] ?? 0,
                'rows_updated' => $report['summary']['rows_updated'] ?? 0,
                'rows_skipped' => $report['summary']['rows_skipped'] ?? 0,
                'errors_count' => $report['summary']['errors_count'] ?? 0,
                'report' => json_encode($report, JSON_UNESCAPED_UNICODE),
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->printReport($importer, $dryRun);

        return ($report['summary']['errors_count'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function verifyOldConnections(): bool
    {
        foreach (['old_authority3', 'old_entrep'] as $connection) {
            try {
                DB::connection($connection)->getPdo();
                $db = DB::connection($connection)->getDatabaseName();
                $this->line("Connected: {$connection} → {$db}");
            } catch (\Throwable $e) {
                $this->error("Cannot connect to {$connection}: ".$e->getMessage());
                $this->line('');
                $this->line('Import SQL dumps first:');
                $this->line('  mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_authority3;"');
                $this->line('  mysql -u root old_authority3 < "u142331648_authority3 (2).sql"');
                $this->line('  mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_entrep;"');
                $this->line('  mysql -u root old_entrep < u142331648_entrep_db.sql');

                return false;
            }
        }

        return true;
    }

    private function printReport(OldDataImporter $importer, bool $dryRun): void
    {
        $report = $importer->report()->toArray();
        $s = $report['summary'];

        $this->newLine();
        $this->info($dryRun ? '=== DRY RUN REPORT ===' : '=== IMPORT REPORT ===');
        $this->table(['Metric', 'Count'], [
            ['Old tables (SQL analysis)', $s['tables_old'] ?? '-'],
            ['Mapped tables', $s['tables_mapped'] ?? '-'],
            ['Unmapped tables', $s['tables_unmapped'] ?? '-'],
            ['Rows read', $s['rows_read']],
            ['Rows inserted', $s['rows_inserted']],
            ['Rows updated', $s['rows_updated']],
            ['Rows skipped (duplicates)', $s['rows_skipped']],
            ['Invalid phones (rejected)', $s['invalid_phones_count'] ?? 0],
            ['Errors', $s['errors_count']],
        ]);

        if (! empty($report['rejected_phones'])) {
            $this->warn('Rejected phone samples (first '.count($report['rejected_phones']).'):');
            foreach ($report['rejected_phones'] as $sample) {
                $source = $sample['source'] ?? 'unknown';
                $oldId = $sample['old_id'] ?? '-';
                $raw = $sample['raw_value'] ?? '';
                $preview = mb_strlen($raw) > 80 ? mb_substr($raw, 0, 80).'…' : $raw;
                $this->line("  [{$source}#{$oldId}] {$preview}");
            }
        }

        if (! empty($report['unmapped_tables'])) {
            $this->warn('Unmapped tables:');
            foreach ($report['unmapped_tables'] as $table => $note) {
                $this->line("  - {$table}: {$note}");
            }
        }

        if (! empty($report['missing_files'])) {
            $this->warn('Missing attachment files: '.count($report['missing_files']));
        }

        if (! empty($report['errors'])) {
            $this->error('Errors:');
            foreach (array_slice($report['errors'], 0, 20) as $error) {
                $this->line("  [{$error['table']}] {$error['message']}");
            }
        }
    }
}
