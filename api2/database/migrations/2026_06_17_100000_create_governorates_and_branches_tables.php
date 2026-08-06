<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('code', 20)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
        Schema::dropIfExists('governorates');
    }
};
