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
        Schema::create('trainer_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trainer_id')
                ->unique()
                ->constrained('trainers')
                ->cascadeOnDelete();

            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedInteger('experience_years')->default(0);

            $table->text('skills')->nullable();
            $table->text('special_interests')->nullable();
            $table->text('linkedin_summary')->nullable();

            $table->string('cv_file')->nullable();
            $table->string('profile_image')->nullable();

            $table->enum('visibility', ['internal', 'public'])->default('internal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_profiles');
    }
};