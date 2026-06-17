<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\PoolScope;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionActive
{
    /**
     * Lock operational routes when tenant billing is not active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        if ($userId <= 0 || AccessControl::userIsSuperAdmin($userId) || $this->isAllowedBillingRoute($request)) {
            return $next($request);
        }

        if (! Schema::hasTable('tenants') || ! Schema::hasTable('subscriptions')) {
            return $next($request);
        }

        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId <= 0) {
            return $next($request);
        }

        $tenantStatus = (string) (DB::table('tenants')->where('id', $tenantId)->value('status') ?? '');
        $subscriptionStatus = (string) (DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->value('status') ?? '');

        $tenantOk = $tenantStatus === '' || $tenantStatus === 'active';
        $subscriptionOk = in_array($subscriptionStatus, ['trial', 'active'], true);

        if ($tenantOk && $subscriptionOk) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Langganan tenant belum aktif. Selesaikan pembayaran di halaman subscription.',
                'redirect_url' => route('subscription.index', absolute: false),
                'tenant_status' => $tenantStatus,
                'subscription_status' => $subscriptionStatus,
            ], 402);
        }

        return redirect()->route('subscription.index')->with('status', 'billing_required');
    }

    private function isAllowedBillingRoute(Request $request): bool
    {
        return $request->routeIs(
            'subscription.index',
            'logout',
            'verification.*',
            'profile.*',
            'security.*',
            'user-password.update',
            'appearance.edit',
        );
    }
}
