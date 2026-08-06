<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incubation_applications', function (Blueprint $table) {
            // حقول تقنية مخصصة
            $table->tinyInteger('tech_readiness_level')->nullable()->comment('TRL 1-9');
            $table->string('revenue_model')->nullable()->comment('saas|b2b|b2c|marketplace|hardware|other');
            $table->string('demo_url')->nullable();
            $table->string('github_url')->nullable();
            $table->boolean('has_ip')->default(false)->comment('Intellectual Property / براءة اختراع');
            $table->string('ip_description')->nullable();
            $table->json('tech_stack')->nullable()->comment('مصفوفة التقنيات المستخدمة');
            $table->string('target_platform')->nullable()->comment('web|mobile|desktop|embedded|saas|api|other');

            // حقول مشتركة كانت ناقصة
            $table->unsignedInteger('expected_jobs')->nullable()->comment('عدد الوظائف المتوقعة');
            $table->decimal('funding_needed', 15, 2)->nullable()->comment('التمويل المطلوب بالليرة');
            $table->string('funding_stage')->nullable()->comment('bootstrapped|seeking|funded');
            $table->text('competitive_advantage')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incubation_applications', function (Blueprint $table) {
            $table->dropColumn([
                'tech_readiness_level','revenue_model','demo_url','github_url',
                'has_ip','ip_description','tech_stack','target_platform',
                'expected_jobs','funding_needed','funding_stage','competitive_advantage',
            ]);
        });
    }
};
