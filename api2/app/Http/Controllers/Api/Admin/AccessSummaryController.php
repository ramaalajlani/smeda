<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserAccessService;
use App\Support\AccessControlGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessSummaryController extends Controller
{
    public function __construct(private UserAccessService $userAccessService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!AccessControlGuard::isAccessAdministrator($user)) {
            abort(403, 'غير مصرح.');
        }

        if (!$user->hasPermissionTo('manage_user_access') && !$user->hasPermissionTo('view_users')) {
            abort(403, 'غير مصرح.');
        }

        return response()->json([
            'data' => $this->userAccessService->accessSummary(),
        ]);
    }
}
