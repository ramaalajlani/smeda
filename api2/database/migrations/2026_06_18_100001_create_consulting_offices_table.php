<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulting_offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();

            $table->string('name');
            $table->string('code', 30)->unique()->nullable();
            $table->string('license_number', 100)->nullable();
            $table->date('license_date')->nullable();
            $table->date('license_expiry')->nullable();

            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->enum('status', ['pending', 'active', 'suspended', 'revoked'])->default('pending');
            $table->date('accreditation_date')->nullable();

            $table->decimal('overall_rating', 3, 2)->default(0);
            $table->unsignedInteger('total_requests_completed')->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(0);     // percentage 0-100
            $table->decimal('report_accept_rate', 5, 2)->default(0);

            $table->text('bio')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['governorate_id', 'status', 'id']);
            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulting_offices');
    }
};
