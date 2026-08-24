<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_kit_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_kit_id')->constrained('training_kits')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['training_kit_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_kit_attachments');
    }
};
