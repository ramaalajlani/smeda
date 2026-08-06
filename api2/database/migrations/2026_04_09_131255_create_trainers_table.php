<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_center_id')
                ->nullable()
                ->constrained('training_centers')
                ->nullOnDelete();

            $table->string('name');
            $table->string('trainer_code', 50)->unique(); // TRN-0001

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('specialization', 150)->nullable();
            $table->string('classification', 100)->nullable();

            $table->boolean('has_tot')->default(false);
            $table->string('tot_certificate_number', 100)->nullable();
            $table->string('tot_certificate_source')->nullable();
            $table->date('tot_issue_date')->nullable();
            $table->date('tot_expiry_date')->nullable();

            $table->boolean('can_train')->default(false);
            $table->boolean('can_evaluate')->default(false);

            $table->enum('status', [
                'active',
                'under_review',
                'suspended',
                'expired',
            ])->default('under_review');

            $table->date('accreditation_start_date')->nullable();
            $table->date('accreditation_end_date')->nullable();

            $table->text('bio')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['training_center_id', 'status', 'id']);
            $table->index(['status', 'id']);
            $table->index(['has_tot', 'id']);
            $table->index(['can_train', 'id']);
            $table->index(['specialization', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};