<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('trainee_code', 50)->unique(); // TRA-0001
            $table->string('national_id', 100)->nullable()->unique();

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('city', 100)->nullable();
            $table->string('address')->nullable();

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            $table->string('education_level', 100)->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'blocked',
            ])->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'id']);
            $table->index(['city', 'id']);
            $table->index(['created_at', 'id']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};