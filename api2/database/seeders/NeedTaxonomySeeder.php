<?php

namespace Database\Seeders;

use App\Models\NeedLookup;
use App\Models\NeedSector;
use App\Support\NeedTaxonomy;
use Illuminate\Database\Seeder;

/**
 * يزرع القوائم المرجعية لتصنيف الاحتياجات في need_lookups + need_sectors.
 *
 * يستخدم firstOrCreate حتى لا يتجاوز أي تعديلات إدارية لاحقة
 * (تفعيل / تعطيل / ترتيب) على القيم الموجودة.
 *
 * php artisan db:seed --class=NeedTaxonomySeeder
 */
class NeedTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $lookupSets = [
            NeedTaxonomy::TYPE_CATEGORY => NeedTaxonomy::CATEGORIES,
            NeedTaxonomy::TYPE_FACILITY => NeedTaxonomy::FACILITY_TYPES,
            NeedTaxonomy::TYPE_FACILITY_SUBTYPE => NeedTaxonomy::FACILITY_SUBTYPES,
            NeedTaxonomy::TYPE_TARGETING => NeedTaxonomy::TARGETING_TYPES,
        ];

        $created = 0;

        foreach ($lookupSets as $type => $options) {
            $order = 0;
            foreach ($options as $value => $label) {
                $row = NeedLookup::query()->firstOrCreate(
                    ['lookup_type' => $type, 'value' => $value],
                    ['label' => $label, 'sort_order' => $order += 10, 'is_active' => true],
                );
                if ($row->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $order = 0;
        foreach (NeedTaxonomy::SECTORS as $code => $nameAr) {
            $row = NeedSector::query()->firstOrCreate(
                ['code' => $code],
                ['name_ar' => $nameAr, 'sort_order' => $order += 10, 'is_active' => true],
            );
            if ($row->wasRecentlyCreated) {
                $created++;
            }
        }

        NeedTaxonomy::flushCache();

        $this->command?->info("Need taxonomy seeded (new rows: {$created}).");
    }
}
