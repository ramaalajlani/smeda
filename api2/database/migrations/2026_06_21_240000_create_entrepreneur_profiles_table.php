<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrepreneur_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            /* ── أولاً: معلومات رائد الأعمال ── */
            $table->string('full_name');
            $table->string('governorate')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('education_level')->nullable();   // diploma|bachelor|master|phd|other
            $table->string('specialization')->nullable();

            /* ── ثانياً: معلومات المشروع ── */
            $table->string('project_name');
            $table->string('project_field')->nullable();     // ai|mobile_apps|software|games|ecommerce|cybersecurity|fintech|elearning|digital_health|iot|blockchain|other
            $table->string('project_field_other')->nullable();
            $table->year('founding_year')->nullable();

            /* ── ثالثاً: الملخص التنفيذي ── */
            $table->text('executive_summary')->nullable();   // 150-250 كلمة
            $table->text('elevator_pitch')->nullable();       // دقيقة واحدة

            /* ── رابعاً: مستوى الجاهزية ── */
            $table->string('readiness_stage')->nullable();   // idea|mvp|usable|has_users|revenue|scalable
            $table->boolean('has_prototype')->default(false);
            $table->boolean('tested_with_users')->default(false);
            $table->text('testing_results')->nullable();

            /* ── خامساً: المشكلة والحل ── */
            $table->text('problem_description')->nullable();
            $table->text('target_customers')->nullable();
            $table->text('differentiation')->nullable();
            $table->json('competitive_advantages')->nullable(); // tech|price|speed|ux|ai|ease|other

            /* ── سادساً: الفريق ── */
            $table->string('team_size_range')->nullable();   // solo|2-5|6-10|10+
            $table->json('team_roles')->nullable();           // developers|ai|designers|data|marketing|sales|management

            /* ── سابعاً: التقنيات ── */
            $table->json('technologies')->nullable();         // ai|ml|llm|mobile|cloud|cybersecurity|blockchain|data|iot|other

            /* ── ثامناً: السوق والعملاء ── */
            $table->json('market_validation_methods')->nullable(); // interviews|surveys|actual_use|usage_data|market_study|not_yet
            $table->string('target_market')->nullable();      // syria|arab|global
            $table->string('current_users_range')->nullable(); // none|<100|100-1000|1000-10000|>10000
            $table->string('current_customers_range')->nullable(); // none|<10|10-50|50-200|>200

            /* ── تاسعاً: الإيرادات ── */
            $table->boolean('has_revenue')->default(false);
            $table->json('revenue_sources')->nullable();      // subscriptions|services|direct_sales|commissions|ads|other

            /* ── عاشراً: التمويل والاستثمار ── */
            $table->json('funding_sources')->nullable();      // personal|partner|investor|grant|incubator|accelerator|none
            $table->boolean('seeking_investment')->default(false);
            $table->string('investment_needed_range')->nullable(); // <10k|10-50k|50-250k|>250k

            /* ── الحادي عشر: التحديات ── */
            $table->json('challenges')->nullable();           // funding|marketing|customers|tech|team|regulations|expansion|export|other

            /* ── الثاني عشر: الأثر والنمو ── */
            $table->string('jobs_3years_range')->nullable();  // <5|5-20|20-50|>50
            $table->string('scalability_outside_syria')->nullable(); // yes|no|studying

            /* ── الثالث عشر: احتياجات المشروع ── */
            $table->json('support_needed')->nullable();       // funding|incubation|acceleration|investor_network|training|tech_consulting|business_consulting|marketing|partnerships

            /* ── الرابع عشر: معلومات إضافية ── */
            $table->json('previous_participation')->nullable(); // incubation|competitions|exhibitions|funding_programs|accelerators|none
            $table->text('additional_notes')->nullable();

            /* ── إدارة ── */
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneur_profiles');
    }
};
