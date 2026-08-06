<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_training_kit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trainer_id')
                ->constrained('trainers')
                ->cascadeOnDelete();

            $table->foreignId('training_kit_id')
                ->constrained('training_kits')
                ->cascadeOnDelete();

            $table->boolean('is_authorized')->default(true);
            $table->date('authorized_from')->nullable();
            $table->date('authorized_to')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['trainer_id', 'training_kit_id'], 'trainer_kit_unique');
            $table->index(['trainer_id', 'is_authorized', 'id']);
            $table->index(['training_kit_id', 'is_authorized', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_training_kit');
    }
};