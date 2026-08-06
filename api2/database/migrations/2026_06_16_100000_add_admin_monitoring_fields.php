<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module', 80)->nullable()->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->string('description', 500)->nullable()->after('module');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'module')) {
                $table->dropColumn('module');
            }
            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
