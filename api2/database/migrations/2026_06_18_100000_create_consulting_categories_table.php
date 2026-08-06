<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();        // CON-01 .. CON-12
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->json('applicable_sectors')->nullable(); // ['agriculture','industry','trade','service']
            $table->boolean('requires_isic4')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_categories');
    }
};
