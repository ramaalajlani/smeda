<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_centers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 50)->unique(); // TC-0001

            $table->string('city', 100);
            $table->string('address')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('classification', 100)->nullable(); // درجة أولى / ثانية ...
            $table->enum('accreditation_status', [
                'pending',
                'approved',
                'renewal',
                'rejected',
                'suspended',
            ])->default('pending');

            $table->boolean('supports_offline_training')->default(true);
            $table->boolean('supports_online_training')->default(false);

            $table->date('accreditation_start_date')->nullable();
            $table->date('accreditation_end_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'id']);
            $table->index(['accreditation_status', 'id']);
            $table->index(['is_active', 'id']);
            $table->index(['supports_online_training', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_centers');
    }
};