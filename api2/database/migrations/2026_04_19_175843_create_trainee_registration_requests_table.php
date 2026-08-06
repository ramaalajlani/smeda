<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_registration_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 100)->unique();

            $table->string('full_name');
            $table->string('national_id', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('city', 100)->nullable();
            $table->string('address')->nullable();

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('education_level', 100)->nullable();

            $table->enum('registration_mode', [
                'self',
                'guardian',
                'group',
            ])->default('self');

            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_national_id', 100)->nullable();
            $table->string('group_name')->nullable();

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

            $table->foreignId('approved_trainee_id')
                ->nullable()
                ->constrained('trainees')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'id']);
            $table->index(['registration_mode', 'status', 'id']);
            $table->index(['submitted_by_user_id', 'id']);
            $table->index(['approved_trainee_id', 'id']);
            $table->index(['city', 'id']);
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_registration_requests');
    }
};