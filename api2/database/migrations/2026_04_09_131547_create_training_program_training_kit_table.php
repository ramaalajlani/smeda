<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_program_training_kit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_program_id')
                ->constrained('training_programs')
                ->cascadeOnDelete();

            $table->foreignId('training_kit_id')
                ->constrained('training_kits')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->unique(
                ['training_program_id', 'training_kit_id'],
                'tptk_prog_kit_uniq'
            );

            $table->index(
                ['training_program_id', 'sort_order', 'id'],
                'tptk_prog_sort_id_idx'
            );

            $table->index(
                ['training_kit_id', 'id'],
                'tptk_kit_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_program_training_kit');
    }
};