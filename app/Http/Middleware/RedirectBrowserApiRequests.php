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
            return redirect()->to(url($this->targetPath($request)));
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
            'routes' => '/admin-ops/rute-induk',
            'schedules' => '/admin-ops/jadwal',
            'drivers' => '/admin-ops/driver',
            'luggage-services' => '/admin-ops/tarif-bagasi',
            'segments' => '/admin-ops/segments',
            'customers' => '/admin-ops/customers',
            'units' => '/admin-ops/kategori-armada',
            'armadas', 'armada-categories' => '/admin-ops/armada',
            'pools', 'pool' => '/admin-ops/pool',
            'users' => '/admin-ops/users',
            'roles' => '/admin-ops/roles',
            'activity-logs' => '/admin-ops/logs',
            'reports' => '/report',
            'charters' => '/charters',
            'luggages' => '/luggages',
            'assignments' => '/admin-ops/flows/assignments',
            'customer-bagasi' => '/admin-ops/customer-bagasi',
            'customer-charter' => '/admin-ops/customer-charter',
            'charter-routes' => '/admin-ops/rute-carter',
            default => '/admin-ops',
        };
    }
}
