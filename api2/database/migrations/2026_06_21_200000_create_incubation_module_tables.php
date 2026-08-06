<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الحاضنات
        Schema::create('incubators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('sector')->nullable(); // tech, industrial, agricultural, services, creative
            $table->string('location')->nullable();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('capacity')->default(20); // max projects
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // برامج الحضانة
        Schema::create('incubation_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incubator_id')->constrained('incubators')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_months')->default(12);
            $table->integer('seats')->default(10);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['open', 'closed', 'completed'])->default('open');
            $table->text('requirements')->nullable();
            $table->timestamps();
        });

        // طلبات الانضمام للحضانة
        Schema::create('incubation_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('incubator_id')->constrained('incubators')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('incubation_programs')->nullOnDelete();
            $table->foreignId('applicant_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('project_name');
            $table->string('project_sector')->nullable();
            $table->enum('business_stage', ['idea', 'pre_seed', 'seed', 'early', 'growth'])->default('idea');
            $table->text('project_description');
            $table->text('problem_statement')->nullable();
            $table->text('target_market')->nullable();
            $table->integer('team_size')->default(1);
            $table->boolean('has_prototype')->default(false);
            $table->boolean('has_revenue')->default(false);
            $table->enum('status', ['pending', 'under_review', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // المشاريع المحتضنة (بعد القبول)
        Schema::create('incubated_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('incubation_applications')->cascadeOnDelete();
            $table->foreignId('incubator_id')->constrained('incubators')->cascadeOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('project_name');
            $table->enum('stage', ['seed', 'early', 'growth', 'exit'])->default('seed');
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['active', 'graduated', 'withdrawn', 'terminated'])->default('active');
            $table->decimal('current_revenue', 15, 2)->nullable();
            $table->integer('current_employees')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // جلسات الإرشاد
        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('incubated_projects')->cascadeOnDelete();
            $table->foreignId('mentor_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->integer('duration_minutes')->default(60);
            $table->string('topic');
            $table->text('notes')->nullable();
            $table->text('action_items')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });

        // تقارير التقدم
        Schema::create('incubation_progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('incubated_projects')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->enum('period_type', ['monthly', 'quarterly'])->default('monthly');
            $table->string('period_label'); // e.g. "Q1-2026", "Jan-2026"
            $table->decimal('revenue', 15, 2)->nullable();
            $table->integer('employees')->nullable();
            $table->integer('customers')->nullable();
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->text('next_steps')->nullable();
            $table->integer('overall_rating')->nullable(); // 1-10
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incubation_progress_reports');
        Schema::dropIfExists('mentoring_sessions');
        Schema::dropIfExists('incubated_projects');
        Schema::dropIfExists('incubation_applications');
        Schema::dropIfExists('incubation_programs');
        Schema::dropIfExists('incubators');
    }
};
