<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Governorate;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncubatorSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->where('email', 'general@system.com')->value('id');

        $rows = [
            [
                'code' => 'INC-DAM-01',
                'name' => 'حاضنة دمشق للمشاريع التقنية',
                'description' => 'حاضنة متخصصة بدعم المشاريع التقنية والرقمية من الفكرة إلى الإطلاق.',
                'sector' => 'tech',
                'location' => 'دمشق — المزة',
                'governorate_code' => 'damascus',
                'capacity' => 25,
            ],
            [
                'code' => 'INC-ALP-01',
                'name' => 'حاضنة حلب الصناعية',
                'description' => 'احتضان المشاريع الصناعية والإنتاجية الصغيرة والمتوسطة.',
                'sector' => 'industrial',
                'location' => 'حلب — السليمانية',
                'governorate_code' => 'aleppo',
                'capacity' => 30,
            ],
            [
                'code' => 'INC-HMS-01',
                'name' => 'حاضنة حمص للخدمات',
                'description' => 'دعم مشاريع الخدمات والتجارة المحلية.',
                'sector' => 'services',
                'location' => 'حمص — الوعر',
                'governorate_code' => 'homs',
                'capacity' => 20,
            ],
            [
                'code' => 'INC-IDB-01',
                'name' => 'حاضنة إدلب الزراعية',
                'description' => 'تطوير المشاريع الزراعية والتصنيع الغذائي.',
                'sector' => 'agricultural',
                'location' => 'إدلب — المركز',
                'governorate_code' => 'idlib',
                'capacity' => 18,
            ],
            [
                'code' => 'INC-LTK-01',
                'name' => 'حاضنة اللاذقية الإبداعية',
                'description' => 'احتضان المشاريع الإبداعية والسياحية والثقافية.',
                'sector' => 'creative',
                'location' => 'اللاذقية — الكورنيش',
                'governorate_code' => 'latakia',
                'capacity' => 15,
            ],
        ];

        foreach ($rows as $row) {
            $governorate = Governorate::query()->where('code', $row['governorate_code'])->first();
            $branch = $governorate
                ? Branch::query()->where('governorate_id', $governorate->id)->first()
                : null;

            Incubator::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'sector' => $row['sector'],
                    'location' => $row['location'],
                    'governorate_id' => $governorate?->id,
                    'branch_id' => $branch?->id,
                    'phone' => '011-0000000',
                    'email' => strtolower(str_replace('-', '.', $row['code'])) . '@demo.local',
                    'capacity' => $row['capacity'],
                    'status' => 'active',
                    'created_by' => $creator,
                ]
            );
        }
    }
}
