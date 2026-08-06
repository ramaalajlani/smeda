<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 50)->unique(); // PRG-0001
            $table->text('description')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'archived',
            ])->default('active');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'id']);
            $table->index(['is_active', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};