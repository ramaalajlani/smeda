<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trainee_id')
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->foreignId('training_center_id')
                ->nullable()
                ->constrained('training_centers')
                ->nullOnDelete();

            $table->foreignId('trainer_id')
                ->nullable()
                ->constrained('trainers')
                ->nullOnDelete();

            $table->foreignId('training_kit_id')
                ->nullable()
                ->constrained('training_kits')
                ->nullOnDelete();

            $table->foreignId('training_program_id')
                ->nullable()
                ->constrained('training_programs')
                ->nullOnDelete();

            $table->foreignId('training_course_id')
                ->nullable()
                ->constrained('training_courses')
                ->nullOnDelete();

            $table->string('certificate_number', 100)->unique();
            $table->string('reference_number', 100)->nullable()->unique();
            $table->string('verification_code', 100)->nullable()->unique();

            $table->enum('certificate_type', [
                'attendance',
                'pass',
            ]);

            $table->enum('result', [
                'pending',
                'passed',
                'failed',
                'review',
            ])->default('pending');

            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('hours_awarded')->default(0);

            $table->enum('status', [
                'draft',
                'pending_center_approval',
                'pending_training_approval',
                'pending_deputy_approval',
                'pending_general_director_approval',
                'approved',
                'rejected',
                'cancelled',
            ])->default('draft');

            $table->date('issue_date')->nullable();
            $table->date('certificate_date')->nullable();

            $table->string('qr_code_path')->nullable();
            $table->string('certificate_file_path')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['trainee_id', 'status', 'id'], 'cert_trainee_stat_id_idx');
            $table->index(['training_center_id', 'status', 'id'], 'cert_center_stat_id_idx');
            $table->index(['trainer_id', 'status', 'id'], 'cert_trainer_stat_id_idx');
            $table->index(['training_kit_id', 'status', 'id'], 'cert_kit_stat_id_idx');
            $table->index(['training_course_id', 'status', 'id'], 'cert_course_stat_id_idx');
            $table->index(['certificate_type', 'status', 'id'], 'cert_type_stat_id_idx');
            $table->index(['status', 'id'], 'cert_status_id_idx');
            $table->index(['issue_date', 'id'], 'cert_issue_id_idx');
            $table->index(['is_verified', 'id'], 'cert_verified_id_idx');
            $table->index(['created_at', 'id'], 'cert_created_id_idx');
            $table->index(['deleted_at'], 'cert_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};