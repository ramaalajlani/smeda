<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_office_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('consulting_offices')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();

            $table->string('violation_type', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->text('action_taken')->nullable();

            $table->timestamps();
            $table->index(['office_id', 'status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_office_violations');
    }
};
