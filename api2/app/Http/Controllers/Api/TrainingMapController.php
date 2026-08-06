<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\TrainingCenter;
use App\Models\TrainingCourse;
use App\Models\User;
use App\Support\PaginationLimiter;
use App\Support\TrainingDataScope;
use App\Support\TrainingLocationFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class TrainingMapController extends Controller
{
    public function centers(Request $request): JsonResponse
    {
        $user = $this->resolveOptionalUser($request);
        $limit = PaginationLimiter::mapLimit($request);
        $canViewInternal = TrainingLocationFormatter::canViewInternal($user);

        $cacheKey = null;
        if (!$user) {
            $cacheKey = 'map:training-centers:public:' . md5($limit . '|' . ($canViewInternal ? '1' : '0'));
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return response()->json($cached);
            }
        }

        $query = TrainingCenter::query()
            ->select(['id', 'name', 'code', 'city', 'governorate', 'district', 'address', 'latitude', 'longitude', 'location_visibility', 'accreditation_status'])
            ->active()
            ->approved()
            ->where(function ($scoped) use ($canViewInternal) {
                $scoped->where('location_visibility', 'public');

                if ($canViewInternal) {
                    $scoped->orWhere('location_visibility', 'internal');
                }
            })
            ->where(function ($located) {
                $located->where(function ($coords) {
                    $coords->whereNotNull('latitude')->whereNotNull('longitude');
                })->orWhereNotNull('city');
            });

        if ($user) {
            $query = TrainingDataScope::scopeTrainingCenters($query, $user);
        } else {
            $query->where('location_visibility', 'public');
        }

        $points = $query->orderBy('name')->limit($limit)->get()->map(function (TrainingCenter $center) use ($user) {
            return TrainingLocationFormatter::mapPoint(
                'training_center',
                $center,
                $user,
                'training-centers.certificate',
                ['id' => $center->id]
            );
        })->filter()->values();

        $payload = [
            'data' => $points,
            'meta' => [
                'count' => $points->count(),
                'limit' => $limit,
                'authenticated' => (bool) $user,
            ],
        ];

        if ($cacheKey) {
            Cache::put($cacheKey, $payload, now()->addSeconds(60));
        }

        return response()->json($payload);
    }

    public function courses(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_courses')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض دورات الخريطة.',
            ], 403);
        }

        $limit = PaginationLimiter::mapLimit($request);
        $canViewInternal = TrainingLocationFormatter::canViewInternal($user);

        $query = TrainingDataScope::scopeTrainingCourses(TrainingCourse::query(), $user)
            ->select(['id', 'title', 'course_code', 'delivery_mode', 'status', 'governorate', 'city', 'district', 'address', 'latitude', 'longitude', 'location_visibility', 'venue_name'])
            ->where('delivery_mode', 'offline')
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($scoped) use ($canViewInternal) {
                $scoped->where('location_visibility', 'public');

                if ($canViewInternal) {
                    $scoped->orWhere('location_visibility', 'internal');
                }
            })
            ->where(function ($located) {
                $located->where(function ($coords) {
                    $coords->whereNotNull('latitude')->whereNotNull('longitude');
                })->orWhereNotNull('city');
            });

        $points = $query->orderByDesc('id')->limit($limit)->get()->map(function (TrainingCourse $course) use ($user) {
            return TrainingLocationFormatter::mapPoint(
                'training_course',
                $course,
                $user,
                'api.courses.show',
                ['id' => $course->id]
            );
        })->filter()->values();

        return response()->json([
            'data' => $points,
            'meta' => [
                'count' => $points->count(),
                'limit' => $limit,
            ],
        ]);
    }

    public function trainers(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_trainers')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض مدربي الخريطة.',
            ], 403);
        }

        $limit = PaginationLimiter::mapLimit($request);

        $query = TrainingDataScope::scopeTrainers(Trainer::query(), $user)
            ->select(['id', 'name', 'trainer_code', 'status', 'governorate', 'city', 'district', 'location_visibility', 'service_areas', 'training_center_id'])
            ->where('status', 'active')
            ->whereIn('location_visibility', ['internal', 'public'])
            ->where(function ($located) {
                $located->whereNotNull('city')->orWhereNotNull('governorate');
            });

        $points = $query->orderBy('name')->limit($limit)->get()->map(function (Trainer $trainer) use ($user) {
            $point = TrainingLocationFormatter::mapPoint(
                'trainer',
                $trainer,
                $user,
                'trainers.card',
                ['id' => $trainer->id]
            );

            if ($point) {
                $point['latitude'] = null;
                $point['longitude'] = null;
                $point['address'] = null;
                $point['service_areas'] = $trainer->service_areas;
            }

            return $point;
        })->filter()->values();

        return response()->json([
            'data' => $points,
            'meta' => [
                'count' => $points->count(),
                'limit' => $limit,
            ],
        ]);
    }

    public function trainees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasPermissionTo('view_trainees')) {
            return response()->json([
                'message' => 'ليس لديك صلاحية عرض متدربي الخريطة.',
            ], 403);
        }

        $limit = PaginationLimiter::mapLimit($request);

        $trainees = TrainingDataScope::scopeTrainees(Trainee::query(), $user)
            ->select([
                'id',
                'name',
                'trainee_code',
                'status',
                'governorate',
                'city',
                'district',
                'owned_training_center_id',
            ])
            ->with([
                'ownedTrainingCenter:id,name,latitude,longitude,city,governorate',
            ])
            ->where(function ($located) {
                $located->whereNotNull('owned_training_center_id')
                    ->orWhereNotNull('city')
                    ->orWhereNotNull('governorate')
                    ->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('training_course_trainee as tct')
                            ->join('training_courses as tc', 'tc.id', '=', 'tct.training_course_id')
                            ->whereColumn('tct.trainee_id', 'trainees.id')
                            ->whereNotNull('tc.training_center_id')
                            ->whereNull('tc.deleted_at');
                    });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        // Resolve course-center coords in one query for trainees without owned center coords.
        $needsCourseCenter = $trainees->filter(function (Trainee $t) {
            $c = $t->ownedTrainingCenter;
            return !$c || $c->latitude === null || $c->longitude === null;
        })->pluck('id');

        $courseCenters = collect();
        if ($needsCourseCenter->isNotEmpty()) {
            $courseCenters = DB::table('training_course_trainee as tct')
                ->join('training_courses as tc', 'tc.id', '=', 'tct.training_course_id')
                ->join('training_centers as cen', 'cen.id', '=', 'tc.training_center_id')
                ->whereIn('tct.trainee_id', $needsCourseCenter->all())
                ->whereNotNull('cen.latitude')
                ->whereNotNull('cen.longitude')
                ->whereNull('tc.deleted_at')
                ->whereNull('cen.deleted_at')
                ->orderByDesc('tc.id')
                ->get(['tct.trainee_id', 'cen.id as center_id', 'cen.name as center_name', 'cen.latitude', 'cen.longitude', 'cen.city', 'cen.governorate'])
                ->groupBy('trainee_id')
                ->map(fn ($rows) => $rows->first());
        }

        $govCentroids = $this->governorateCentroids();

        $points = $trainees->map(function (Trainee $trainee, int $index) use ($courseCenters, $govCentroids) {
            $lat = null;
            $lng = null;
            $place = $trainee->city ?: $trainee->governorate;
            $centerName = null;

            $owned = $trainee->ownedTrainingCenter;
            if ($owned && $owned->latitude !== null && $owned->longitude !== null) {
                $lat = (float) $owned->latitude;
                $lng = (float) $owned->longitude;
                $centerName = $owned->name;
                $place = $owned->city ?: ($owned->governorate ?: $place);
            } elseif ($courseCenters->has($trainee->id)) {
                $row = $courseCenters->get($trainee->id);
                $lat = (float) $row->latitude;
                $lng = (float) $row->longitude;
                $centerName = $row->center_name;
                $place = $row->city ?: ($row->governorate ?: $place);
            } else {
                $key = trim((string) ($trainee->governorate ?: ''));
                if ($key !== '' && isset($govCentroids[$key])) {
                    $lat = $govCentroids[$key]['lat'];
                    $lng = $govCentroids[$key]['lng'];
                }
            }

            if ($lat === null || $lng === null) {
                return null;
            }

            // Slight offset so multiple trainees at same center remain visible.
            $jitter = (($index % 7) - 3) * 0.00035;
            $lat += $jitter;
            $lng += (($index % 5) - 2) * 0.00035;

            return [
                'id' => $trainee->id,
                'type' => 'trainee',
                'name' => $trainee->name,
                'trainee_code' => $trainee->trainee_code,
                'governorate' => $trainee->governorate,
                'city' => $trainee->city ?: $place,
                'address' => $centerName ? ('مركز: ' . $centerName) : null,
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => $trainee->status,
                'link' => null,
            ];
        })->filter()->values();

        return response()->json([
            'data' => $points,
            'meta' => [
                'count' => $points->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * @return array<string, array{lat: float, lng: float}>
     */
    private function governorateCentroids(): array
    {
        return Cache::remember('map:gov-centroids:v1', now()->addHours(6), function () {
            $rows = DB::table('syria_locations')
                ->select('gov_name_ar', DB::raw('AVG(latitude) as lat'), DB::raw('AVG(longitude) as lng'))
                ->groupBy('gov_name_ar')
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $name = trim((string) $row->gov_name_ar);
                if ($name === '') {
                    continue;
                }
                $map[$name] = [
                    'lat' => (float) $row->lat,
                    'lng' => (float) $row->lng,
                ];
            }

            return $map;
        });
    }

    private function resolveOptionalUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $tokenable = $accessToken?->tokenable;

        return $tokenable instanceof User ? $tokenable : null;
    }
}
