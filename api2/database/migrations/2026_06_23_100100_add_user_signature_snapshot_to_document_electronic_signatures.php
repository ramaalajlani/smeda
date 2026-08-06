<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_electronic_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('user_electronic_signature_id')
                ->nullable()
                ->after('signed_by_user_id');

            $table->foreign('user_electronic_signature_id', 'doc_esign_user_sig_fk')
                ->references('id')
                ->on('user_electronic_signatures')
                ->nullOnDelete();

            $table->string('signature_image_path')->nullable()->after('signer_title');
            $table->string('signature_image_hash', 64)->nullable()->after('signature_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('document_electronic_signatures', function (Blueprint $table) {
            $table->dropForeign('doc_esign_user_sig_fk');
            $table->dropColumn(['user_electronic_signature_id', 'signature_image_path', 'signature_image_hash']);
        });
    }
};
