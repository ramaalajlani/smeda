<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_kit_nominations', function (Blueprint $table) {
            $table->id();

            // المدرب
            $table->foreignId('trainer_id')
                ->constrained()
                ->cascadeOnDelete();

            // الحقيبة (اختياري إذا موجودة)
            $table->foreignId('training_kit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // اسم الحقيبة (في حال جديدة)
            $table->string('proposed_name')->nullable();

            // تفاصيل
            $table->text('description')->nullable();
            $table->string('sector')->nullable();
            $table->string('category')->nullable();
            $table->integer('hours')->nullable();

            // الحالة
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
            ])->default('pending');

            // قرار
            $table->text('decision_notes')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_kit_nominations');
    }
};