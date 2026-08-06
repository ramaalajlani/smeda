<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_code', 30)->unique()->nullable();

            $table->foreignId('request_id')->constrained('consulting_requests')->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('consulting_offers')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained('consulting_offices')->cascadeOnDelete();
            $table->foreignId('client_user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('signed_by_client_at')->nullable();
            $table->timestamp('signed_by_office_at')->nullable();

            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->decimal('total_value', 12, 2);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('contract_pdf_path')->nullable();

            $table->timestamps();

            $table->index(['request_id']);
            $table->index(['office_id', 'id']);
            $table->index(['client_user_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_contracts');
    }
};
