<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_registration_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 100)->unique();

            $table->foreignId('training_center_id')
                ->nullable()
                ->constrained('training_centers')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('national_id', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('specialization', 150)->nullable();
            $table->string('classification_requested', 100)->nullable();

            $table->boolean('has_tot')->default(false);
            $table->string('tot_certificate_number', 100)->nullable();
            $table->string('tot_certificate_source')->nullable();
            $table->date('tot_issue_date')->nullable();
            $table->date('tot_expiry_date')->nullable();

            $table->string('cv_file')->nullable();
            $table->string('certificate_file')->nullable();

            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('review_notes')->nullable();

            $table->foreignId('approved_trainer_id')
                ->nullable()
                ->constrained('trainers')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'id']);
            $table->index(['training_center_id', 'status', 'id']);
            $table->index(['submitted_by_user_id', 'id']);
            $table->index(['approved_trainer_id', 'id']);
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_registration_requests');
    }
};