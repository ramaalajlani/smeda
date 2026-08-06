<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('consulting_requests')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained('consulting_offices')->cascadeOnDelete();

            $table->text('methodology_text');
            $table->unsignedSmallInteger('proposed_duration_days');
            $table->decimal('price', 12, 2);
            $table->string('sample_attachments')->nullable();

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('seen_by_client_at')->nullable();

            $table->timestamps();

            $table->unique(['request_id', 'office_id'], 'offer_request_office_unique');
            $table->index(['request_id', 'status', 'id']);
            $table->index(['office_id', 'status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_offers');
    }
};
