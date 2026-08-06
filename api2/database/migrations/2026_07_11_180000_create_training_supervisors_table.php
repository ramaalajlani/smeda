<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 80)->unique();
            $table->enum('type', ['ministry', 'directorate', 'internal_entity'])->default('directorate');
            $table->foreignId('parent_id')->nullable()->constrained('training_supervisors')->nullOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['branch_id', 'is_active']);
            $table->index(['governorate_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_supervisors');
    }
};
