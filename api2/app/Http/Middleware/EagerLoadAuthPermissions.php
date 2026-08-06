<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يحمّل أدوار وصلاحيات المستخدم مرة واحدة لكل طلب API
 * لتفادي استعلامات Spatie المتكررة عند كل hasRole/can.
 */
class EagerLoadAuthPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing(['roles', 'permissions']);
            // يبني كاش الصلاحيات داخل الطلب (Spatie)
            $user->getAllPermissions();
        }

        return $next($request);
    }
}
