<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('consulting_contracts')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_role', 30)->nullable(); // client / office / branch_manager / admin

            $table->text('message_text');
            $table->string('attachment_path')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'id']);
            $table->index(['sender_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_messages');
    }
};
