<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_registration_request_members', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('course_registration_request_id');
            $table->unsignedBigInteger('trainee_id')->nullable();

            $table->string('full_name');
            $table->string('national_id', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('education_level', 100)->nullable();

            $table->enum('relation_type', [
                'self',
                'son',
                'daughter',
                'dependent',
                'member',
            ])->default('member');

            $table->enum('status', [
                'pending',
                'registered',
                'rejected',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();

            // indexes قصيرة
            $table->index(
                ['course_registration_request_id', 'status', 'id'],
                'crrm_req_stat_id_idx'
            );

            $table->index(['trainee_id', 'id'], 'crrm_trainee_id_idx');
            $table->index(['relation_type', 'id'], 'crrm_relation_id_idx');

            // foreign keys قصيرة
            $table->foreign('course_registration_request_id', 'crrm_req_fk')
                ->references('id')
                ->on('course_registration_requests')
                ->cascadeOnDelete();

            $table->foreign('trainee_id', 'crrm_trainee_fk')
                ->references('id')
                ->on('trainees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_registration_request_members');
    }
};