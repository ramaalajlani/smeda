<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('partner_name');
            $table->string('agreement_type')->default('general');
            $table->string('status')->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_type');
            $table->string('title');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 8)->default('SYP');
            $table->string('status')->default('draft');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_records');
        Schema::dropIfExists('agreements');
    }
};
