<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حالة جلسات المستشار الذكي — تخزين دائم بدل كاش الملفات.
 * يقاوم cache:clear ويعمل عبر أكثر من خادم على نفس قاعدة البيانات.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_user_states', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('current_session_id', 128)->nullable();
            $table->string('department_id', 64)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('ai_chat_owned_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_id', 128);
            $table->timestamp('last_used_at');
            $table->timestamps();

            $table->unique(['user_id', 'session_id']);
            $table->index(['user_id', 'last_used_at']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_owned_sessions');
        Schema::dropIfExists('ai_chat_user_states');
    }
};
