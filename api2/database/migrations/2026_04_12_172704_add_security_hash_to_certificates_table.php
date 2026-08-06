<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * No-op placeholder — security_hash is added by 2026_05_29_130000_add_security_hash_column_to_certificates_table.
     * Kept for environments where this migration already ran in history.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
        });
    }
};
