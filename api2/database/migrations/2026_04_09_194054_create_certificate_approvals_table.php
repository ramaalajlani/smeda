<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('certificate_id')
                ->constrained('certificates')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('approval_step', [
                'center_approval',
                'training_manager_approval',
                'deputy_director_approval',
                'general_director_approval',
            ]);

            $table->enum('decision', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->timestamp('decision_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['certificate_id', 'approval_step'],
                'ca_cert_step_uniq'
            );

            $table->index(
                ['certificate_id', 'decision', 'id'],
                'ca_cert_dec_id_idx'
            );

            $table->index(
                ['approval_step', 'decision', 'id'],
                'ca_step_dec_id_idx'
            );

            $table->index(
                ['approved_by', 'id'],
                'ca_approved_by_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_approvals');
    }
};