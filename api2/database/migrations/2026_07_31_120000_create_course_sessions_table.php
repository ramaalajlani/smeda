<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_course_id')
                ->constrained('training_courses')
                ->cascadeOnDelete();

            // المحور المرتبط بالجلسة (اختياري)
            $table->foreignId('program_module_id')
                ->nullable()
                ->constrained('training_program_modules')
                ->nullOnDelete();

            $table->unsignedInteger('session_no')->default(1);
            $table->date('session_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('title')->nullable();

            $table->enum('status', ['scheduled', 'held', 'cancelled'])
                ->default('scheduled');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_course_id', 'session_date', 'id'], 'cs_course_date_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
};
