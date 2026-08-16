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
            'routes' => '/settings/rute-induk',
            'schedules' => '/settings/jadwal',
            'drivers' => '/settings/driver',
            'luggage-services' => '/settings/tarif-bagasi',
            'segments' => '/settings/segments',
            'customers' => '/settings/customers',
            'units' => '/settings/kategori-armada',
            'armadas', 'armada-categories' => '/settings/armada',
            'pools', 'pool' => '/settings/pool',
            'users' => '/settings/users',
            'roles' => '/settings/roles',
            'activity-logs' => '/settings/logs',
            'reports' => '/report',
            'charters' => '/charters',
            'luggages' => '/luggages',
            'assignments' => '/settings/flows/assignments',
            'customer-bagasi' => '/settings/customer-bagasi',
            'customer-charter' => '/settings/customer-charter',
            'charter-routes' => '/settings/rute-carter',
            default => '/settings/rute-induk',
        };
    }
}
