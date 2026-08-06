<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('needs', function (Blueprint $table) {
            $table->id();
            $table->string('need_code')->unique();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('summary')->nullable();

            $table->enum('need_owner_type', ['citizen', 'state'])->default('citizen');
            $table->enum('need_scope', ['individual', 'project', 'local', 'governorate', 'national', 'sectoral'])->default('individual');
            $table->string('need_type')->nullable();
            $table->string('need_category')->nullable();
            $table->enum('need_complexity', ['general', 'specific'])->default('specific');

            $table->enum('source_platform', ['gis', 'legacy_gis', 'training', 'finance', 'incubator', 'workforce', 'manual', 'other'])->default('gis');
            $table->string('source_module')->nullable();
            $table->unsignedBigInteger('source_record_id')->nullable();

            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('district_name')->nullable();
            $table->string('administrative_unit_name')->nullable();
            $table->string('countryside_name')->nullable();
            $table->string('locality_name')->nullable();
            $table->string('village_or_neighborhood')->nullable();
            $table->text('address_details')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_source')->nullable();

            $table->string('sector')->nullable();
            $table->string('economic_sector')->nullable();
            $table->string('syrsic_section')->nullable();
            $table->string('syrsic_division')->nullable();
            $table->string('syrsic_group')->nullable();
            $table->string('syrsic_class')->nullable();
            $table->string('syrsic_activity')->nullable();

            $table->string('priority')->default('medium');
            $table->string('status')->default('pending_governorate_review');
            $table->string('approval_status')->nullable();

            $table->string('state_need_level')->nullable();
            $table->string('citizen_need_profile')->nullable();
            $table->string('responsible_entity')->nullable();
            $table->string('proposed_intervention')->nullable();
            $table->unsignedTinyInteger('public_benefit_score')->nullable();

            $table->string('applicant_name')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_type')->nullable();
            $table->string('organization_name')->nullable();

            $table->unsignedInteger('beneficiaries_count')->nullable();
            $table->unsignedInteger('expected_jobs_count')->nullable();
            $table->unsignedInteger('expected_projects_count')->nullable();
            $table->string('impact_level')->nullable();
            $table->string('urgency_level')->nullable();
            $table->string('expected_duration')->nullable();

            $table->text('available_partners')->nullable();
            $table->text('obstacles')->nullable();
            $table->text('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('funding_application_id')->nullable()->constrained('funding_applications')->nullOnDelete();
            $table->foreignId('training_course_id')->nullable()->constrained('training_courses')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('classified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('reviewer_note')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('return_reason')->nullable();
            $table->text('classification_note')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->boolean('is_public')->default(false);
            $table->boolean('is_mapped')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['governorate_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['source_platform', 'source_record_id']);
            $table->index(['need_owner_type', 'status']);
        });

        Schema::create('need_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('need_id')->constrained('needs')->cascadeOnDelete();
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('administrative_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('district_name')->nullable();
            $table->string('unit_name');
            $table->string('countryside_name')->nullable();
            $table->string('locality_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('need_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('lookup_type');
            $table->string('value');
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['lookup_type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('need_lookups');
        Schema::dropIfExists('administrative_units');
        Schema::dropIfExists('need_action_logs');
        Schema::dropIfExists('needs');
    }
};
