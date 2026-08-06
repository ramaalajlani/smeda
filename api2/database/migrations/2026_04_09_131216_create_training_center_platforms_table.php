<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_center_platforms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_center_id')
                ->constrained('training_centers')
                ->cascadeOnDelete();

            $table->string('platform_name', 100); // Zoom / Meet / Teams / LMS
            $table->string('platform_url')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending');

            $table->date('approved_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_center_id', 'status', 'id']);
            $table->index(['platform_name', 'id']);
            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_center_platforms');
    }
};