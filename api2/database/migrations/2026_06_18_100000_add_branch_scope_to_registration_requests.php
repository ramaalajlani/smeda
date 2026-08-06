<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'training_center_registration_requests',
        'trainer_registration_requests',
        'trainee_registration_requests',
        'course_registration_requests',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'governorate_id')) {
                    $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
                }
                if (!Schema::hasColumn($tableName, 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
            });
        }

        $this->backfillFromTable('trainer_registration_requests', 'training_center_id', 'training_centers');
        $this->backfillFromTable('course_registration_requests', 'training_course_id', 'training_courses');
        $this->backfillFromUsers('training_center_registration_requests');
        $this->backfillFromUsers('trainee_registration_requests');
    }

    private function backfillFromTable(string $requestTable, string $foreignColumn, string $sourceTable): void
    {
        if (!Schema::hasTable($requestTable) || !Schema::hasTable($sourceTable)) {
            return;
        }

        DB::table($requestTable)
            ->whereNull('branch_id')
            ->whereNotNull($foreignColumn)
            ->orderBy('id')
            ->each(function ($row) use ($requestTable, $foreignColumn, $sourceTable) {
                $source = DB::table($sourceTable)
                    ->where('id', $row->{$foreignColumn})
                    ->first(['branch_id', 'governorate_id']);

                if ($source?->branch_id) {
                    DB::table($requestTable)->where('id', $row->id)->update([
                        'branch_id' => $source->branch_id,
                        'governorate_id' => $source->governorate_id,
                    ]);
                }
            });
    }

    private function backfillFromUsers(string $requestTable): void
    {
        if (!Schema::hasTable($requestTable) || !Schema::hasTable('users')) {
            return;
        }

        DB::table($requestTable)
            ->whereNull('branch_id')
            ->whereNotNull('submitted_by_user_id')
            ->orderBy('id')
            ->each(function ($row) use ($requestTable) {
                $user = DB::table('users')
                    ->where('id', $row->submitted_by_user_id)
                    ->first(['branch_id', 'governorate_id']);

                if ($user?->branch_id) {
                    DB::table($requestTable)->where('id', $row->id)->update([
                        'branch_id' => $user->branch_id,
                        'governorate_id' => $user->governorate_id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    $table->dropConstrainedForeignId('branch_id');
                }
                if (Schema::hasColumn($tableName, 'governorate_id')) {
                    $table->dropConstrainedForeignId('governorate_id');
                }
            });
        }
    }
};
