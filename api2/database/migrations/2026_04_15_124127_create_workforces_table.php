<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workforces', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trainee_id')
                ->unique()
                ->constrained('trainees')
                ->cascadeOnDelete();

            $table->string('workforce_code', 50)->unique();

            $table->enum('status', [
                'active',
                'inactive',
                'suspended',
            ])->default('active');

            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workforces');
    }
};