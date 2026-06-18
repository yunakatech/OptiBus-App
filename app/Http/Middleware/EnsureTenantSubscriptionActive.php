<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\TenantBillingAccess;
use Closure;
use Illuminate\Http\Request;
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

        $billingAccess = TenantBillingAccess::forUser($userId);

        if (! ($billingAccess['locked'] ?? false)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Langganan tenant belum aktif. Selesaikan pembayaran di halaman subscription.',
                'redirect_url' => route('subscription.index', absolute: false),
                'billing_access' => $billingAccess,
            ], 402);
        }

        return redirect()->route('subscription.index')->with('status', 'billing_required');
    }

    private function isAllowedBillingRoute(Request $request): bool
    {
        return $request->routeIs(
            'subscription.index',
            'subscription.checkout',
            'logout',
            'verification.*',
            'profile.*',
            'security.*',
            'user-password.update',
            'appearance.edit',
        );
    }
}
