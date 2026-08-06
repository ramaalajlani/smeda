<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('training_center_id')
                ->nullable()
                ->after('entity_type')
                ->constrained('training_centers')
                ->nullOnDelete();

            $table->foreignId('trainer_id')
                ->nullable()
                ->after('training_center_id')
                ->constrained('trainers')
                ->nullOnDelete();

            $table->foreignId('trainee_id')
                ->nullable()
                ->after('trainer_id')
                ->constrained('trainees')
                ->nullOnDelete();

            $table->index(['training_center_id']);
            $table->index(['trainer_id']);
            $table->index(['trainee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trainee_id');
            $table->dropConstrainedForeignId('trainer_id');
            $table->dropConstrainedForeignId('training_center_id');
        });
    }
};