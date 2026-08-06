<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsultingCategory;

class ConsultingCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code'=>'CON-01','name_ar'=>'دراسة جدوى','name_en'=>'Feasibility Study','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>1],
            ['code'=>'CON-02','name_ar'=>'خطة عمل','name_en'=>'Business Plan','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>2],
            ['code'=>'CON-03','name_ar'=>'تسويق ومبيعات','name_en'=>'Marketing & Sales','applicable_sectors'=>['trade','service','industry'],'requires_isic4'=>false,'sort_order'=>3],
            ['code'=>'CON-04','name_ar'=>'محاسبة وضرائب','name_en'=>'Accounting & Tax','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>4],
            ['code'=>'CON-05','name_ar'=>'قانوني وترخيص','name_en'=>'Legal & Licensing','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>5],
            ['code'=>'CON-06','name_ar'=>'تصنيف النشاط الاقتصادي','name_en'=>'ISIC4 Classification','applicable_sectors'=>null,'requires_isic4'=>true,'sort_order'=>6],
            ['code'=>'CON-07','name_ar'=>'استشارة تمويلية','name_en'=>'Financing Advisory','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>7],
            ['code'=>'CON-08','name_ar'=>'استشارة فنية وإنتاجية','name_en'=>'Technical & Production','applicable_sectors'=>['industry'],'requires_isic4'=>false,'sort_order'=>8],
            ['code'=>'CON-09','name_ar'=>'استشارة زراعية','name_en'=>'Agricultural Advisory','applicable_sectors'=>['agriculture'],'requires_isic4'=>false,'sort_order'=>9],
            ['code'=>'CON-10','name_ar'=>'استشارة تقنية ورقمية','name_en'=>'Tech & Digital','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>10],
            ['code'=>'CON-11','name_ar'=>'استشارة إدارية','name_en'=>'Management Advisory','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>11],
            ['code'=>'CON-12','name_ar'=>'توسع وتطوير','name_en'=>'Growth & Expansion','applicable_sectors'=>null,'requires_isic4'=>false,'sort_order'=>12],
        ];

        foreach ($categories as $cat) {
            ConsultingCategory::updateOrCreate(
                ['code' => $cat['code']],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}
