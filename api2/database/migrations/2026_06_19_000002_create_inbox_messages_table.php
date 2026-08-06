<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();

            // المرسل
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_role', 40)->default('admin'); // admin|branch_manager|system

            // المستقبل (فرد أو بث)
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_broadcast')->default(false);   // إرسال لجميع المستخدمين
            $table->string('broadcast_role')->nullable();       // إرسال لدور معين فقط

            // المحتوى
            $table->string('subject');
            $table->text('body');
            $table->boolean('requires_reply')->default(false);  // هل يستلزم رداً؟
            $table->enum('priority', ['normal','high','urgent'])->default('normal');

            // الملفات المرفقة (JSON)
            $table->json('attachments')->nullable();

            // الرد (thread بسيط)
            $table->foreignId('parent_id')->nullable()->constrained('inbox_messages')->nullOnDelete();

            // حالة القراءة تُتابع في جدول منفصل للبث
            $table->boolean('is_read')->default(false);         // للرسائل الفردية فقط
            $table->timestamp('read_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['recipient_id', 'is_read', 'id']);
            $table->index(['sender_id', 'id']);
            $table->index(['is_broadcast', 'broadcast_role', 'id']);
        });

        // جدول قراءة رسائل البث
        Schema::create('inbox_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('inbox_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_message_reads');
        Schema::dropIfExists('inbox_messages');
    }
};
