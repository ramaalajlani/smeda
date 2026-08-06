<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Need;
use App\Models\NeedLookup;
use App\Models\User;
use App\Support\NeedTaxonomy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * اختبارات بنية تصنيف الاحتياجات الجديدة:
 * need_category / facility_type / facility_subtype / targeting_type / القطاعات many-to-many.
 */
class NeedTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    private Branch $aleppo;

    protected function setUp(): void
    {
        parent::setUp();
        NeedTaxonomy::flushCache();
        $this->seed(DatabaseSeeder::class);
        $this->aleppo = Branch::query()->where('code', 'BR-ALEPPO')->firstOrFail();
    }

    protected function tearDown(): void
    {
        NeedTaxonomy::flushCache();
        parent::tearDown();
    }

    private function actingAsDataEntry(): User
    {
        $user = User::factory()->create([
            'governorate_id' => $this->aleppo->governorate_id,
            'branch_id' => $this->aleppo->id,
        ]);
        $user->assignRole('data_entry');
        Sanctum::actingAs($user);

        return $user;
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'احتياج تصنيف',
            'priority' => 'متوسطة',
            'latitude' => 36.202,
            'longitude' => 37.134,
        ], $overrides);
    }

    public function test_create_need_with_full_taxonomy_and_multiple_sectors(): void
    {
        $this->actingAsDataEntry();

        $response = $this->postJson('/api/needs', $this->payload([
            'need_category' => 'facility_establishment',
            'facility_type' => 'business_incubator',
            'facility_subtype' => 'technology',
            'targeting_type' => 'entrepreneurs',
            'sectors' => ['technology', 'crafts'],
            'need_reason' => 'لا توجد حاضنة أعمال في المنطقة.',
        ]))->assertCreated();

        $need = Need::query()->findOrFail((int) $response->json('data.id'));

        $this->assertSame('facility_establishment', $need->need_category);
        $this->assertSame('business_incubator', $need->facility_type);
        $this->assertSame('technology', $need->facility_subtype);
        $this->assertSame('entrepreneurs', $need->targeting_type);
        $this->assertSame('لا توجد حاضنة أعمال في المنطقة.', $need->need_reason);
        $this->assertEqualsCanonicalizing(
            ['technology', 'crafts'],
            $need->sectors()->pluck('code')->all()
        );
        // حقل sector النصي القديم يُملأ تلقائياً للتوافق
        $this->assertNotEmpty($need->sector);
    }

    public function test_every_facility_type_can_be_created(): void
    {
        $this->actingAsDataEntry();

        foreach (array_keys(NeedTaxonomy::FACILITY_TYPES) as $facilityType) {
            $payload = $this->payload([
                'title' => 'إنشاء ' . $facilityType,
                'need_category' => 'facility_establishment',
                'facility_type' => $facilityType,
                'sectors' => ['all'],
            ]);

            if ($facilityType === 'business_incubator') {
                $payload['facility_subtype'] = 'multi_sector';
            }

            $this->postJson('/api/needs', $payload)->assertCreated();
        }

        $this->assertSame(
            count(NeedTaxonomy::FACILITY_TYPES),
            Need::query()->whereNotNull('facility_type')->count()
        );
    }

    public function test_facility_type_required_when_establishing_facility(): void
    {
        $this->actingAsDataEntry();

        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'facility_establishment',
            'sectors' => ['all'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['facility_type']);
    }

    public function test_incubator_subtype_required_only_for_business_incubator(): void
    {
        $this->actingAsDataEntry();

        // حاضنة بدون نوع فرعي → خطأ
        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'facility_establishment',
            'facility_type' => 'business_incubator',
            'sectors' => ['all'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['facility_subtype']);

        // نوع منشأة آخر مع نوع فرعي → يُتجاهل النوع الفرعي ويُحفظ null
        $response = $this->postJson('/api/needs', $this->payload([
            'need_category' => 'facility_establishment',
            'facility_type' => 'studies_center',
            'facility_subtype' => 'technology',
            'sectors' => ['all'],
        ]))->assertCreated();

        $this->assertNull(Need::query()->findOrFail((int) $response->json('data.id'))->facility_subtype);
    }

    public function test_sectors_required_with_need_category(): void
    {
        $this->actingAsDataEntry();

        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'sector_support',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['sectors']);
    }

    public function test_invalid_sector_code_rejected(): void
    {
        $this->actingAsDataEntry();

        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'sector_support',
            'sectors' => ['not_a_sector'],
        ]))->assertUnprocessable();
    }

    public function test_district_required_for_geographic_area_targeting(): void
    {
        $this->actingAsDataEntry();

        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'area_support',
            'targeting_type' => 'geographic_area',
            'sectors' => ['all'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['district_name']);
    }

    public function test_project_name_required_for_existing_project_targeting(): void
    {
        $this->actingAsDataEntry();

        $this->postJson('/api/needs', $this->payload([
            'need_category' => 'project_development',
            'targeting_type' => 'existing_project',
            'sectors' => ['all'],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['organization_name']);
    }

    public function test_coordinates_still_required(): void
    {
        $this->actingAsDataEntry();

        $payload = $this->payload(['need_category' => 'service_gap', 'sectors' => ['services']]);
        unset($payload['latitude'], $payload['longitude']);

        $this->postJson('/api/needs', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_legacy_payload_without_taxonomy_still_works(): void
    {
        $this->actingAsDataEntry();

        // حمولة قديمة كما كانت قبل التطوير — يجب ألا تتعطل
        $this->postJson('/api/needs', $this->payload([
            'sector' => 'زراعة',
            'need_type' => 'تدريب',
        ]))->assertCreated();
    }

    public function test_lookups_endpoint_returns_taxonomy_lists(): void
    {
        $this->actingAsDataEntry();

        $this->getJson('/api/needs/lookups')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'sectors', 'need_types', 'priorities', 'statuses',
                    'need_categories', 'facility_types', 'facility_subtypes',
                    'targeting_types', 'sector_options',
                ],
            ]);
    }

    public function test_lookup_admin_can_deactivate_value_without_deleting_it(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('needs.manage_lookups', 'needs.view');
        Sanctum::actingAs($admin);

        $lookup = NeedLookup::query()
            ->where('lookup_type', 'facility_type')
            ->where('value', 'studies_center')
            ->firstOrFail();

        $this->putJson('/api/needs/lookups/manage/' . $lookup->id, ['is_active' => false])
            ->assertOk();

        $this->assertFalse($lookup->fresh()->is_active);
        // القيمة لم تُحذف — الاحتياجات القديمة المرتبطة بها تبقى سليمة
        $this->assertDatabaseHas('need_lookups', ['id' => $lookup->id, 'value' => 'studies_center']);

        // القيمة المعطّلة تختفي من قوائم الإدخال
        $options = NeedTaxonomy::options(NeedTaxonomy::TYPE_FACILITY);
        $this->assertArrayNotHasKey('studies_center', $options);
    }

    public function test_lookup_management_requires_permission(): void
    {
        $this->actingAsDataEntry();

        $this->getJson('/api/needs/lookups/manage')->assertForbidden();
        $this->postJson('/api/needs/lookups/manage', [
            'lookup_type' => 'facility_type',
            'value' => 'x_center',
            'label' => 'مركز تجريبي',
        ])->assertForbidden();
    }
}
