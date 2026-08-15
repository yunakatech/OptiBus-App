<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\PoolScope;
use App\Support\TenantWriteContext;
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

        if ($request->isMethodSafe() && ! $this->requiresOperationalTenantContext($request)) {
            return $next($request);
        }

        if (PoolScope::tenantId($userId) > 0) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(TenantWriteContext::errorPayload(), 409);
        }

        return redirect()
            ->route('platform.dashboard')
            ->with('status', TenantWriteContext::MESSAGE);
    }

    private function isExemptRoute(Request $request): bool
    {
        return $request->routeIs(
            'platform.dashboard',
            'admin-ops.saas',
            'admin-ops.saas.*',
            'admin-ops.roles',
            'api.admin.tenants.*',
            'api.admin.subscriptions.*',
            'api.admin.plans.*',
            'api.admin.invoices.*',
            'api.admin.payment-settings.*',
            'api.admin.roles.*',
            'api.admin.reports.*',
            'api.admin.tenant.switch',
            'admin/tenant/switch',
            'admin/pool/switch',
            'report.*',
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

    private function requiresOperationalTenantContext(Request $request): bool
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        return $routeName === 'admin-ops.index'
            || str_starts_with($routeName, 'admin-ops.')
            || str_starts_with($routeName, 'api.admin.');
    }
}
