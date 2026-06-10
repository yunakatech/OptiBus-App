<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperAdmin
{
    /**
     * Redirect super admins from operational dashboard to platform dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) ($request->user()?->id ?? 0);

        if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
            // Only redirect for the main dashboard, not for API calls
            if ($request->route()->getName() === 'dashboard' && $request->isMethod('GET')) {
                return redirect()->route('platform.dashboard');
            }
        }

        return $next($request);
    }
}
