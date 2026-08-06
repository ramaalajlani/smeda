<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_course_trainee', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_course_id')
                ->constrained('training_courses')
                ->cascadeOnDelete();

            $table->foreignId('trainee_id')
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->enum('attendance_status', [
                'registered',
                'attended',
                'absent',
                'withdrawn',
                'completed',
            ])->default('registered');

            $table->enum('result', [
                'pending',
                'passed',
                'failed',
                'attendance_only',
            ])->default('pending');

            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('attended_hours')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['training_course_id', 'trainee_id'],
                'tct_course_trainee_uniq'
            );

            $table->index(
                ['training_course_id', 'attendance_status', 'id'],
                'tct_course_att_id_idx'
            );

            $table->index(
                ['training_course_id', 'result', 'id'],
                'tct_course_res_id_idx'
            );

            $table->index(
                ['trainee_id', 'result', 'id'],
                'tct_trainee_res_id_idx'
            );

            $table->index(
                ['trainee_id', 'attendance_status', 'id'],
                'tct_trainee_att_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_course_trainee');
    }
};