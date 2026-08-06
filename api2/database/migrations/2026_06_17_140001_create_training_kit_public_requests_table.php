<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_kit_public_requests', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('proposed_name');
            $table->string('city')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'closed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_kit_public_requests');
    }
};
