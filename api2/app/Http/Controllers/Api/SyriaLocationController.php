<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyriaLocationController extends Controller
{
    private const MAP_CACHE_SECONDS = 21600;

    /* GET /api/locations/governorates */
    public function governorates(): JsonResponse
    {
        $rows = DB::table('syria_locations')
            ->select('gov_pcode as pcode', 'gov_name_ar as name_ar', 'gov_name_en as name_en')
            ->distinct()
            ->orderBy('gov_name_ar')
            ->get();

        return response()->json($rows);
    }

    /* GET /api/locations/districts?gov=SY10 */
    public function districts(Request $request): JsonResponse
    {
        $request->validate(['gov' => 'required|string|max:10']);

        $rows = DB::table('syria_locations')
            ->select('district_pcode as pcode', 'district_name_ar as name_ar', 'district_name_en as name_en')
            ->where('gov_pcode', $request->gov)
            ->distinct()
            ->orderBy('district_name_ar')
            ->get();

        return response()->json($rows);
    }

    /* GET /api/locations/subdistricts?district=SY1005 */
    public function subdistricts(Request $request): JsonResponse
    {
        $request->validate(['district' => 'required|string|max:10']);

        $rows = DB::table('syria_locations')
            ->select('subdistrict_pcode as pcode', 'subdistrict_name_ar as name_ar', 'subdistrict_name_en as name_en')
            ->where('district_pcode', $request->district)
            ->distinct()
            ->orderBy('subdistrict_name_ar')
            ->get();

        return response()->json($rows);
    }

    /* GET /api/locations/communities?subdistrict=SY100500 */
    public function communities(Request $request): JsonResponse
    {
        $request->validate(['subdistrict' => 'required|string|max:10']);

        $rows = DB::table('syria_locations')
            ->select('community_pcode as pcode', 'community_name_ar as name_ar', 'community_name_en as name_en', 'latitude as lat', 'longitude as lng')
            ->where('subdistrict_pcode', $request->subdistrict)
            ->orderBy('community_name_ar')
            ->get();

        return response()->json($rows);
    }

    /* GET /api/locations/map?gov=SY10&limit=3000
       يُرجع جميع التجمعات السكانية كنقاط GIS للخريطة */
    public function mapPoints(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 5000), 7500);
        $govFilter = $request->filled('gov') ? (string) $request->input('gov') : '';

        $cacheKey = 'syria_locations:map:v1:' . md5(json_encode([
            'gov' => $govFilter,
            'limit' => $limit,
        ]));

        $payload = Cache::remember($cacheKey, self::MAP_CACHE_SECONDS, function () use ($request, $limit, $govFilter) {
            $q = DB::table('syria_locations')->select(
                'community_pcode as pcode',
                'community_name_ar as name_ar',
                'community_name_en as name_en',
                'gov_name_ar',
                'district_name_ar',
                'subdistrict_name_ar',
                'latitude as lat',
                'longitude as lng'
            )->whereNotNull('latitude')->whereNotNull('longitude');

            if ($govFilter !== '') {
                if (preg_match('/^SY/i', $govFilter)) {
                    $q->where('gov_pcode', strtoupper($govFilter));
                } else {
                    $q->where(function ($sub) use ($govFilter) {
                        $sub->where('gov_name_ar', 'like', '%' . $govFilter . '%')
                            ->orWhere('gov_name_en', 'like', '%' . $govFilter . '%');
                    });
                }
            }

            $rows = $q->limit($limit)->get();

            return [
                'data' => $rows,
                'total' => $rows->count(),
            ];
        });

        return response()->json($payload);
    }

    /* GET /api/locations/search?q=حلب&limit=15
       GET /api/locations/search?lat=36.2&lng=37.1&near=1   (أقرب مجتمع) */
    public function search(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 15), 50);

        $base = DB::table('syria_locations')->select(
            'community_pcode as pcode',
            'community_name_ar as name_ar',
            'community_name_en as name_en',
            'subdistrict_name_ar',
            'district_name_ar',
            'gov_name_ar',
            'gov_pcode',
            'district_pcode',
            'subdistrict_pcode',
            'latitude as lat',
            'longitude as lng'
        );

        // بحث بالقرب من إحداثيات
        if ($request->boolean('near') && $request->filled('lat') && $request->filled('lng')) {
            $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ]);

            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');

            $rows = $base
                ->orderByRaw(
                    'POW(latitude - ?, 2) + POW(longitude - ?, 2)',
                    [$lat, $lng]
                )
                ->limit($limit)
                ->get();

            return response()->json($rows);
        }

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $needle = '%' . $validated['q'] . '%';
        $rows = $base
            ->where(function ($query) use ($needle) {
                $query->where('community_name_ar', 'like', $needle)
                    ->orWhere('community_name_en', 'like', $needle)
                    ->orWhere('gov_name_ar', 'like', $needle)
                    ->orWhere('gov_name_en', 'like', $needle)
                    ->orWhere('district_name_ar', 'like', $needle)
                    ->orWhere('district_name_en', 'like', $needle)
                    ->orWhere('subdistrict_name_ar', 'like', $needle)
                    ->orWhere('subdistrict_name_en', 'like', $needle);
            })
            ->limit($limit)
            ->get();

        return response()->json($rows);
    }
}
