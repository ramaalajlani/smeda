<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_module_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_course_id')
                ->constrained('training_courses')
                ->cascadeOnDelete();

            $table->foreignId('trainee_id')
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->foreignId('program_module_id')
                ->constrained('training_program_modules')
                ->cascadeOnDelete();

            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->decimal('pass_mark', 5, 2)->nullable();

            $table->enum('result', ['pending', 'passed', 'failed'])
                ->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['training_course_id', 'trainee_id', 'program_module_id'],
                'tms_course_trainee_module_uniq'
            );

            $table->index(['training_course_id', 'program_module_id', 'id'], 'tms_course_module_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_module_scores');
    }
};
