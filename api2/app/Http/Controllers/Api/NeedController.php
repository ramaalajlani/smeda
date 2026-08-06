<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\AdministrativeUnit;

use App\Models\Governorate;

use App\Models\Need;

use App\Services\Needs\NeedAiClassifyService;

use App\Services\Needs\NeedDashboardService;

use App\Services\Needs\NeedExportService;

use App\Services\Needs\NeedWorkflowService;

use App\Support\NeedDataScope;

use App\Support\NeedStatus;

use App\Support\NeedTaxonomy;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;

use Symfony\Component\HttpFoundation\StreamedResponse;



class NeedController extends Controller

{

    /** @var list<string> */

    private const LEGACY_SECTORS = [

        'زراعة', 'صناعة', 'تجارة', 'خدمات', 'سياحة', 'حرف', 'تكنولوجيا',

        'طاقة', 'تعليم وتدريب', 'ريادة أعمال', 'مشاريع صغيرة ومتوسطة',

        'تنمية محلية', 'استثمار',

    ];



    /** @var list<string> */

    private const LEGACY_NEED_TYPES = [

        'دورة', 'تدريب', 'تمويل', 'دراسة', 'استشارة', 'مشروع', 'حاضنة',

        'بيت حرفي / إنتاجي', 'تسويق', 'دعم فني', 'تجهيزات', 'بنية تحتية',

    ];



    /** @var list<string> */

    private const LEGACY_PRIORITIES = ['عاجلة', 'عالية', 'متوسطة', 'منخفضة'];



    /** @var list<string> */

    private const LEGACY_STATUSES = [

        'بانتظار تدقيق بيانات المحافظة',

        'معاد للتعديل',

        'بانتظار موافقة مدير الفرع',

        'موافق عليه',

        'مرفوض',

        'مصنف',

        'قيد المعالجة',

        'تم الحل',

        'مؤرشف',

    ];



    public function __construct(

        private NeedWorkflowService $workflow,

        private NeedDashboardService $dashboard,

        private NeedExportService $exportService,

        private NeedAiClassifyService $aiClassify,

    ) {}



    public function index(Request $request): JsonResponse

