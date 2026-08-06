<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('applicant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('applicant_name');
            $table->string('national_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('governorate_id')->constrained('governorates');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('project_name');
            $table->string('project_type')->nullable();
            $table->string('project_sector')->nullable();
            $table->enum('project_size', ['micro', 'small', 'medium'])->default('small');
            $table->enum('business_stage', ['idea', 'startup', 'existing', 'expansion'])->default('startup');
            $table->decimal('requested_amount', 15, 2);
            $table->string('currency', 8)->default('SYP');
            $table->enum('financing_type', ['capital', 'working_capital', 'mixed'])->default('capital');
            $table->unsignedSmallInteger('repayment_period_months')->nullable();
            $table->text('purpose')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'branch_review', 'needs_completion', 'consultant_review',
                'consultant_priced', 'funder_review', 'approved', 'rejected', 'funded', 'defaulted', 'closed',
            ])->default('draft');
            $table->string('current_stage')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'status']);
            $table->index(['applicant_user_id', 'status']);
        });

        Schema::create('funding_application_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications')->cascadeOnDelete();
            $table->text('owner_experience')->nullable();
            $table->unsignedInteger('employees_count')->nullable();
            $table->decimal('monthly_revenue', 15, 2)->nullable();
            $table->decimal('monthly_expenses', 15, 2)->nullable();
            $table->decimal('existing_debts', 15, 2)->nullable();
            $table->text('assets_description')->nullable();
            $table->text('market_description')->nullable();
            $table->text('challenges')->nullable();
            $table->text('requested_support')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('funding_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('consultant_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license_number')->nullable();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('specialization')->nullable();
            $table->json('sectors')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('consultant_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications')->cascadeOnDelete();
            $table->foreignId('consultant_office_id')->constrained('consultant_offices');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at');
            $table->enum('status', ['assigned', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->decimal('price_offer_amount', 15, 2)->nullable();
            $table->string('price_offer_currency', 8)->nullable();
            $table->enum('price_offer_status', ['pending', 'submitted', 'approved', 'rejected'])->nullable();
            $table->text('consultant_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('consultant_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications')->cascadeOnDelete();
            $table->foreignId('consultant_office_id')->constrained('consultant_offices');
            $table->foreignId('consultant_user_id')->constrained('users');
            $table->unsignedTinyInteger('feasibility_score')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();
            $table->decimal('recommended_amount', 15, 2)->nullable();
            $table->enum('recommendation', ['approve', 'reject', 'needs_adjustment'])->nullable();
            $table->text('report_summary')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('funding_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('partner_type', ['bank', 'fund', 'guarantee_company', 'donor', 'other'])->default('bank');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('funding_partner_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications')->cascadeOnDelete();
            $table->foreignId('funding_partner_id')->constrained('funding_partners');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at');
            $table->enum('status', ['sent', 'under_review', 'approved', 'rejected', 'funded'])->default('sent');
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->string('approved_currency', 8)->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamps();
        });

        Schema::create('funded_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_application_id')->constrained('funding_applications');
            $table->foreignId('funding_partner_id')->nullable()->constrained('funding_partners')->nullOnDelete();
            $table->string('loan_number')->unique();
            $table->decimal('approved_amount', 15, 2);
            $table->string('currency', 8)->default('SYP');
            $table->enum('interest_type', ['interest', 'free', 'profit_margin'])->default('interest');
            $table->decimal('interest_rate', 8, 4)->nullable();
            $table->decimal('profit_margin', 8, 4)->nullable();
            $table->unsignedSmallInteger('installment_count')->default(1);
            $table->decimal('installment_amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paid', 'defaulted', 'restructured', 'closed'])->default('active');
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funded_loan_id')->constrained('funded_loans')->cascadeOnDelete();
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'late', 'partial', 'defaulted'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('consultant_office_id')->nullable()->after('branch_id')->constrained('consultant_offices')->nullOnDelete();
            $table->foreignId('funding_partner_id')->nullable()->after('consultant_office_id')->constrained('funding_partners')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funding_partner_id');
            $table->dropConstrainedForeignId('consultant_office_id');
        });

        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('funded_loans');
        Schema::dropIfExists('funding_partner_assignments');
        Schema::dropIfExists('funding_partners');
        Schema::dropIfExists('consultant_reports');
        Schema::dropIfExists('consultant_assignments');
        Schema::dropIfExists('consultant_offices');
        Schema::dropIfExists('funding_documents');
        Schema::dropIfExists('funding_application_details');
        Schema::dropIfExists('funding_applications');
    }
};
