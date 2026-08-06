<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 30)->unique()->nullable(); // CON-2026-00001

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('branch_manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('category_code', 10);
            $table->enum('request_type', ['new_project', 'existing', 'financing', 'classification'])->default('existing');

            $table->string('title');
            $table->text('description');
            $table->string('project_name')->nullable();
            $table->string('economic_activity')->nullable();
            $table->string('isic4_code', 10)->nullable();

            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->unsignedSmallInteger('expected_duration_days')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'needs_info',
                'sorting',
                'awaiting_offers',
                'offer_submitted',
                'awaiting_client_approval',
                'in_progress',
                'report_submitted',
                'awaiting_authority_approval',
                'completed',
                'transferred_financing',
                'transferred_training',
                'transferred_incubation',
                'transferred_gis',
                'rejected',
                'cancelled',
            ])->default('draft');

            $table->text('branch_notes')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('offers_deadline')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'id']);
            $table->index(['governorate_id', 'status', 'id']);
            $table->index(['branch_id', 'status', 'id']);
            $table->index(['status', 'id']);
            $table->index(['category_code', 'status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_requests');
    }
};
