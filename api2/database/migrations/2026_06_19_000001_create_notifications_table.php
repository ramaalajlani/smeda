<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // نوع الإشعار
            $table->string('type', 60); // consulting_new_offer, report_approved, message_received, ...
            $table->string('title');
            $table->text('body')->nullable();

            // رابط اختياري للتنقل
            $table->string('action_url')->nullable();
            $table->string('action_label', 60)->nullable();

            // الأيقونة والتصنيف
            $table->string('icon', 60)->default('bi-bell-fill');
            $table->string('color', 20)->default('primary'); // primary|success|warning|danger|info

            // مرجع اختياري (طلب استشارة، عقد، إلخ)
            $table->string('reference_type', 60)->nullable(); // consulting_request, consulting_contract, inbox_message
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_read', 'id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
