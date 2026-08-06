<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_center_id')
                ->constrained('training_centers')
                ->cascadeOnDelete();

            $table->foreignId('trainer_id')
                ->constrained('trainers')
                ->cascadeOnDelete();

            $table->foreignId('training_kit_id')
                ->constrained('training_kits')
                ->cascadeOnDelete();

            $table->foreignId('training_program_id')
                ->nullable()
                ->constrained('training_programs')
                ->nullOnDelete();

            $table->string('course_code', 50)->unique(); // CRS-0001
            $table->string('title');

            $table->enum('delivery_mode', ['online', 'offline'])->default('offline');
            $table->string('approved_platform', 150)->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('planned_hours')->default(0);
            $table->unsignedInteger('actual_hours')->default(0);
            $table->unsignedInteger('capacity')->default(0);

            $table->enum('status', [
                'draft',
                'scheduled',
                'ongoing',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['training_center_id', 'status', 'id']);
            $table->index(['trainer_id', 'status', 'id']);
            $table->index(['training_kit_id', 'status', 'id']);
            $table->index(['training_program_id', 'status', 'id']);
            $table->index(['delivery_mode', 'status', 'id']);
            $table->index(['start_date', 'id']);
            $table->index(['end_date', 'id']);
            $table->index(['status', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_courses');
    }
};