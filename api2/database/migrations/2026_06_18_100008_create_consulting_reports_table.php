<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('consulting_contracts')->cascadeOnDelete();
            $table->foreignId('request_id')->constrained('consulting_requests')->cascadeOnDelete();

            $table->string('report_pdf_path');
            $table->timestamp('submission_date')->nullable();

            $table->enum('review_status', ['pending', 'approved', 'returned'])->default('pending');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('review_date')->nullable();
            $table->text('reviewer_notes')->nullable();

            $table->enum('recommendation_type', [
                'none',
                'financing',
                'training',
                'incubation',
                'gis',
                'license',
                'activity_change',
            ])->default('none');

            $table->text('recommendation_details')->nullable();
            $table->string('isic4_recommendation', 10)->nullable();

            $table->timestamps();

            $table->index(['contract_id']);
            $table->index(['request_id']);
            $table->index(['review_status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_reports');
    }
};
