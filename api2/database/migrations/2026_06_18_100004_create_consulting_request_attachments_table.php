<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('consulting_requests')->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('upload_stage', ['request', 'execution', 'report'])->default('request');

            $table->timestamps();
            $table->index(['request_id', 'upload_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_request_attachments');
    }
};
