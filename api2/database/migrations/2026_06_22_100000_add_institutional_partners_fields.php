<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_offices', function (Blueprint $table) {
            if (!Schema::hasColumn('consultant_offices', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('consultant_offices', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('consultant_offices', 'supervised_by_type')) {
                $table->string('supervised_by_type', 50)->default('consultant_union')->after('approved_at');
            }
            if (!Schema::hasColumn('consultant_offices', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('funding_partners', function (Blueprint $table) {
            if (!Schema::hasColumn('funding_partners', 'license_number')) {
                $table->string('license_number')->nullable()->after('partner_type');
            }
            if (!Schema::hasColumn('funding_partners', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('funding_partners', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('funding_partners', 'supervised_by_type')) {
                $table->string('supervised_by_type', 50)->default('central_bank')->after('approved_at');
            }
            if (!Schema::hasColumn('funding_partners', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        // Expand status values; SQLite stores enum as text anyway.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE consultant_offices MODIFY status ENUM('pending','approved','active','inactive','suspended','rejected') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE funding_partners MODIFY status ENUM('pending','approved','active','inactive','suspended','rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('consultant_offices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'supervised_by_type']);
        });

        Schema::table('funding_partners', function (Blueprint $table) {
            $table->dropColumn('license_number');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'supervised_by_type']);
        });
    }
};
