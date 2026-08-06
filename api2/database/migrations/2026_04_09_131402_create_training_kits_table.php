<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_kits', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 50)->unique(); // KIT-0001

            $table->string('sector', 150)->nullable();
            $table->string('category', 150)->nullable();
            $table->string('type', 150)->nullable();
            $table->string('material_code', 100)->nullable();
            $table->string('level', 100)->nullable();

            $table->unsignedInteger('hours')->default(0);

            $table->text('objective')->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'archived',
            ])->default('active');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['sector', 'id']);
            $table->index(['category', 'id']);
            $table->index(['type', 'id']);
            $table->index(['level', 'id']);
            $table->index(['status', 'id']);
            $table->index(['is_active', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_kits');
    }
};