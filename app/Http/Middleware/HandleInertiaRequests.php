<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\PoolScope;
use App\Support\TenantBillingAccess;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);
        $tenantSubscription = $userId > 0 ? PoolScope::tenantSubscription($userId) : null;
        $billingAccess = $userId > 0 ? TenantBillingAccess::forUser($userId) : null;
        $poolSwitcherScope = $userId > 0 ? PoolScope::forCurrentUser(0, $userId, false) : null;

        // Tenant-filtered pools for global pool switcher (cached 30s)
        $availablePools = [];
        if ($userId > 0 && \Illuminate\Support\Facades\Schema::hasTable('pools')) {
            $tenantId = PoolScope::tenantId($userId);
            $availablePools = \Illuminate\Support\Facades\Cache::remember(
                "inertia:pools:user:{$userId}:v2",
                now()->addSeconds(30),
                function () use ($tenantId, $poolSwitcherScope): array {
                    $query = \Illuminate\Support\Facades\DB::table('pools')
                        ->where('status', 'active')
                        ->select(['id', 'name', 'code']);

                    if ($tenantId > 0 && \Illuminate\Support\Facades\Schema::hasColumn('pools', 'tenant_id')) {
                        $query->where('tenant_id', $tenantId);
                    }

                    if (! ($poolSwitcherScope['all'] ?? true)) {
                        $poolIds = $poolSwitcherScope['pool_ids'] ?? [];
                        if (! empty($poolIds)) {
                            $query->whereIn('id', $poolIds);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    }

                    return $query->orderBy('name')->get()->map(fn ($p) => [
                        'id' => (int) $p->id,
                        'name' => (string) $p->name,
                        'code' => (string) ($p->code ?? ''),
                    ])->values()->all();
                }
            );
        }

        $activePoolId = (int) (session('active_pool_id', 0));
        $activePoolName = 'Semua Pool';
        if ($activePoolId > 0) {
            $activePool = collect($availablePools)->firstWhere('id', $activePoolId);
            if ($activePool) {
                $activePoolName = (string) ($activePool['name'] ?? 'Pool');
            } else {
                session()->forget('active_pool_id');
                $activePoolId = 0;
            }
        }

        $poolScope = $userId > 0 ? PoolScope::forCurrentUser(0, $userId) : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'avatar' => $user->avatar ?? null,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'is_super_admin' => AccessControl::userIsSuperAdmin($userId),
                ] : null,
                'permissions' => $userId > 0 ? AccessControl::userPermissions($userId) : [],
                'pools' => $availablePools,
                'pool_scope' => $poolScope ? [
                    'all' => (bool) ($poolScope['all'] ?? true),
                    'pool_ids' => array_values(array_map('intval', $poolScope['pool_ids'] ?? [])),
                    'pool_name' => (string) ($poolScope['pool_name'] ?? 'Semua Pool'),
                    'route_ids' => array_values(array_map('intval', $poolScope['route_ids'] ?? [])),
                    'route_names' => array_values(array_map('strval', $poolScope['route_names'] ?? [])),
                    'labels' => array_values(array_map('strval', $poolScope['labels'] ?? [])),
                ] : null,
                'active_pool' => $activePoolId > 0 ? [
                    'id' => $activePoolId,
                    'name' => $activePoolName,
                ] : null,
                'tenant_subscription' => $tenantSubscription,
                'billing_access' => $billingAccess,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
