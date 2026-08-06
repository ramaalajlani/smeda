<?php

namespace App\Http\Middleware;

use App\Support\DashboardAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        DashboardAccess::assertMainDashboardAccess($request->user());

        return $next($request);
    }
}
