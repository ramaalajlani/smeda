<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المواد/الوحدات التدريبية داخل الحقيبة
        Schema::create('kit_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_kit_id')
                ->constrained('training_kits')
                ->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('hours')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('objectives')->nullable();
            $table->string('evaluation_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_kit_id', 'sort_order', 'id'], 'km_kit_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_materials');
    }
};