    {

        $this->authorize('viewAny', Need::class);

        $lite = $request->boolean('lite');

        $with = $lite
            ? ['governorate:id,name_ar']
            : [
                'governorate:id,name_ar',
                'branch:id,name',
                'creator:id,name,email',
                'sectors:id,code,name_ar',
            ];

        $query = NeedDataScope::scopeNeeds(
            Need::query()->with($with),
            $request->user()
        );

        if ($lite) {
            $query->select([
                'id', 'need_code', 'title', 'need_owner_type', 'sector',
                'status', 'priority', 'governorate_id', 'created_at',
            ]);
        }

        $this->dashboard->applyFilters($query, $this->filters($request));

        $query->when($request->filled('q'), function ($q) use ($request) {
            $search = trim($request->string('q')->toString());
            if (mb_strlen($search) < 2) {
                return;
            }
            $term = '%'.$search.'%';
            $q->where(function ($inner) use ($term) {
                $inner->where('need_code', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('sector', 'like', $term);
            });
        });

        $rows = $query->orderByDesc('id')
            ->paginate(max(1, min((int) $request->integer('per_page', 20), 100)));

        return response()->json($rows);
    }



    public function store(Request $request): JsonResponse

    {

        $this->authorize('create', Need::class);



        $validated = $request->validate($this->storeRules(), $this->locationMessages());



        NeedDataScope::assertGovernorateScope(

            $request->user(),

            isset($validated['governorate_id']) ? (int) $validated['governorate_id'] : null,

            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,

        );



        $need = $this->workflow->create($request->user(), $validated, $request);



        return response()->json([

            'message' => 'تم حفظ الاحتياج.',

            'data' => $need->load(['governorate:id,name_ar', 'branch:id,name', 'creator:id,name,email']),

        ], 201);

    }



    public function show(Request $request, int $id): JsonResponse

    {

        $need = Need::query()

            ->with([

                'governorate:id,name_ar',

                'branch:id,name',

                'creator:id,name,email',

                'sectors:id,code,name_ar',

                'reviewer:id,name,email',

                'approver:id,name,email',

                'classifier:id,name,email',

                'actionLogs.performer:id,name,email',
                'fundingApplication:id,application_number,status',
                'trainingCourse:id,title,status',

            ])

            ->findOrFail($id);



        $this->authorize('view', $need);



        return response()->json(['data' => $need]);

    }



    public function update(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('update', $need);



        $validated = $request->validate($this->updateRules(), $this->locationMessages());



        NeedDataScope::assertGovernorateScope(

            $request->user(),

            isset($validated['governorate_id']) ? (int) $validated['governorate_id'] : $need->governorate_id,

            isset($validated['branch_id']) ? (int) $validated['branch_id'] : $need->branch_id,

        );



        $need = $this->workflow->update($need, $request->user(), $validated, $request);



        return response()->json([

            'message' => 'تم تحديث الاحتياج.',

            'data' => $need->load(['governorate:id,name_ar', 'branch:id,name', 'creator:id,name,email']),

        ]);

    }



    public function review(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('review', $need);



        $validated = $request->validate(['note' => ['nullable', 'string']]);

        $need = $this->workflow->review($need, $request->user(), $validated['note'] ?? null, $request);



        return response()->json([

            'message' => 'تمت مراجعة الاحتياج.',

            'data' => $need,

        ]);

    }



    public function approve(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('approve', $need);



        $validated = $request->validate(['note' => ['nullable', 'string']]);

        $need = $this->workflow->approve($need, $request->user(), $validated['note'] ?? null, $request);



        return response()->json([

            'message' => 'تم اعتماد الاحتياج.',

            'data' => $need,

        ]);

    }



    public function reject(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('reject', $need);



        $validated = $request->validate(['rejection_reason' => ['required', 'string']]);

        $need = $this->workflow->reject($need, $request->user(), $validated['rejection_reason'], $request);



        return response()->json([

            'message' => 'تم رفض الاحتياج.',

            'data' => $need,

        ]);

    }



    public function returnForEdit(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('returnForEdit', $need);



        $validated = $request->validate(['return_reason' => ['required', 'string']]);

        $need = $this->workflow->returnForEdit($need, $request->user(), $validated['return_reason'], $request);



        return response()->json([

            'message' => 'تم إعادة الاحتياج للتعديل.',

            'data' => $need,

        ]);

    }



    public function classify(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('classify', $need);



        $validated = $request->validate([

            'proposed_intervention' => ['required', 'string', 'max:100'],

            'note' => ['nullable', 'string'],

        ]);



        $need = $this->workflow->classify(

            $need,

            $request->user(),

            $validated['proposed_intervention'],

            $validated['note'] ?? null,

            $request

        );



        return response()->json([

            'message' => 'تم تصنيف الاحتياج.',

            'data' => $need,

        ]);

    }

    /** اقتراح تصنيف عبر ميكروسيرفس AI (نموذج إنشاء). */
    public function aiSuggest(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Need::class);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sector' => ['nullable', 'string', 'max:100'],
            'district_name' => ['nullable', 'string', 'max:150'],
        ]);

        if (!trim(($validated['title'] ?? '').($validated['description'] ?? ''))) {
            return response()->json([
                'message' => 'أدخل اسم الاحتياج أو الوصف أولاً لاقتراح التصنيف.',
            ], 422);
        }

        try {
            return response()->json([
                'data' => $this->aiClassify->suggest($validated),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'تعذر الحصول على اقتراح التصنيف.',
            ], 502);
        }
    }

    /** اقتراح تصنيف عبر ميكروسيرفس AI لاحتياج موجود. */
    public function aiSuggestForNeed(Request $request, int $id): JsonResponse
    {
        $need = Need::query()->findOrFail($id);
        $this->authorize('view', $need);

        try {
            return response()->json([
                'data' => $this->aiClassify->suggestForNeed($need),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'تعذر الحصول على اقتراح التصنيف.',
            ], 502);
        }
    }



    public function resolve(Request $request, int $id): JsonResponse

    {

        $need = Need::query()->findOrFail($id);

        $this->authorize('resolve', $need);



        $validated = $request->validate(['note' => ['nullable', 'string']]);

        $need = $this->workflow->resolve($need, $request->user(), $validated['note'] ?? null, $request);



        return response()->json([

            'message' => 'تم حل الاحتياج.',

            'data' => $need,

        ]);

    }



    public function map(Request $request): JsonResponse

    {

        $this->authorize('map', Need::class);

        $filters = $this->filters($request);

        if ($request->filled('limit')) {
            $filters['limit'] = $request->integer('limit');
        }

        $result = $this->dashboard->mapPoints($request->user(), $filters);



        return response()->json([

            'data' => $result['points'],

            'meta' => $result['meta'],

        ]);

    }



    public function dashboard(Request $request): JsonResponse

    {

        $this->authorize('dashboard', Need::class);



        return response()->json([

            'data' => $this->dashboard->stats($request->user()),

        ]);

    }

    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (
            !$user->hasPermissionTo('needs.dashboard')
            && !$user->hasPermissionTo('needs.create')
            && !$user->hasPermissionTo('needs.review')
        ) {
            abort(403);
        }

        return response()->json(['data' => $this->dashboard->analytics($user)]);
    }



    public function dataEntryWorkspace(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('data_entry') && !\App\Support\AccessControlGuard::isNationalAdministrator($user)) {

            abort(403, 'غير مصرح بلوحة إدخال البيانات.');

        }

        $this->authorize('create', Need::class);



        return response()->json([

            'data' => $this->dashboard->dataEntryWorkspace($user),

        ]);

    }



    public function reviewerWorkspace(Request $request): JsonResponse

    {

        $user = $request->user();

        if (!$user->hasRole('data_reviewer') && !\App\Support\AccessControlGuard::isNationalAdministrator($user)) {

            abort(403, 'غير مصرح بلوحة مراجعة الاحتياجات.');

        }

        if (!$user->hasPermissionTo('needs.review')) {

            abort(403, 'لا تملك صلاحية مراجعة الاحتياجات.');

        }



        return response()->json([

            'data' => $this->dashboard->reviewerWorkspace($user),

        ]);

    }



    /** @return array<string, mixed> */
    public static function legacyLookupsData(): array
    {
        return array_merge([
            'sectors' => self::LEGACY_SECTORS,
            'need_types' => self::LEGACY_NEED_TYPES,
            'priorities' => self::LEGACY_PRIORITIES,
            'statuses' => self::LEGACY_STATUSES,
            'status_codes' => collect(NeedStatus::LEGACY_MAP)->mapWithKeys(
                fn (string $code, string $label) => [$label => $code]
            )->all(),
        ], NeedTaxonomy::lists());
    }

    public function lookups(Request $request): JsonResponse

    {

        $this->authorize('viewAny', Need::class);

        $data = Cache::remember('needs:lookups:v2', 3600, fn () => self::legacyLookupsData());

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'private, max-age=300');

    }



    public function adminUnits(Request $request): JsonResponse

    {

        $this->authorize('viewAny', Need::class);



        // مصدر البيانات: جدول syria_locations (المُعبّأ من ملف الإكسل).
        // ربط معرّف المحافظة (governorates.code) برمز pcode في syria_locations.
        $govCodeToPcode = [
            'damascus' => 'SY01', 'aleppo' => 'SY02', 'rural_damascus' => 'SY03',
            'homs' => 'SY04', 'hama' => 'SY05', 'latakia' => 'SY06', 'idlib' => 'SY07',
            'hasakah' => 'SY08', 'deir_ez_zor' => 'SY09', 'tartus' => 'SY10',
            'raqqa' => 'SY11', 'daraa' => 'SY12', 'suweida' => 'SY13', 'quneitra' => 'SY14',
        ];

        $govId = null;
        $govPcode = null;
        $govNameAr = null;

        if ($request->filled('governorate_id')) {
            $govId = $request->integer('governorate_id');
            $gov = Governorate::query()->find($govId);

            if ($gov) {
                $govNameAr = $gov->name_ar;
                $govPcode = $govCodeToPcode[$gov->code] ?? null;
            }
        }

        $level = $request->string('level')->toString() ?: 'all';

        // مستوى المنطقة فقط — خفيف وسريع لإدخال البيانات
        if ($level === 'district') {
            $cacheKey = 'needs:admin-units:district:'.md5(($govPcode ?: '').'|'.($govNameAr ?: '').'|'.$request->input('q'));

            $data = Cache::remember($cacheKey, 1800, function () use ($govPcode, $govNameAr, $govId, $request) {
                $districts = DB::table('syria_locations')
                    ->select('gov_name_ar', 'district_name_ar')
                    ->whereNotNull('district_name_ar')
                    ->where('district_name_ar', '!=', '')
                    ->when($govPcode, fn ($q) => $q->where('gov_pcode', $govPcode))
                    ->when($govPcode === null && $govNameAr, fn ($q) => $q->where('gov_name_ar', $govNameAr))
                    ->when($request->filled('q'), function ($q) use ($request) {
                        $term = '%'.$request->string('q').'%';
                        $q->where('district_name_ar', 'like', $term);
                    })
                    ->groupBy('gov_name_ar', 'district_name_ar')
                    ->orderBy('district_name_ar')
                    ->limit(200)
                    ->get();

                return $districts->map(fn ($r) => [
                    'governorate_id' => $govId,
                    'governorate' => ['name_ar' => $r->gov_name_ar],
                    'district_name' => $r->district_name_ar,
                    'countryside_name' => null,
                    'unit_name' => $r->district_name_ar,
                    'is_active' => true,
                ])->values()->all();
            });

            return response()->json(['data' => $data])
                ->header('Cache-Control', 'private, max-age=300');
        }

        // نواحي منطقة واحدة فقط
        if ($level === 'subdistrict') {
            $districtName = trim($request->string('district_name')->toString());
            if ($districtName === '') {
                return response()->json(['data' => []]);
            }

            $cacheKey = 'needs:admin-units:sub:'.md5(($govPcode ?: '').'|'.($govNameAr ?: '').'|'.$districtName);

            $data = Cache::remember($cacheKey, 1800, function () use ($govPcode, $govNameAr, $govId, $districtName) {
                $subs = DB::table('syria_locations')
                    ->select('gov_name_ar', 'district_name_ar', 'subdistrict_name_ar')
                    ->where('district_name_ar', $districtName)
                    ->whereNotNull('subdistrict_name_ar')
                    ->where('subdistrict_name_ar', '!=', '')
                    ->when($govPcode, fn ($q) => $q->where('gov_pcode', $govPcode))
                    ->when($govPcode === null && $govNameAr, fn ($q) => $q->where('gov_name_ar', $govNameAr))
                    ->groupBy('gov_name_ar', 'district_name_ar', 'subdistrict_name_ar')
                    ->orderBy('subdistrict_name_ar')
                    ->limit(200)
                    ->get();

                return $subs->map(fn ($r) => [
                    'governorate_id' => $govId,
                    'governorate' => ['name_ar' => $r->gov_name_ar],
                    'district_name' => $r->district_name_ar,
                    'countryside_name' => $r->subdistrict_name_ar,
                    'unit_name' => $r->subdistrict_name_ar,
                    'is_active' => true,
                ])->values()->all();
            });

            return response()->json(['data' => $data])
                ->header('Cache-Control', 'private, max-age=300');
        }

        $query = DB::table('syria_locations')
            ->select('gov_name_ar', 'district_name_ar', 'subdistrict_name_ar')
            ->whereNotNull('district_name_ar')
            ->where('district_name_ar', '!=', '')
            ->when($govPcode, fn ($q) => $q->where('gov_pcode', $govPcode))
            ->when($govPcode === null && $govNameAr, fn ($q) => $q->where('gov_name_ar', $govNameAr))
            ->when($request->filled('district_name'), fn ($q) => $q->where('district_name_ar', $request->string('district_name')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';

                $q->where(function ($inner) use ($term) {
                    $inner->where('district_name_ar', 'like', $term)
                        ->orWhere('subdistrict_name_ar', 'like', $term)
                        ->orWhere('community_name_ar', 'like', $term);
                });
            })
            ->distinct()
            ->orderBy('gov_name_ar')
            ->orderBy('district_name_ar')
            ->orderBy('subdistrict_name_ar');

        $perPage = max(1, min((int) $request->integer('per_page', 25), 2000));
        $rows = $query->limit($perPage)->get();

        $data = $rows->map(fn ($r) => [
            'governorate_id' => $govId,
            'governorate' => ['name_ar' => $r->gov_name_ar],
            'district_name' => $r->district_name_ar,       // المنطقة
            'countryside_name' => $r->subdistrict_name_ar, // الناحية
            'unit_name' => $r->subdistrict_name_ar,
            'is_active' => true,
        ])->values();

        return response()->json(['data' => $data]);

    }



    public function export(Request $request): StreamedResponse

    {

        $this->authorize('export', Need::class);



        return $this->exportService->exportCsv($request->user(), $this->filters($request));

    }



    /**

     * @return array<string, mixed>

     */

    private function filters(Request $request): array

    {

        return $request->only([

            'governorate_id',

            'branch_id',

            'sector',

            'sector_code',

            'priority',

            'status',

            'need_type',

            'need_category',

            'facility_type',

            'facility_subtype',

            'targeting_type',

            'district_name',

            'need_owner_type',

            'need_scope',

            'source_platform',

            'proposed_intervention',

            'need_complexity',

            'q',

        ]);

    }



    /**
     * رسائل عربية واضحة لحقول الموقع والتصنيف.
     *
     * @return array<string, string>
     */
    private function locationMessages(): array
    {
        $msg = 'يرجى تحديد موقع الاحتياج على الخريطة، لأن الاحتياجات بدون موقع لن تظهر على خريطة GIS.';

        return [
            'latitude.required' => $msg,
            'longitude.required' => $msg,
            'latitude.between' => 'الموقع المحدد خارج حدود الجمهورية السورية، يرجى تحديد موقع صحيح على الخريطة.',
            'longitude.between' => 'الموقع المحدد خارج حدود الجمهورية السورية، يرجى تحديد موقع صحيح على الخريطة.',
            'facility_type.required_if' => 'نوع المنشأة مطلوب عند اختيار إنشاء أو تطوير منشأة.',
            'facility_type.in' => 'نوع المنشأة المحدد غير موجود ضمن القائمة المعتمدة.',
            'facility_subtype.required_if' => 'نوع الحاضنة مطلوب عند اختيار حاضنة أعمال.',
            'facility_subtype.in' => 'نوع الحاضنة المحدد غير صالح.',
            'need_category.in' => 'تصنيف الاحتياج المحدد غير صالح.',
            'targeting_type.in' => 'نوع الاستهداف المحدد غير صالح.',
            'sectors.required_with' => 'يجب اختيار قطاع واحد على الأقل (أو "جميع القطاعات").',
            'sectors.min' => 'يجب اختيار قطاع واحد على الأقل (أو "جميع القطاعات").',
            'sectors.*.in' => 'أحد القطاعات المحددة غير موجود ضمن القائمة المعتمدة.',
            'district_name.required_if' => 'المنطقة مطلوبة عند اختيار "دعم منطقة" كنوع استهداف.',
            'organization_name.required_if' => 'اسم المشروع القائم مطلوب عند اختيار "تنمية مشروع قائم".',
        ];
    }

    /**

     * @return array<string, list<string>>

     */

    private function storeRules(): array

    {

        return [

            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'summary' => ['nullable', 'string', 'max:500'],

            'need_owner_type' => ['nullable', 'in:citizen,state'],

            'need_scope' => ['nullable', 'in:individual,project,local,governorate,national,sectoral'],

            'need_complexity' => ['nullable', 'in:general,specific'],

            'need_type' => ['nullable', 'string', 'max:100'],

            'need_category' => ['nullable', 'string', 'max:100', Rule::in(NeedTaxonomy::values(NeedTaxonomy::TYPE_CATEGORY))],

            'facility_type' => [
                'nullable',
                'required_if:need_category,facility_establishment,facility_development',
                Rule::in(NeedTaxonomy::values(NeedTaxonomy::TYPE_FACILITY)),
            ],

            'facility_subtype' => [
                'nullable',
                'required_if:facility_type,business_incubator',
                Rule::in(NeedTaxonomy::values(NeedTaxonomy::TYPE_FACILITY_SUBTYPE)),
            ],

            'targeting_type' => ['nullable', Rule::in(NeedTaxonomy::values(NeedTaxonomy::TYPE_TARGETING))],

            'sectors' => ['nullable', 'array', 'min:1', 'required_with:need_category'],

            'sectors.*' => ['string', Rule::in(NeedTaxonomy::sectorCodes())],

            'need_reason' => ['nullable', 'string'],

            'proposed_intervention' => ['nullable', 'string', 'max:100'],

            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],

            'district_name' => ['nullable', 'string', 'max:255', 'required_if:targeting_type,geographic_area'],

            'administrative_unit_name' => ['nullable', 'string', 'max:255'],

            'countryside_name' => ['nullable', 'string', 'max:255'],

            'locality_name' => ['nullable', 'string', 'max:255'],

            'village_or_neighborhood' => ['nullable', 'string', 'max:255'],

            'address_details' => ['nullable', 'string'],

            'latitude' => ['required', 'numeric', 'between:32,37.5'],

            'longitude' => ['required', 'numeric', 'between:35.4,42.5'],

            'location_source' => ['nullable', 'string', 'max:100'],

            'sector' => ['nullable', 'string', 'max:100'],

            'economic_sector' => ['nullable', 'string', 'max:100'],

            'syrsic_section' => ['nullable', 'string', 'max:100'],

            'syrsic_division' => ['nullable', 'string', 'max:100'],

            'syrsic_group' => ['nullable', 'string', 'max:100'],

            'syrsic_class' => ['nullable', 'string', 'max:100'],

            'syrsic_activity' => ['nullable', 'string', 'max:100'],

            'priority' => ['nullable', 'string', 'max:50'],

            'state_need_level' => ['nullable', 'string', 'max:100'],

            'citizen_need_profile' => ['nullable', 'string', 'max:100'],

            'responsible_entity' => ['nullable', 'string', 'max:255'],

            'applicant_name' => ['nullable', 'string', 'max:255'],

            'applicant_phone' => ['nullable', 'string', 'max:50'],

            'applicant_email' => ['nullable', 'email', 'max:255'],

            'applicant_type' => ['nullable', 'string', 'max:100'],

            'organization_name' => ['nullable', 'string', 'max:255', 'required_if:targeting_type,existing_project'],

            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],

            'expected_jobs_count' => ['nullable', 'integer', 'min:0'],

            'expected_projects_count' => ['nullable', 'integer', 'min:0'],

            'impact_level' => ['nullable', 'string', 'max:100'],

            'urgency_level' => ['nullable', 'string', 'max:100'],

            'expected_duration' => ['nullable', 'string', 'max:100'],

            'available_partners' => ['nullable', 'string'],

            'obstacles' => ['nullable', 'string'],

            'requirements' => ['nullable', 'string'],

            'notes' => ['nullable', 'string'],

            'metadata' => ['nullable', 'array'],

            'is_public' => ['nullable', 'boolean'],

            'training_course_id' => ['nullable', 'integer', 'exists:training_courses,id'],

            'funding_application_id' => ['nullable', 'integer', 'exists:funding_applications,id'],

        ];

    }



    /**

     * @return array<string, list<string>>

     */

    private function updateRules(): array

    {

        $rules = $this->storeRules();

        $rules['title'] = ['sometimes', 'string', 'max:255'];



        return $rules;

    }

}


