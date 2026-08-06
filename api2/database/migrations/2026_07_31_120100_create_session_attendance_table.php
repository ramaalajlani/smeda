<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_session_id')
                ->constrained('course_sessions')
                ->cascadeOnDelete();

            $table->foreignId('trainee_id')
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->enum('status', ['present', 'absent', 'late', 'excused'])
                ->default('present');

            $table->unsignedInteger('minutes_attended')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['course_session_id', 'trainee_id'],
                'sa_session_trainee_uniq'
            );

            $table->index(['trainee_id', 'status', 'id'], 'sa_trainee_status_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
    }
};
