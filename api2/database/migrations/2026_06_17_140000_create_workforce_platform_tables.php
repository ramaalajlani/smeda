<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organization_name');
            $table->string('title');
            $table->string('city')->nullable();
            $table->foreignId('governorate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employment_type')->default('full_time');
            $table->string('sector')->nullable();
            $table->text('description')->nullable();
            $table->text('skills')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant_name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('specialty')->nullable();
            $table->string('city')->nullable();
            $table->string('experience_years')->nullable();
            $table->text('summary')->nullable();
            $table->string('cv_path')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('staff_training_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organization_name');
            $table->unsignedInteger('employees_count')->default(1);
            $table->string('training_field')->nullable();
            $table->string('city')->nullable();
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'scheduled', 'closed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_training_requests');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
    }
};
