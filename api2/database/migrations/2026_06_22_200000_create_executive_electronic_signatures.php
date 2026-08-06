<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_signer_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('role_key', 60)->unique()->comment('general_director|deputy_general_director');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signer_name');
            $table->string('signer_title');
            $table->string('signature_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_electronic_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('signable_type');
            $table->unsignedBigInteger('signable_id');
            $table->string('role_key', 60);
            $table->foreignId('signed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('signer_name');
            $table->string('signer_title');
            $table->string('document_hash', 64);
            $table->string('signature_hmac', 64);
            $table->string('verification_code', 32)->unique();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->index(['signable_type', 'signable_id']);
            $table->index(['role_key', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_electronic_signatures');
        Schema::dropIfExists('executive_signer_profiles');
    }
};
