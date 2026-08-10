<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funding_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('funding_applications', 'financing_mode')) {
                $table->string('financing_mode', 32)->nullable()->after('financing_type');
            }
            if (!Schema::hasColumn('funding_applications', 'project_status')) {
                $table->string('project_status', 32)->nullable()->after('business_stage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funding_applications', function (Blueprint $table) {
            if (Schema::hasColumn('funding_applications', 'financing_mode')) {
                $table->dropColumn('financing_mode');
            }
            if (Schema::hasColumn('funding_applications', 'project_status')) {
                $table->dropColumn('project_status');
            }
        });
    }
};
