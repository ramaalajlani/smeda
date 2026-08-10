<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funding_application_details', function (Blueprint $table) {
            if (!Schema::hasColumn('funding_application_details', 'extra_data')) {
                $table->json('extra_data')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funding_application_details', function (Blueprint $table) {
            if (Schema::hasColumn('funding_application_details', 'extra_data')) {
                $table->dropColumn('extra_data');
            }
        });
    }
};
