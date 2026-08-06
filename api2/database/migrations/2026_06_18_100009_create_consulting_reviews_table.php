<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('consulting_contracts')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained('consulting_offices')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('overall_rating');      // 1-5
            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->unsignedTinyInteger('time_rating')->nullable();
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->text('comment')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['contract_id', 'reviewer_id'], 'review_contract_reviewer_unique');
            $table->index(['office_id', 'is_published', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_reviews');
    }
};
