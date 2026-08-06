<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_registration_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 100)->unique();

            $table->foreignId('training_course_id')
                ->constrained('training_courses')
                ->cascadeOnDelete();

            $table->enum('registration_mode', [
                'self',
                'guardian_with_dependents',
                'group_batch',
            ])->default('self');

            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('submitted_by_type', 50)->nullable(); // trainee / guardian / guest / center_user

            $table->string('applicant_name');
            $table->string('applicant_phone', 30)->nullable();
            $table->string('applicant_email')->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_national_id', 100)->nullable();

            $table->text('notes')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'guardian_confirmed',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->timestamp('guardian_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['training_course_id', 'status', 'id']);
            $table->index(['registration_mode', 'status', 'id']);
            $table->index(['submitted_by_user_id', 'id']);
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_registration_requests');
    }
};