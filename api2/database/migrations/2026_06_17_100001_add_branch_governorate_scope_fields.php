<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $scopedTables = [
        'training_centers',
        'trainers',
        'trainees',
        'training_courses',
        'certificates',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'governorate_id')) {
                $table->foreignId('governorate_id')->nullable()->after('entity_type')->constrained('governorates')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('governorate_id')->constrained('branches')->nullOnDelete();
            }
        });

        foreach ($this->scopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'governorate_id')) {
                    $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
                }
                if (!Schema::hasColumn($tableName, 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
            });
        }

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'manager_user_id')) {
                $table->foreignId('manager_user_id')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'manager_user_id')) {
                $table->dropConstrainedForeignId('manager_user_id');
            }
        });

        foreach ($this->scopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    $table->dropConstrainedForeignId('branch_id');
                }
                if (Schema::hasColumn($tableName, 'governorate_id')) {
                    $table->dropConstrainedForeignId('governorate_id');
                }
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
            if (Schema::hasColumn('users', 'governorate_id')) {
                $table->dropConstrainedForeignId('governorate_id');
            }
        });
    }
};
