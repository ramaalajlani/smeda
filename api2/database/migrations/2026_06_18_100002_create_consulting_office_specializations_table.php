<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_office_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('consulting_offices')->cascadeOnDelete();
            $table->string('category_code', 10);
            $table->string('sector', 50)->nullable();  // agriculture/industry/trade/service/all
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->string('sample_work_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->unique(['office_id', 'category_code'], 'office_category_unique');
            $table->index(['category_code', 'is_verified'], 'cos_cat_verified_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_office_specializations');
    }
};
