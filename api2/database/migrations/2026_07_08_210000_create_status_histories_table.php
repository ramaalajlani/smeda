<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->string('model_type', 191);
            $table->unsignedBigInteger('model_id');
            $table->string('from_status', 100)->nullable();
            $table->string('to_status', 100);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id', 'created_at'], 'status_histories_model_idx');
            $table->index(['changed_by', 'created_at'], 'status_histories_changed_by_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
