<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Services\Dashboard\RoleDashboardService;

use App\Support\DashboardAccess;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;



class DashboardController extends Controller

{

    public function __construct(private RoleDashboardService $roleDashboard) {}



    public function index(Request $request): JsonResponse

    {

        $user = $request->user();



        if (!$user) {

            return response()->json(['message' => 'المستخدم غير مسجل الدخول.'], 401);

        }



        DashboardAccess::assertMainDashboardAccess($user);

        $cacheKey = sprintf(
            'dashboard:v1:u%d:b%s:g%s:c%s:co%s:fp%s:r%s',
            $user->id,
            $user->branch_id ?? '0',
            $user->governorate_id ?? '0',
            $user->training_center_id ?? '0',
            $user->consultant_office_id ?? '0',
            $user->funding_partner_id ?? '0',
            md5($user->getRoleNames()->sort()->implode(','))
        );

        $payload = Cache::remember($cacheKey, 120, fn () => $this->roleDashboard->forUser($user));

        return response()->json($payload);

    }

}

