<?php

namespace App\Support;

use App\Models\NeedLookup;
use App\Models\NeedSector;
use Illuminate\Support\Facades\Schema;

/**
 * التصنيف المرجعي لوحدة خريطة الاحتياجات:
 * تصنيف الاحتياج / نوع المنشأة / النوع الفرعي / نوع الاستهداف / القطاعات.
 *
 * القوائم تُقرأ من قاعدة البيانات (need_lookups + need_sectors) إن وُجدت،
 * مع fallback للثوابت أدناه حفاظاً على عمل النظام قبل تشغيل الـ seeder.
 */
class NeedTaxonomy
{
    public const TYPE_CATEGORY = 'need_category';
    public const TYPE_FACILITY = 'facility_type';
    public const TYPE_FACILITY_SUBTYPE = 'facility_subtype';
    public const TYPE_TARGETING = 'targeting_type';

    /** @var array<string, string> */
    public const CATEGORIES = [
        'facility_establishment' => 'إنشاء منشأة أو مركز',
        'facility_development' => 'تطوير منشأة قائمة',
        'project_development' => 'تنمية مشروع',
        'sector_support' => 'دعم قطاع محدد',
        'area_support' => 'دعم منطقة جغرافية',
        'service_gap' => 'توفير خدمة غير متاحة',
        'other' => 'أخرى',
    ];

    /** @var array<string, string> */
    public const FACILITY_TYPES = [
        'family_development_center' => 'مركز تنمية أسرية',
        'small_projects_development_unit' => 'وحدة تنمية المشروعات الصغيرة',
        'project_environment' => 'بيئة / بيت مشاريع',
        'business_incubator' => 'حاضنة أعمال',
        'business_hub' => 'مركز أعمال Business Hub',
        'entrepreneurship_center' => 'مركز ريادي لرواد الأعمال',
        'free_workspace' => 'مساحة عمل حرة Free Workspace',
        'studies_center' => 'مركز دراسات',
        'financing_services_center' => 'مركز خدمات تمويل',
        'micro_project_registration_center' => 'مركز تراخيص وتسجيل الأسر المنتجة والمشروعات متناهية الصغر',
    ];

    /** @var array<string, string> النوع الفرعي — يظهر فقط مع حاضنة الأعمال */
    public const FACILITY_SUBTYPES = [
        'technology' => 'تقنية',
        'crafts' => 'حرفية',
        'agricultural' => 'زراعية',
        'multi_sector' => 'متعددة القطاعات',
    ];

    /** @var array<string, string> */
    public const TARGETING_TYPES = [
        'existing_project' => 'تنمية مشروع قائم',
        'specific_sector' => 'استهداف قطاع محدد',
        'geographic_area' => 'دعم منطقة',
        'entrepreneurs' => 'دعم رواد الأعمال',
        'productive_families' => 'دعم الأسر المنتجة',
        'micro_projects' => 'دعم المشروعات متناهية الصغر',
        'other' => 'أخرى',
    ];

    /** @var array<string, string> */
    public const SECTORS = [
        'agriculture' => 'زراعي',
        'industry' => 'صناعي',
        'crafts' => 'حرفي',
        'trade' => 'تجاري',
        'services' => 'خدمي',
        'technology' => 'تقني',
        'tourism' => 'سياحي',
        'food' => 'غذائي',
        'health' => 'صحي',
        'education' => 'تعليمي',
        'creative' => 'ثقافي وإبداعي',
        'environment' => 'بيئي',
        'all' => 'جميع القطاعات',
    ];

    /**
     * ربط قيم القطاع النصية القديمة (LEGACY_SECTORS العربية) بأكواد القطاعات الجديدة.
     *
     * @var array<string, string>
     */
    public const LEGACY_SECTOR_MAP = [
        'زراعة' => 'agriculture',
        'صناعة' => 'industry',
        'تجارة' => 'trade',
        'خدمات' => 'services',
        'سياحة' => 'tourism',
        'حرف' => 'crafts',
        'تكنولوجيا' => 'technology',
        'تعليم وتدريب' => 'education',
    ];

    /** @var array<string, array<string, string>>|null cache للطلب الواحد */
    private static ?array $lookupCache = null;

    private static ?array $sectorCache = null;

    /** @return array<string, string> value => label */
    public static function options(string $lookupType): array
    {
        $constants = self::constantsFor($lookupType);

        if (self::$lookupCache === null) {
            self::$lookupCache = self::loadLookupsFromDb();
        }

        return self::$lookupCache[$lookupType] ?? $constants;
    }

    /** @return array<string, string> code => name_ar (المفعّلة فقط) */
    public static function sectorOptions(): array
    {
        if (self::$sectorCache !== null) {
            return self::$sectorCache;
        }

        try {
            if (Schema::hasTable('need_sectors')) {
                $rows = NeedSector::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->pluck('name_ar', 'code')
                    ->all();

                if (!empty($rows)) {
                    return self::$sectorCache = $rows;
                }
            }
        } catch (\Throwable) {
            // fallback للثوابت
        }

        return self::$sectorCache = self::SECTORS;
    }

    /** @return list<string> */
    public static function values(string $lookupType): array
    {
        return array_keys(self::options($lookupType));
    }

    /** @return list<string> */
    public static function sectorCodes(): array
    {
        return array_keys(self::sectorOptions());
    }

    public static function label(string $lookupType, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::options($lookupType)[$value]
            ?? self::constantsFor($lookupType)[$value]
            ?? $value;
    }

    public static function sectorLabel(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return self::sectorOptions()[$code] ?? self::SECTORS[$code] ?? $code;
    }

    /**
     * القوائم بصيغة جاهزة لواجهة الـ API: [{value, label}, ...]
     *
     * @return array<string, list<array{value: string, label: string}>>
     */
    public static function lists(): array
    {
        $format = fn (array $options) => collect($options)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        return [
            'need_categories' => $format(self::options(self::TYPE_CATEGORY)),
            'facility_types' => $format(self::options(self::TYPE_FACILITY)),
            'facility_subtypes' => $format(self::options(self::TYPE_FACILITY_SUBTYPE)),
            'targeting_types' => $format(self::options(self::TYPE_TARGETING)),
            'sector_options' => $format(self::sectorOptions()),
        ];
    }

    public static function flushCache(): void
    {
        self::$lookupCache = null;
        self::$sectorCache = null;
    }

    /** @return array<string, string> */
    private static function constantsFor(string $lookupType): array
    {
        return match ($lookupType) {
            self::TYPE_CATEGORY => self::CATEGORIES,
            self::TYPE_FACILITY => self::FACILITY_TYPES,
            self::TYPE_FACILITY_SUBTYPE => self::FACILITY_SUBTYPES,
            self::TYPE_TARGETING => self::TARGETING_TYPES,
            default => [],
        };
    }

    /** @return array<string, array<string, string>> */
    private static function loadLookupsFromDb(): array
    {
        try {
            if (!Schema::hasTable('need_lookups')) {
                return [];
            }

            $grouped = [];

            NeedLookup::query()
                ->whereIn('lookup_type', [
                    self::TYPE_CATEGORY,
                    self::TYPE_FACILITY,
                    self::TYPE_FACILITY_SUBTYPE,
                    self::TYPE_TARGETING,
                ])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['lookup_type', 'value', 'label'])
                ->each(function (NeedLookup $row) use (&$grouped) {
                    $grouped[$row->lookup_type][$row->value] = $row->label;
                });

            return $grouped;
        } catch (\Throwable) {
            return [];
        }
    }
}
