<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syria_locations', function (Blueprint $table) {
            $table->id();
            $table->string('gov_pcode', 10)->index();
            $table->string('gov_name_en', 60);
            $table->string('gov_name_ar', 60);
            $table->string('district_pcode', 10)->index();
            $table->string('district_name_en', 80);
            $table->string('district_name_ar', 80);
            $table->string('subdistrict_pcode', 10)->index();
            $table->string('subdistrict_name_en', 80);
            $table->string('subdistrict_name_ar', 80);
            $table->string('community_pcode', 10)->unique();
            $table->string('community_name_en', 120);
            $table->string('community_name_ar', 120);
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);

            $table->index(['gov_pcode', 'district_pcode']);
            $table->index(['district_pcode', 'subdistrict_pcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syria_locations');
    }
};
