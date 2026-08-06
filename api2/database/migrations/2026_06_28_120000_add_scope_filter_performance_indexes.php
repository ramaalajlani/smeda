<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for branch/governorate scoped filters used in dashboards and lists.
 * FK columns already have single-column indexes; these speed up WHERE branch_id AND status queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addCompositeIndex('trainers', ['branch_id', 'status']);
        $this->addCompositeIndex('trainees', ['branch_id', 'status']);
        $this->addCompositeIndex('training_courses', ['branch_id', 'status']);
        $this->addCompositeIndex('certificates', ['branch_id', 'status']);
        $this->addCompositeIndex('incubators', ['branch_id', 'status']);
        $this->addCompositeIndex('financial_records', ['branch_id', 'status']);
        $this->addCompositeIndex('agreements', ['branch_id', 'status']);
        $this->addCompositeIndex('needs', ['status', 'created_at']);
        $this->addCompositeIndex('incubation_applications', ['incubator_id', 'status']);

        $this->addSingleIndex('training_centers', 'branch_id');
        $this->addSingleIndex('users', 'branch_id');
        $this->addSingleIndex('users', 'governorate_id');
    }

    public function down(): void
    {
        $this->dropCompositeIndex('trainers', ['branch_id', 'status']);
        $this->dropCompositeIndex('trainees', ['branch_id', 'status']);
        $this->dropCompositeIndex('training_courses', ['branch_id', 'status']);
        $this->dropCompositeIndex('certificates', ['branch_id', 'status']);
        $this->dropCompositeIndex('incubators', ['branch_id', 'status']);
        $this->dropCompositeIndex('financial_records', ['branch_id', 'status']);
        $this->dropCompositeIndex('agreements', ['branch_id', 'status']);
        $this->dropCompositeIndex('needs', ['status', 'created_at']);
        $this->dropCompositeIndex('incubation_applications', ['incubator_id', 'status']);

        $this->dropSingleIndex('training_centers', 'branch_id');
        $this->dropSingleIndex('users', 'branch_id');
        $this->dropSingleIndex('users', 'governorate_id');
    }

    /** @param list<string> $columns */
    private function addCompositeIndex(string $table, array $columns): void
    {
        if (!$this->columnsExist($table, $columns)) {
            return;
        }

        $indexName = $table.'_'.implode('_', $columns).'_idx';

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    /** @param list<string> $columns */
    private function dropCompositeIndex(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $indexName = $table.'_'.implode('_', $columns).'_idx';

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function addSingleIndex(string $table, string $column): void
    {
        if (!$this->columnsExist($table, [$column])) {
            return;
        }

        $indexName = $table.'_'.$column.'_perf_idx';

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
            $table->index($column, $indexName);
        });
    }

    private function dropSingleIndex(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $indexName = $table.'_'.$column.'_perf_idx';

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /** @param list<string> $columns */
    private function columnsExist(string $table, array $columns): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select('PRAGMA index_list('.DB::getPdo()->quote($table).')');

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $rows = DB::select(
                'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ? LIMIT 1',
                [$table, $indexName]
            );

            return ! empty($rows);
        }

        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $indexName]
        );

        return ! empty($rows);
    }
};
