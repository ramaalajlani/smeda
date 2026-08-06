<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('body');

            // الروابط الاختيارية بوحدات المنصة
            $table->foreignId('incubated_project_id')->nullable()->constrained('incubated_projects')->nullOnDelete();
            $table->foreignId('incubator_id')->nullable()->constrained('incubators')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // بيانات صاحب القصة
            $table->string('hero_name');                                // اسم صاحب النجاح
            $table->string('hero_title')->nullable();                   // المسمى الوظيفي / الدور
            $table->string('hero_photo_url')->nullable();
            $table->string('project_name')->nullable();
            $table->string('sector')->nullable();                       // tech|industrial|agricultural|services|creative

            // أرقام النجاح (اختيارية)
            $table->decimal('revenue_achieved', 15, 2)->nullable();
            $table->integer('jobs_created')->nullable();
            $table->integer('years_in_incubator')->nullable();
            $table->string('current_stage')->nullable();                // ما وصل إليه المشروع الآن

            // الاقتباس البارز
            $table->text('featured_quote')->nullable();

            // محتوى وسائط
            $table->string('cover_image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->json('gallery')->nullable();                        // مصفوفة روابط صور

            // تحكم النشر
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
