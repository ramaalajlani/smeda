<?php

namespace Database\Seeders;

use App\Models\EntrepreneurProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntrepreneurDemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'project.owner@system.com')->first();
        $general = User::query()->where('email', 'general@system.com')->first();

        if (!$owner) {
            return;
        }

        $profiles = [
            [
                'user_id' => $owner->id,
                'full_name' => 'أحمد الخطيب',
                'governorate' => 'دمشق',
                'phone' => '0932111222',
                'email' => 'ahmed.khattib@demo.local',
                'age' => 29,
                'education_level' => 'bachelor',
                'specialization' => 'هندسة حاسوب',
                'project_name' => 'SyriaPay',
                'project_field' => 'fintech',
                'founding_year' => 2024,
                'executive_summary' => 'SyriaPay منصة دفع رقمي تهدف إلى تمكين التجارة الإلكترونية في سوريا عبر حلول دفع آمنة وسهلة للمتاجر الصغيرة والمتوسطة. نحل مشكلة غياب البنية التحتية للدفع الإلكتروني المحلي ونوفر واجهة موحدة للتجار والمستهلكين مع تكامل بنكي تدريجي. الفئة المستهدفة هي أصحاب المتاجر الإلكترونية والمشاريع الناشئة. المشروع في مرحلة MVP مع 120 تاجر تجريبي.',
                'elevator_pitch' => 'نحن نبني Stripe السوري — منصة دفع محلية تمكّن أي متجر إلكتروني من قبول المدفوعات خلال دقائق.',
                'readiness_stage' => 'has_users',
                'has_prototype' => true,
                'tested_with_users' => true,
                'problem_description' => 'صعوبة قبول المدفوعات الإلكترونية للمتاجر الصغيرة في سوريا.',
                'target_customers' => 'المتاجر الإلكترونية والمشاريع الرقمية الناشئة.',
                'differentiation' => 'تكامل محلي مع البنوك وواجهة عربية مبسطة.',
                'competitive_advantages' => ['tech', 'ux', 'ease'],
                'team_size_range' => '2-5',
                'team_roles' => ['developers', 'designers', 'management'],
                'technologies' => ['mobile', 'cloud', 'data'],
                'market_validation_methods' => ['interviews', 'actual_use'],
                'target_market' => 'syria',
                'current_users_range' => '100-1000',
                'current_customers_range' => '10-50',
                'has_revenue' => false,
                'funding_sources' => ['personal', 'incubator'],
                'seeking_investment' => true,
                'investment_needed_range' => '50-250k',
                'challenges' => ['funding', 'regulations'],
                'jobs_3years_range' => '5-20',
                'scalability_outside_syria' => 'studying',
                'support_needed' => ['funding', 'incubation', 'investor_network'],
                'previous_participation' => ['competitions'],
                'status' => 'approved',
                'reviewed_by' => $general?->id,
                'reviewed_at' => now()->subDays(10),
            ],
            [
                'user_id' => $owner->id,
                'full_name' => 'ليلى المنصور',
                'governorate' => 'حلب',
                'phone' => '0945333444',
                'email' => 'layla.mansour@demo.local',
                'age' => 26,
                'education_level' => 'master',
                'specialization' => 'علم بيانات',
                'project_name' => 'AgriSense AI',
                'project_field' => 'ai',
                'founding_year' => 2025,
                'executive_summary' => 'AgriSense AI منصة تحليل زراعي تعتمد على صور الأقمار الصناعية والذكاء الاصطناعي لتقديم توصيات ري ومحاصيل للمزارعين السوريين. نحل مشكلة نقص البيانات الزراعية الدقيقة ونقدم تقارير أسبوعية عبر تطبيق جوال. المشروع في مرحلة MVP مع 3 تجارب ميدانية في ريف حلب.',
                'elevator_pitch' => 'نُحوّل بيانات الأقمار الصناعية إلى قرارات زراعية يومية للمزارع السوري.',
                'readiness_stage' => 'mvp',
                'has_prototype' => true,
                'tested_with_users' => true,
                'problem_description' => 'المزارعون يفتقرون لبيانات دقيقة عن حالة المحصول والري.',
                'target_customers' => 'المزارعون والتعاونيات الزراعية.',
                'differentiation' => 'نموذج AI مخصص للمناخ السوري.',
                'competitive_advantages' => ['ai', 'tech'],
                'team_size_range' => '2-5',
                'team_roles' => ['developers', 'ai', 'data'],
                'technologies' => ['ai', 'ml', 'mobile'],
                'market_validation_methods' => ['surveys', 'actual_use'],
                'target_market' => 'syria',
                'current_users_range' => '<100',
                'current_customers_range' => '<10',
                'has_revenue' => false,
                'funding_sources' => ['grant'],
                'seeking_investment' => true,
                'investment_needed_range' => '10-50k',
                'challenges' => ['funding', 'customers'],
                'jobs_3years_range' => '5-20',
                'scalability_outside_syria' => 'yes',
                'support_needed' => ['incubation', 'tech_consulting', 'funding'],
                'previous_participation' => ['incubation'],
                'status' => 'submitted',
            ],
            [
                'user_id' => $owner->id,
                'full_name' => 'كريم ناصر',
                'governorate' => 'حمص',
                'phone' => '0956777888',
                'email' => 'karim.nasser@demo.local',
                'age' => 31,
                'education_level' => 'bachelor',
                'specialization' => 'تصميم تفاعلي',
                'project_name' => 'EduSpark',
                'project_field' => 'elearning',
                'founding_year' => 2023,
                'executive_summary' => 'EduSpark منصة تعليم تفاعلي للمرحلة الثانوية تقدم دروساً مصورة واختبارات ذكية مع تتبع أداء الطالب. نستهدف الطلاب في المناطق التي تعاني من نقص المعلمين المتخصصين. المنصة لديها 800 مستخدم مسجل و150 مشترك مدفوع.',
                'elevator_pitch' => 'منصة تعليم ثانوي تفاعلية تجمع بين الفيديو والاختبارات الذكية والمتابعة الشخصية.',
                'readiness_stage' => 'revenue',
                'has_prototype' => true,
                'tested_with_users' => true,
                'problem_description' => 'نقص المحتوى التعليمي التفاعلي المحلي للثانوية.',
                'target_customers' => 'طلاب الثانوية وأولياء الأمور.',
                'differentiation' => 'محتوى سوري متوافق مع المنهاج.',
                'competitive_advantages' => ['ux', 'price'],
                'team_size_range' => '6-10',
                'team_roles' => ['developers', 'designers', 'marketing'],
                'technologies' => ['mobile', 'cloud'],
                'market_validation_methods' => ['usage_data', 'market_study'],
                'target_market' => 'syria',
                'current_users_range' => '100-1000',
                'current_customers_range' => '50-200',
                'has_revenue' => true,
                'revenue_sources' => ['subscriptions'],
                'funding_sources' => ['personal', 'partner'],
                'seeking_investment' => false,
                'challenges' => ['marketing', 'expansion'],
                'jobs_3years_range' => '20-50',
                'scalability_outside_syria' => 'yes',
                'support_needed' => ['marketing', 'partnerships'],
                'previous_participation' => ['exhibitions', 'funding_programs'],
                'status' => 'under_review',
                'reviewed_by' => $general?->id,
                'reviewed_at' => now()->subDays(2),
            ],
        ];

        foreach ($profiles as $data) {
            EntrepreneurProfile::query()->updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'project_name' => $data['project_name'],
                ],
                $data
            );
        }
    }
}
