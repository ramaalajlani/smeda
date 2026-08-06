<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_center_registration_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 100)->unique();

            $table->string('center_name');
            $table->string('city', 100);
            $table->string('address')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('classification_requested', 100)->nullable();

            $table->boolean('supports_offline_training')->default(true);
            $table->boolean('supports_online_training')->default(false);

            // الموقع الجغرافي
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // بيانات الترخيص
            $table->string('license_number')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->string('license_issued_by')->nullable();
            $table->string('license_image_path')->nullable();

            // حقول قديمة/إضافية إن لزم استخدامها لاحقاً
            $table->string('license_file')->nullable();
            $table->string('accreditation_file')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();

            $table->text('review_notes')->nullable();
            $table->text('decision_notes')->nullable();

            $table->unsignedBigInteger('approved_training_center_id')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'id'], 'tcrr_status_id_idx');
            $table->index(['submitted_by_user_id', 'id'], 'tcrr_sub_by_id_idx');
            $table->index(['approved_training_center_id', 'id'], 'tcrr_appr_center_id_idx');
            $table->index(['city', 'id'], 'tcrr_city_id_idx');
            $table->index(['created_at', 'id'], 'tcrr_created_id_idx');

            // Foreign keys
            $table->foreign('submitted_by_user_id', 'tcrr_sub_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('reviewed_by_user_id', 'tcrr_rev_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_training_center_id', 'tcrr_appr_center_fk')
                ->references('id')
                ->on('training_centers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_center_registration_requests');
    }
};