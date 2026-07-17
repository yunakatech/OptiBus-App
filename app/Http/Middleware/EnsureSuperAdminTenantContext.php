<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\PoolScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminTenantContext
{
    /**
     * Super admins must pick a tenant before visiting operational routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) ($request->user()?->id ?? 0);

        if ($userId <= 0 || ! AccessControl::userIsSuperAdmin($userId)) {
            return $next($request);
        }

        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        if (PoolScope::tenantId($userId) > 0) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Pilih tenant dulu.',
                'redirect_url' => route('platform.dashboard', absolute: false),
            ], 409);
        }

        return redirect()
            ->route('platform.dashboard')
            ->with('status', 'Pilih tenant dulu.');
    }

    private function isExemptRoute(Request $request): bool
    {
        return $request->routeIs(
            'platform.dashboard',
            'admin-ops.saas',
            'admin-ops.saas.*',
            'api.admin.tenants.*',
            'api.admin.subscriptions.*',
            'api.admin.plans.*',
            'api.admin.invoices.*',
            'api.admin.payment-settings.*',
            'api.admin.tenant.switch',
            'admin/tenant/switch',
            'admin/pool/switch',
            'logout',
            'verification.*',
            'profile.*',
            'security.*',
            'user-password.update',
            'appearance.edit',
            'onboarding',
            'onboarding.store',
        );
    }
}
