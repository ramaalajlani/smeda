<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── محاور البرنامج ──
        Schema::create('training_program_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('hours')->default(0);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->text('required_tools')->nullable();
            $table->string('evaluation_method')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'sort_order']);
        });

        // ── مخرجات البرنامج ──
        Schema::create('training_program_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['program_id', 'sort_order']);
        });

        // ── الربط مع خدمات الهيئة ──
        Schema::create('training_program_service_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->enum('service_type', [
                'needs_map', 'entrepreneurs', 'finance',
                'consulting', 'incubators', 'accelerators',
                'certificates', 'risk_assessment',
            ]);
            $table->unsignedBigInteger('service_reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'service_type']);
        });

        // ── سجل الاعتماد ──
        Schema::create('training_program_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('action');  // submitted / approved / rejected / suspended / archived
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_id', 'created_at']);
        });

        // ── الدورات المنشأة من البرنامج ──
        Schema::create('training_program_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['program_id', 'course_id']);
            $table->index('program_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_program_executions');
        Schema::dropIfExists('training_program_approval_logs');
        Schema::dropIfExists('training_program_service_links');
        Schema::dropIfExists('training_program_outcomes');
        Schema::dropIfExists('training_program_modules');
    }
};
