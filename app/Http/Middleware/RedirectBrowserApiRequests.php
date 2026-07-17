<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBrowserApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirect($request)) {
            return redirect()->to($this->targetPath($request));
        }

        return $next($request);
    }

    private function shouldRedirect(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        return ! $request->expectsJson()
            && ! $request->ajax()
            && ! $request->headers->has('X-Inertia');
    }

    private function targetPath(Request $request): string
    {
        $path = trim((string) $request->route('path', $request->path()), '/');

        if (str_starts_with($path, 'api/admin/')) {
            $path = substr($path, strlen('api/admin/'));
        }

        $segment = explode('/', $path)[0] ?? '';

        return match ($segment) {
            'routes' => route('admin-ops.routes', absolute: false),
            'schedules' => route('admin-ops.schedules', absolute: false),
            'drivers' => route('admin-ops.drivers', absolute: false),
            'luggage-services' => route('admin-ops.services', absolute: false),
            'segments' => route('admin-ops.segments', absolute: false),
            'customers' => route('admin-ops.customers', absolute: false),
            'units' => route('admin-ops.units', absolute: false),
            'armadas', 'armada-categories' => route('admin-ops.armadas', absolute: false),
            'pools', 'pool' => route('admin-ops.pools', absolute: false),
            'users' => route('admin-ops.users', absolute: false),
            'roles' => route('admin-ops.roles', absolute: false),
            'activity-logs' => route('admin-ops.logs', absolute: false),
            'reports' => route('report.index', absolute: false),
            'charters' => route('charters.index', absolute: false),
            'luggages' => route('luggages.index', absolute: false),
            'assignments' => route('admin-ops.flows.assignments', absolute: false),
            'customer-bagasi' => route('admin-ops.master.customer-bagasi', absolute: false),
            'customer-charter' => route('admin-ops.master.customer-charter', absolute: false),
            'charter-routes' => route('admin-ops.master.rute-carter', absolute: false),
            default => route('admin-ops.index', absolute: false),
        };
    }
}
