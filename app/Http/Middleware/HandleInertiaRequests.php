<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use App\Support\PoolScope;
use App\Support\TenantBillingAccess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $isSuperAdmin = $userId > 0 && AccessControl::userIsSuperAdmin($userId);
        $resolvedTenantId = $userId > 0 ? PoolScope::tenantId($userId) : 0;
        $tenantSubscription = $userId > 0 ? PoolScope::tenantSubscription($userId) : null;
        $billingAccess = $userId > 0 ? TenantBillingAccess::forUser($userId) : null;

        $availableTenants = [];
        if ($isSuperAdmin && Schema::hasTable('tenants')) {
            $availableTenants = Cache::remember(
                "inertia:tenants:user:{$userId}:v1",
                now()->addSeconds(30),
                function (): array {
                    $hasUsersTenant = Schema::hasColumn('users', 'tenant_id');
                    $hasPoolsTenant = Schema::hasColumn('pools', 'tenant_id');

                    $query = DB::table('tenants')
                        ->where('tenants.status', '!=', 'canceled')
                        ->leftJoin('subscriptions', function ($join): void {
                            $join->on('tenants.id', '=', 'subscriptions.tenant_id')
                                ->whereRaw('subscriptions.id = (SELECT id FROM subscriptions s2 WHERE s2.tenant_id = tenants.id ORDER BY s2.created_at DESC LIMIT 1)');
                        })
                        ->orderBy('tenants.name');

                    if ($hasUsersTenant) {
                        $query->leftJoinSub(
                            DB::table('users')
                                ->select('tenant_id', DB::raw('COUNT(*) as user_count'))
                                ->groupBy('tenant_id'),
                            'tenant_user_counts',
                            'tenant_user_counts.tenant_id',
                            '=',
                            'tenants.id',
                        );
                    }

                    if ($hasPoolsTenant) {
                        $query->leftJoinSub(
                            DB::table('pools')
                                ->select('tenant_id', DB::raw('COUNT(*) as pool_count'))
                                ->groupBy('tenant_id'),
                            'tenant_pool_counts',
                            'tenant_pool_counts.tenant_id',
                            '=',
                            'tenants.id',
                        );
                    }

                    $rows = $query->select(
                        'tenants.id',
                        'tenants.name',
                        'tenants.slug',
                        'tenants.status',
                        $hasUsersTenant ? DB::raw('COALESCE(tenant_user_counts.user_count, 0) as user_count') : DB::raw('0 as user_count'),
                        $hasPoolsTenant ? DB::raw('COALESCE(tenant_pool_counts.pool_count, 0) as pool_count') : DB::raw('0 as pool_count'),
                    )->get();

                    return $rows->map(static fn ($tenant): array => [
                        'id' => (int) $tenant->id,
                        'name' => (string) $tenant->name,
                        'slug' => (string) $tenant->slug,
                        'status' => (string) ($tenant->status ?? 'active'),
                        'user_count' => (int) ($tenant->user_count ?? 0),
                        'pool_count' => (int) ($tenant->pool_count ?? 0),
                    ])->values()->all();
                }
            );
        }

        $activeTenant = null;
        if ($resolvedTenantId > 0 && Schema::hasTable('tenants')) {
            $activeTenant = Cache::remember(
                "inertia:tenant:user:{$userId}:tenant:{$resolvedTenantId}:v1",
                now()->addSeconds(30),
                function () use ($resolvedTenantId): ?array {
                    $tenant = DB::table('tenants')
                        ->where('id', $resolvedTenantId)
                        ->where('status', '!=', 'canceled')
                        ->first(['id', 'name', 'slug', 'status']);

                    if (! $tenant) {
                        return null;
                    }

                    return [
                        'id' => (int) $tenant->id,
                        'name' => (string) $tenant->name,
                        'slug' => (string) $tenant->slug,
                        'status' => (string) ($tenant->status ?? 'active'),
                    ];
                }
            );
        }

        if ($isSuperAdmin && $resolvedTenantId > 0 && $activeTenant === null) {
            session()->forget('active_tenant_id');
            session()->forget('active_pool_id');
            $resolvedTenantId = 0;
        }

        // Tenant-filtered pools for the current tenant context (cached 30s)
        $availablePools = [];
        if ($userId > 0 && Schema::hasTable('pools') && $resolvedTenantId > 0) {
            $availablePools = Cache::remember(
                "inertia:pools:user:{$userId}:tenant:{$resolvedTenantId}:v1",
                now()->addSeconds(30),
                function () use ($isSuperAdmin, $resolvedTenantId, $userId): array {
                    $query = \Illuminate\Support\Facades\DB::table('pools')
                        ->where('status', 'active')
                        ->select(['id', 'name', 'code']);

                    if (\Illuminate\Support\Facades\Schema::hasColumn('pools', 'tenant_id')) {
                        $query->where('tenant_id', $resolvedTenantId);
                    }

                    if (! $isSuperAdmin) {
                        $poolIds = PoolScope::userPoolIds($userId);
                        if ($poolIds !== []) {
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
        $tenantContextRequired = $this->tenantContextRequired($request, $userId, $resolvedTenantId);

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
                'tenants' => $availableTenants,
                'pools' => $availablePools,
                'active_tenant' => $activeTenant,
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
                'tenant_context_required' => $tenantContextRequired,
                'tenant_subscription' => $tenantSubscription,
                'billing_access' => $billingAccess,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    private function tenantContextRequired(Request $request, int $userId, int $resolvedTenantId): bool
    {
        if ($userId <= 0 || ! AccessControl::userIsSuperAdmin($userId) || $resolvedTenantId > 0) {
            return false;
        }

        $route = (string) $request->route()?->getName();
        if ($route === '') {
            return false;
        }

        $allowedPatterns = [
            'platform.dashboard',
            'admin-ops.saas',
            'admin-ops.saas.*',
            'subscription.*',
            'logout',
            'verification.*',
            'profile.*',
            'security.*',
            'user-password.update',
            'appearance.edit',
        ];

        foreach ($allowedPatterns as $pattern) {
            if ($request->routeIs($pattern)) {
                return false;
            }
        }

        return true;
    }
}
