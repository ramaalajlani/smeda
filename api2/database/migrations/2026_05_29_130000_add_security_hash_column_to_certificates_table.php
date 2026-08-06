<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('certificates', 'security_hash')) {
            return;
        }

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('security_hash', 64)->nullable()->after('verification_code');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('certificates', 'security_hash')) {
            return;
        }

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('security_hash');
        });
    }
};
