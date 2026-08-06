<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('scope_type')->default('national')->after('agreement_type');
            $table->foreignId('governorate_id')->nullable()->after('scope_type')->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('governorate_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('governorate_id');
            $table->dropColumn('scope_type');
        });
    }
};
