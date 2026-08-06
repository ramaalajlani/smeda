<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Minimal syria_locations rows for PHPUnit (avoids 7k+ row Excel import per test run).
 */
class SyriaLocationsTestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'gov_pcode' => 'SY01',
                'gov_name_en' => 'Damascus',
                'gov_name_ar' => 'دمشق',
                'district_pcode' => 'SY0101',
                'district_name_en' => 'Damascus',
                'district_name_ar' => 'دمشق',
                'subdistrict_pcode' => 'SY010101',
                'subdistrict_name_en' => 'Central',
                'subdistrict_name_ar' => 'المركز',
                'community_pcode' => 'TEST001',
                'community_name_en' => 'Test Community One',
                'community_name_ar' => 'تجمع تجريبي ١',
                'latitude' => 33.513000,
                'longitude' => 36.292000,
            ],
            [
                'gov_pcode' => 'SY03',
                'gov_name_en' => 'Aleppo',
                'gov_name_ar' => 'حلب',
                'district_pcode' => 'SY0301',
                'district_name_en' => 'Aleppo',
                'district_name_ar' => 'حلب',
                'subdistrict_pcode' => 'SY030101',
                'subdistrict_name_en' => 'Central',
                'subdistrict_name_ar' => 'المركز',
                'community_pcode' => 'TEST002',
                'community_name_en' => 'Test Community Two',
                'community_name_ar' => 'تجمع تجريبي ٢',
                'latitude' => 36.202000,
                'longitude' => 37.134000,
            ],
            [
                'gov_pcode' => 'SY07',
                'gov_name_en' => 'Tartus',
                'gov_name_ar' => 'طرطوس',
                'district_pcode' => 'SY0701',
                'district_name_en' => 'Tartus',
                'district_name_ar' => 'طرطوس',
                'subdistrict_pcode' => 'SY070101',
                'subdistrict_name_en' => 'Coast',
                'subdistrict_name_ar' => 'الساحل',
                'community_pcode' => 'TEST003',
                'community_name_en' => 'Test Community Three',
                'community_name_ar' => 'تجمع تجريبي ٣',
                'latitude' => 34.889000,
                'longitude' => 35.887000,
            ],
        ];

        DB::table('syria_locations')->upsert($rows, ['community_pcode']);
    }
}
