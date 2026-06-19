<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PoolScope
{
    /** @var array<string, int> */
    private static array $tenantIdCache = [];

    /** @var array<string, int> */
    private static array $userTenantColumnCache = [];

    /** @var array<string, array<int, int>> */
    private static array $userPoolIdsCache = [];

    /** @var array<string, array<string, mixed>> */
    private static array $scopeCache = [];

    public static function tablesReady(): bool
    {
        return Schema::hasTable('pools')
            && Schema::hasTable('pool_route')
            && Schema::hasTable('pool_user')
            && Schema::hasTable('routes');
    }

    public static function flushRequestCache(): void
    {
        self::$tenantIdCache = [];
        self::$userTenantColumnCache = [];
        self::$userPoolIdsCache = [];
        self::$scopeCache = [];
    }

    /**
     * Resolve the current user's tenant_id.
     * Returns 0 if the user is a platform super admin (cross-tenant).
     */
    public static function tenantId(?int $userId = null): int
    {
        $userId ??= (int) (auth()->id() ?? 0);
        if ($userId <= 0) {
            return 0;
        }

        $cacheKey = self::requestCacheKey('tenant:'.$userId);
        if (array_key_exists($cacheKey, self::$tenantIdCache)) {
            return self::$tenantIdCache[$cacheKey];
        }

        // Super admins bypass tenant scope → return 0 (all tenants)
        if (AccessControl::userIsSuperAdmin($userId)) {
            return self::$tenantIdCache[$cacheKey] = 0;
        }

        // Check user-level tenant_id first
        $userTenantId = self::userTenantIdFromColumn($userId);
        if ($userTenantId > 0) {
            return self::$tenantIdCache[$cacheKey] = $userTenantId;
        }

        // Fallback: derive tenant_id from user's assigned pools
        if (self::tablesReady() && Schema::hasColumn('pools', 'tenant_id')) {
            $poolIds = self::userPoolIds($userId);
            if ($poolIds !== []) {
                return self::$tenantIdCache[$cacheKey] = (int) (DB::table('pools')->whereIn('id', $poolIds)->value('tenant_id') ?? 0);
            }
        }

        return self::$tenantIdCache[$cacheKey] = 0;
    }

    /**
     * Scope a query builder to the current user's tenant.
     * Skips scoping when tenantId is 0 (super admin = all tenants).
     */
    public static function applyTenantScope(Builder $query, string $tenantColumn = 'tenant_id', ?int $userId = null): void
    {
        $tenantId = self::tenantId($userId);
        if ($tenantId <= 0) {
            return;
        }

        $query->where($tenantColumn, $tenantId);
    }

    /**
     * Get the current tenant's subscription info.
     * Returns null if SaaS tables aren't ready or user is super admin.
     *
     * @return array{subscription_id: int, tenant_id: int, tenant_name: string, tenant_status: string, plan_id: int, plan_name: string, plan_slug: string, subscription_status: string, trial_ends_at: string|null, ends_at: string|null}|null
     */
    public static function tenantSubscription(?int $userId = null): ?array
    {
        $tenantId = self::tenantId($userId);
        if ($tenantId <= 0 || ! Schema::hasTable('subscriptions') || ! Schema::hasTable('plans')) {
            return null;
        }

        $sub = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('subscriptions.tenant_id', $tenantId)
            ->orderByRaw("CASE subscriptions.status WHEN 'active' THEN 0 WHEN 'trial' THEN 1 WHEN 'pending_payment' THEN 2 WHEN 'past_due' THEN 3 WHEN 'suspended' THEN 4 ELSE 5 END")
            ->orderByDesc('subscriptions.created_at')
            ->select(
                'subscriptions.id as subscription_id',
                'subscriptions.tenant_id',
                'tenants.name as tenant_name',
                'tenants.status as tenant_status',
                'subscriptions.plan_id',
                'plans.name as plan_name',
                'plans.slug as plan_slug',
                'subscriptions.status as subscription_status',
                'subscriptions.trial_ends_at',
                'subscriptions.ends_at',
            )
            ->first();

        if (! $sub) {
            return null;
        }

        return [
            'subscription_id' => (int) $sub->subscription_id,
            'tenant_id' => (int) $sub->tenant_id,
            'tenant_name' => (string) $sub->tenant_name,
            'tenant_status' => (string) ($sub->tenant_status ?? ''),
            'plan_id' => (int) $sub->plan_id,
            'plan_name' => (string) $sub->plan_name,
            'plan_slug' => (string) $sub->plan_slug,
            'subscription_status' => (string) $sub->subscription_status,
            'trial_ends_at' => $sub->trial_ends_at,
            'ends_at' => $sub->ends_at,
        ];
    }

    /**
     * @return array{all: bool, pool_ids: array<int, int>, route_ids: array<int, int>, route_names: array<int, string>, labels: array<int, string>, pool_name: string, target_revenue: float, tenant_id: int}
     */
    public static function forCurrentUser(int $poolId = 0, ?int $userId = null, bool $useSessionPool = true): array
    {
        $fallback = [
            'all' => true,
            'pool_ids' => [],
            'route_ids' => [],
            'route_names' => [],
            'labels' => [],
            'pool_name' => 'Semua Pool',
            'target_revenue' => 0.0,
            'tenant_id' => 0,
        ];

        // Global pool override from session — applies across all pages/API calls
        if ($useSessionPool && $poolId <= 0) {
            $sessionPoolId = (int) (session('active_pool_id', 0));
            if ($sessionPoolId > 0) {
                $poolId = $sessionPoolId;
            }
        }

        $userId ??= (int) (auth()->id() ?? 0);
        $cacheKey = self::requestCacheKey('scope:'.$userId.':'.$poolId.':'.($useSessionPool ? '1' : '0'));
        if (array_key_exists($cacheKey, self::$scopeCache)) {
            return self::$scopeCache[$cacheKey];
        }

        if (! self::tablesReady()) {
            return self::$scopeCache[$cacheKey] = $fallback;
        }

        if ((int) DB::table('pools')->count() === 0) {
            return self::$scopeCache[$cacheKey] = $fallback;
        }

        $isSuperAdmin = AccessControl::userIsSuperAdmin($userId);
        $tenantId = self::tenantId($userId);
        if ($isSuperAdmin && $poolId <= 0) {
            return self::$scopeCache[$cacheKey] = $fallback;
        }

        $poolQuery = DB::table('pools')
            ->where('status', 'active')
            ->orderBy('name');

        // Tenant scoping: non-super-admin users only see pools within their tenant
        if ($tenantId > 0 && Schema::hasColumn('pools', 'tenant_id')) {
            $poolQuery->where('tenant_id', $tenantId);
        }

        if ($poolId > 0) {
            $poolQuery->where('id', $poolId);
        }

        if (! $isSuperAdmin) {
            $userPoolIds = self::userPoolIds($userId);
            if ($userPoolIds === []) {
                return self::$scopeCache[$cacheKey] = self::emptyScope('Belum Ada Pool');
            }

            $poolQuery->whereIn('id', $userPoolIds);
        }

        $pools = $poolQuery->get(['id', 'name', 'target_revenue']);
        $poolIds = $pools->pluck('id')->map(static fn ($value) => (int) $value)->values()->all();

        if ($poolIds === []) {
            return self::$scopeCache[$cacheKey] = self::emptyScope('Pool Tidak Tersedia');
        }

        $routes = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->whereIn('pr.pool_id', $poolIds);

        if ($tenantId > 0 && Schema::hasColumn('routes', 'tenant_id')) {
            $routes->where('r.tenant_id', $tenantId);
        }

        $routes = $routes->get(['r.id', 'r.name', 'r.origin', 'r.destination']);

        $labels = [];
        $routeNames = [];
        foreach ($routes as $route) {
            $routeName = trim((string) ($route->name ?? ''));
            $origin = trim((string) ($route->origin ?? ''));
            $destination = trim((string) ($route->destination ?? ''));

            if ($routeName !== '') {
                $routeNames[] = $routeName;
            }
            if ($origin !== '' && $destination !== '') {
                $routeNames[] = $origin.' - '.$destination;
            }

            foreach (['name', 'origin', 'destination'] as $field) {
                $value = trim((string) ($route->{$field} ?? ''));
                if ($value !== '') {
                    $labels[] = $value;
                }
            }
        }

        $scope = [
            'all' => false,
            'pool_ids' => $poolIds,
            'route_ids' => $routes->pluck('id')->map(static fn ($value) => (int) $value)->unique()->values()->all(),
            'route_names' => collect($routeNames)->unique()->values()->all(),
            'labels' => collect($labels)->unique()->values()->all(),
            'pool_name' => $pools->count() === 1 ? (string) ($pools->first()->name ?? 'Pool') : 'Pool Saya',
            'target_revenue' => (float) $pools->sum('target_revenue'),
            'tenant_id' => $tenantId,
        ];

        return self::$scopeCache[$cacheKey] = $scope;
    }

    public static function cacheKey(int $poolId = 0, ?int $userId = null): string
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $scope = self::forCurrentUser($poolId, $userId);

        if ($scope['all']) {
            return 'all';
        }

        return 'u'.$userId.':p'.md5(json_encode([
            'pool_ids' => $scope['pool_ids'],
            'route_ids' => $scope['route_ids'],
            'route_names' => $scope['route_names'],
        ]) ?: '');
    }

    public static function canAccessRouteName(string $routeName, int $poolId = 0, ?int $userId = null): bool
    {
        $routeName = trim($routeName);
        if ($routeName === '') {
            return false;
        }

        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return true;
        }

        $routeId = self::routeIdForName($routeName);
        if ($routeId > 0 && in_array($routeId, $scope['route_ids'], true)) {
            return true;
        }

        $normalizedRouteName = self::normalizeRouteName($routeName);

        return collect($scope['route_names'])
            ->contains(static fn (string $allowedRoute): bool => self::normalizeRouteName($allowedRoute) === $normalizedRouteName);
    }

    public static function applyRouteScope(Builder $query, string $routeIdColumn = '', string $routeNameColumn = '', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        $routeIds = $scope['route_ids'];
        $routeNames = $scope['route_names'];
        if (($routeIdColumn === '' || $routeIds === []) && ($routeNameColumn === '' || $routeNames === [])) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $builder) use ($routeIdColumn, $routeNameColumn, $routeIds, $routeNames): void {
            self::appendRouteClauses($builder, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames);
        });
    }

    public static function applyPoolOrRouteScope(
        Builder $query,
        string $poolColumn = '',
        string $routeIdColumn = '',
        string $routeNameColumn = '',
        int $poolId = 0,
        ?int $userId = null,
    ): void {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        $poolIds = $scope['pool_ids'];
        $routeIds = $scope['route_ids'];
        $routeNames = $scope['route_names'];

        if (
            ($poolColumn === '' || $poolIds === [])
            && ($routeIdColumn === '' || $routeIds === [])
            && ($routeNameColumn === '' || $routeNames === [])
        ) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $builder) use ($poolColumn, $poolIds, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames): void {
            if ($poolColumn !== '' && $poolIds !== []) {
                $builder->whereIn($poolColumn, $poolIds);

                if (($routeIdColumn !== '' && $routeIds !== []) || ($routeNameColumn !== '' && $routeNames !== [])) {
                    $builder->orWhere(function (Builder $legacy) use ($poolColumn, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames): void {
                        $legacy->whereNull($poolColumn);
                        self::appendRouteClauses($legacy, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames);
                    });
                }

                return;
            }

            self::appendRouteClauses($builder, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames);
        });
    }

    public static function applyCharterScope(Builder $query, string $alias = 'charters', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        $prefix = trim($alias) !== '' ? trim($alias).'.' : '';
        $poolColumn = $prefix.'pool_id';
        $pickupColumn = $prefix.'pickup_point';
        $dropColumn = $prefix.'drop_point';
        $hasPoolColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'pool_id');
        $poolIds = $scope['pool_ids'];
        $labels = $scope['labels'];

        if (($hasPoolColumn && $poolIds !== []) || $labels !== []) {
            $query->where(function (Builder $builder) use ($hasPoolColumn, $poolColumn, $pickupColumn, $dropColumn, $poolIds, $labels): void {
                $hasClause = false;

                if ($hasPoolColumn && $poolIds !== []) {
                    $builder->whereIn($poolColumn, $poolIds);
                    $hasClause = true;
                }

                if ($labels !== []) {
                    if ($hasClause && $hasPoolColumn) {
                        $builder->orWhere(function (Builder $legacy) use ($poolColumn, $pickupColumn, $dropColumn, $labels): void {
                            $legacy
                                ->whereNull($poolColumn)
                                ->where(function (Builder $routeBuilder) use ($pickupColumn, $dropColumn, $labels): void {
                                    $routeBuilder
                                        ->whereIn($pickupColumn, $labels)
                                        ->orWhereIn($dropColumn, $labels);
                                });
                        });
                    } elseif ($hasClause) {
                        $builder->orWhere(function (Builder $routeBuilder) use ($pickupColumn, $dropColumn, $labels): void {
                            $routeBuilder
                                ->whereIn($pickupColumn, $labels)
                                ->orWhereIn($dropColumn, $labels);
                        });
                    } else {
                        $builder
                            ->whereIn($pickupColumn, $labels)
                            ->orWhereIn($dropColumn, $labels);
                    }
                }
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public static function applyCustomerScope(Builder $query, string $customerAlias = 'customers', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'tenant_id')) {
            self::applyTenantScope($query, self::qualifiedColumn($customerAlias, 'tenant_id'), $userId);
        }

        $poolIds = $scope['pool_ids'];
        $routeIds = $scope['route_ids'];
        $routeNames = $scope['route_names'];
        $hasCustomerPoolId = Schema::hasTable('customers') && Schema::hasColumn('customers', 'pool_id');
        $canUseCustomerPool = $hasCustomerPoolId && $poolIds !== [];
        $canUseBookings = Schema::hasTable('bookings') && ($routeIds !== [] || $routeNames !== []);

        if (! $canUseCustomerPool && ! $canUseBookings) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPoolColumn = self::qualifiedColumn($customerAlias, 'pool_id');
        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'phone');
        $query->where(function (Builder $customerScope) use (
            $canUseCustomerPool,
            $canUseBookings,
            $customerPoolColumn,
            $customerPhoneColumn,
            $poolIds,
            $routeIds,
            $routeNames,
        ): void {
            if ($canUseCustomerPool) {
                $customerScope->whereIn($customerPoolColumn, $poolIds);
            }

            if (! $canUseBookings) {
                return;
            }

            $legacyBookingClause = function (Builder $legacy) use (
                $canUseCustomerPool,
                $customerPoolColumn,
                $customerPhoneColumn,
                $routeIds,
                $routeNames,
            ): void {
                if ($canUseCustomerPool) {
                    $legacy->whereNull($customerPoolColumn);
                }

                $legacy->whereExists(function (Builder $exists) use ($customerPhoneColumn, $routeIds, $routeNames): void {
                    $exists
                        ->selectRaw('1')
                        ->from('bookings as scoped_bookings')
                        ->whereColumn('scoped_bookings.phone', $customerPhoneColumn);

                    if (Schema::hasColumn('bookings', 'tenant_id')) {
                        self::applyTenantScope($exists, 'scoped_bookings.tenant_id');
                    }

                    self::appendRouteClauses(
                        $exists,
                        Schema::hasColumn('bookings', 'route_id') ? 'scoped_bookings.route_id' : '',
                        $routeIds,
                        'scoped_bookings.rute',
                        $routeNames,
                    );
                });
            };

            $canUseCustomerPool
                ? $customerScope->orWhere($legacyBookingClause)
                : $customerScope->where($legacyBookingClause);
        });
    }

    public static function applyCustomerBagasiScope(Builder $query, string $customerAlias = 'customer_bagasi', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        if (Schema::hasTable('customer_bagasi') && Schema::hasColumn('customer_bagasi', 'tenant_id')) {
            self::applyTenantScope($query, self::qualifiedColumn($customerAlias, 'tenant_id'), $userId);
        }

        $poolIds = $scope['pool_ids'];
        $hasCustomerPoolId = Schema::hasTable('customer_bagasi') && Schema::hasColumn('customer_bagasi', 'pool_id');
        $canUseCustomerPool = $hasCustomerPoolId && $poolIds !== [];
        $canUseLuggages = Schema::hasTable('luggages');

        if (! $canUseCustomerPool && ! $canUseLuggages) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPoolColumn = self::qualifiedColumn($customerAlias, 'pool_id');
        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'no_hp');
        $query->where(function (Builder $customerScope) use (
            $canUseCustomerPool,
            $canUseLuggages,
            $customerPoolColumn,
            $customerPhoneColumn,
            $poolIds,
            $poolId,
            $userId,
        ): void {
            if ($canUseCustomerPool) {
                $customerScope->whereIn($customerPoolColumn, $poolIds);
            }

            if (! $canUseLuggages) {
                return;
            }

            $legacyLuggageClause = function (Builder $legacy) use (
                $canUseCustomerPool,
                $customerPoolColumn,
                $customerPhoneColumn,
                $poolId,
                $userId,
            ): void {
                if ($canUseCustomerPool) {
                    $legacy->whereNull($customerPoolColumn);
                }

                $legacy->whereExists(function (Builder $exists) use ($customerPhoneColumn, $poolId, $userId): void {
                    $exists
                        ->selectRaw('1')
                        ->from('luggages as scoped_luggages')
                        ->where(function (Builder $phone) use ($customerPhoneColumn): void {
                            $phone
                                ->whereColumn('scoped_luggages.sender_phone', $customerPhoneColumn)
                                ->orWhereColumn('scoped_luggages.receiver_phone', $customerPhoneColumn);
                        });

                    if (Schema::hasColumn('luggages', 'tenant_id')) {
                        self::applyTenantScope($exists, 'scoped_luggages.tenant_id', $userId);
                    }

                    self::applyPoolOrRouteScope(
                        $exists,
                        Schema::hasColumn('luggages', 'pool_id') ? 'scoped_luggages.pool_id' : '',
                        Schema::hasColumn('luggages', 'rute_id') ? 'scoped_luggages.rute_id' : '',
                        'scoped_luggages.rute',
                        $poolId,
                        $userId,
                    );
                });
            };

            $canUseCustomerPool
                ? $customerScope->orWhere($legacyLuggageClause)
                : $customerScope->where($legacyLuggageClause);
        });
    }

    public static function applyCustomerCharterScope(Builder $query, string $customerAlias = 'customer_charter', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        if (Schema::hasTable('customer_charter') && Schema::hasColumn('customer_charter', 'tenant_id')) {
            self::applyTenantScope($query, self::qualifiedColumn($customerAlias, 'tenant_id'), $userId);
        }

        $poolIds = $scope['pool_ids'];
        $hasCustomerPoolId = Schema::hasTable('customer_charter') && Schema::hasColumn('customer_charter', 'pool_id');
        $canUseCustomerPool = $hasCustomerPoolId && $poolIds !== [];
        $canUseCharters = Schema::hasTable('charters');

        if (! $canUseCustomerPool && ! $canUseCharters) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPoolColumn = self::qualifiedColumn($customerAlias, 'pool_id');
        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'no_hp');
        $query->where(function (Builder $customerScope) use (
            $canUseCustomerPool,
            $canUseCharters,
            $customerPoolColumn,
            $customerPhoneColumn,
            $poolIds,
            $poolId,
            $userId,
        ): void {
            if ($canUseCustomerPool) {
                $customerScope->whereIn($customerPoolColumn, $poolIds);
            }

            if (! $canUseCharters) {
                return;
            }

            $legacyCharterClause = function (Builder $legacy) use (
                $canUseCustomerPool,
                $customerPoolColumn,
                $customerPhoneColumn,
                $poolId,
                $userId,
            ): void {
                if ($canUseCustomerPool) {
                    $legacy->whereNull($customerPoolColumn);
                }

                $legacy->whereExists(function (Builder $exists) use ($customerPhoneColumn, $poolId, $userId): void {
                    $exists
                        ->selectRaw('1')
                        ->from('charters as scoped_charters')
                        ->whereColumn('scoped_charters.phone', $customerPhoneColumn);

                    if (Schema::hasColumn('charters', 'tenant_id')) {
                        self::applyTenantScope($exists, 'scoped_charters.tenant_id', $userId);
                    }

                    self::applyCharterScope($exists, 'scoped_charters', $poolId, $userId);
                });
            };

            $canUseCustomerPool
                ? $customerScope->orWhere($legacyCharterClause)
                : $customerScope->where($legacyCharterClause);
        });
    }

    /**
     * @return array<int, int>
     */
    public static function userPoolIds(?int $userId = null): array
    {
        if (! self::tablesReady()) {
            return [];
        }

        $userId ??= (int) (auth()->id() ?? 0);
        if ($userId <= 0) {
            return [];
        }

        $cacheKey = self::requestCacheKey('user-pools:'.$userId);
        if (array_key_exists($cacheKey, self::$userPoolIdsCache)) {
            return self::$userPoolIdsCache[$cacheKey];
        }

        $isSuperAdmin = AccessControl::userIsSuperAdmin($userId);
        $userTenantId = self::userTenantIdFromColumn($userId);

        return self::$userPoolIdsCache[$cacheKey] = DB::table('pool_user as pu')
            ->join('pools as p', 'pu.pool_id', '=', 'p.id')
            ->where('pu.user_id', $userId)
            ->where('p.status', 'active')
            ->when(
                ! $isSuperAdmin
                && Schema::hasColumn('pools', 'tenant_id')
                && $userTenantId > 0,
                function (Builder $query) use ($userTenantId): void {
                    $query->where('p.tenant_id', $userTenantId);
                },
            )
            ->pluck('pu.pool_id')
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    public static function customerPoolId(int $routeId = 0, ?int $userId = null): int
    {
        if (! self::tablesReady()) {
            return 0;
        }

        if ($routeId > 0) {
            $routePoolId = (int) (DB::table('pool_route as pr')
                ->join('pools as p', 'pr.pool_id', '=', 'p.id')
                ->where('pr.route_id', $routeId)
                ->where('p.status', 'active')
                ->value('pr.pool_id') ?? 0);

            if ($routePoolId > 0) {
                return $routePoolId;
            }
        }

        $scope = self::forCurrentUser(0, $userId);
        if (! $scope['all'] && count($scope['pool_ids']) === 1) {
            return (int) $scope['pool_ids'][0];
        }

        $activePoolIds = DB::table('pools')
            ->where('status', 'active')
            ->pluck('id')
            ->map(static fn ($value): int => (int) $value)
            ->values()
            ->all();

        return count($activePoolIds) === 1 ? $activePoolIds[0] : 0;
    }

    public static function currentPoolId(?int $userId = null): int
    {
        return self::customerPoolId(0, $userId);
    }

    /**
     * @return array<int, int>
     */
    public static function accessiblePoolIds(?int $userId = null, bool $useSessionPool = true): array
    {
        if (! Schema::hasTable('pools')) {
            return [];
        }

        $scope = self::forCurrentUser(0, $userId, $useSessionPool);
        if (! ($scope['all'] ?? true)) {
            return array_values(array_map('intval', $scope['pool_ids'] ?? []));
        }

        $tenantId = self::tenantId($userId);
        $query = DB::table('pools')->where('status', 'active')->orderBy('id');
        if ($tenantId > 0 && Schema::hasColumn('pools', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query
            ->pluck('id')
            ->map(static fn ($value): int => (int) $value)
            ->values()
            ->all();
    }

    public static function canAccessPool(int $poolId, ?int $userId = null): bool
    {
        if ($poolId <= 0 || ! Schema::hasTable('pools')) {
            return false;
        }

        $query = DB::table('pools')
            ->where('id', $poolId)
            ->where('status', 'active');

        $tenantId = self::tenantId($userId);
        if ($tenantId > 0 && Schema::hasColumn('pools', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if (! $query->exists()) {
            return false;
        }

        $scope = self::forCurrentUser(0, $userId, false);
        if ($scope['all'] ?? true) {
            return true;
        }

        return in_array($poolId, array_map('intval', $scope['pool_ids'] ?? []), true);
    }

    public static function defaultWritablePoolId(?int $userId = null): int
    {
        $poolId = self::currentPoolId($userId);
        if ($poolId > 0) {
            return $poolId;
        }

        if (! Schema::hasTable('pools')) {
            return 0;
        }

        $tenantId = self::tenantId($userId);
        if ($tenantId > 0 && Schema::hasColumn('pools', 'tenant_id')) {
            $poolId = (int) (DB::table('pools')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->orderBy('id')
                ->value('id') ?? 0);
            if ($poolId > 0) {
                return $poolId;
            }

            $poolId = (int) (DB::table('pools')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->value('id') ?? 0);
            if ($poolId > 0) {
                return $poolId;
            }
        }

        return (int) (DB::table('pools')
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    public static function routeIdForName(string $routeName): int
    {
        $target = self::normalizeRouteName($routeName);
        if ($target === '' || ! Schema::hasTable('routes')) {
            return 0;
        }

        $exactMatches = [];
        $aliasMatches = [];

        $tenantId = self::tenantId();
        $query = DB::table('routes');
        if ($tenantId > 0 && Schema::hasColumn('routes', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        foreach ($query->get(['id', 'name', 'origin', 'destination']) as $route) {
            $routeId = (int) ($route->id ?? 0);
            if ($routeId <= 0) {
                continue;
            }

            if (self::normalizeRouteName((string) ($route->name ?? '')) === $target) {
                $exactMatches[] = $routeId;
            }

            $origin = trim((string) ($route->origin ?? ''));
            $destination = trim((string) ($route->destination ?? ''));

            if ($origin !== '' && $destination !== '' && self::normalizeRouteName($origin.' - '.$destination) === $target) {
                $aliasMatches[] = $routeId;
            }
        }

        $exactMatches = array_values(array_unique($exactMatches));
        if (count($exactMatches) === 1) {
            return $exactMatches[0];
        }
        if ($exactMatches !== []) {
            return 0;
        }

        $aliasMatches = array_values(array_unique($aliasMatches));

        return count($aliasMatches) === 1 ? $aliasMatches[0] : 0;
    }

    public static function applyRouteIdentity(
        Builder $query,
        string $routeName,
        string $routeIdColumn = '',
        string $routeNameColumn = 'rute',
        int $routeId = 0,
    ): void {
        $routeId = $routeId > 0 ? $routeId : self::routeIdForName($routeName);
        $normalizedRouteName = self::normalizeRouteName($routeName);

        if (($routeIdColumn === '' || $routeId <= 0) && ($routeNameColumn === '' || $normalizedRouteName === '')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $builder) use ($routeIdColumn, $routeId, $routeNameColumn, $normalizedRouteName): void {
            $hasRouteIdClause = $routeIdColumn !== '' && $routeId > 0;
            if ($hasRouteIdClause) {
                $builder->where($routeIdColumn, $routeId);
            }

            if ($routeNameColumn === '' || $normalizedRouteName === '') {
                return;
            }

            $legacyNameClause = function (Builder $legacy) use ($hasRouteIdClause, $routeIdColumn, $routeNameColumn, $normalizedRouteName): void {
                if ($hasRouteIdClause) {
                    $legacy->whereNull($routeIdColumn);
                }

                $legacy->whereRaw(self::normalizedRouteSql($routeNameColumn).' = ?', [$normalizedRouteName]);
            };

            $hasRouteIdClause
                ? $builder->orWhere($legacyNameClause)
                : $builder->where($legacyNameClause);
        });
    }

    public static function normalizeRouteName(string $routeName): string
    {
        $normalized = mb_strtoupper(trim($routeName));
        $normalized = str_replace(['=>', '->', '→', '–', '—'], '-', $normalized);

        return preg_replace('/\s+/', '', $normalized) ?? $normalized;
    }

    /**
     * @param  array<int, int>  $routeIds
     * @param  array<int, string>  $routeNames
     */
    private static function appendRouteClauses(Builder $builder, string $routeIdColumn, array $routeIds, string $routeNameColumn, array $routeNames): void
    {
        $builder->where(function (Builder $routeBuilder) use ($routeIdColumn, $routeIds, $routeNameColumn, $routeNames): void {
            $hasClause = false;

            if ($routeIdColumn !== '' && $routeIds !== []) {
                $routeBuilder->whereIn($routeIdColumn, $routeIds);
                $hasClause = true;
            }

            if ($routeNameColumn !== '' && $routeNames !== []) {
                $normalizedRouteNames = collect($routeNames)
                    ->map(static fn (string $routeName): string => self::normalizeRouteName($routeName))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $nameClause = function (Builder $nameBuilder) use ($hasClause, $routeIdColumn, $routeNameColumn, $routeNames, $normalizedRouteNames): void {
                    if ($hasClause) {
                        $nameBuilder->whereNull($routeIdColumn);
                    }

                    $nameBuilder->where(function (Builder $legacyName) use ($routeNameColumn, $routeNames, $normalizedRouteNames): void {
                        $legacyName->whereIn($routeNameColumn, $routeNames);

                        if ($normalizedRouteNames !== []) {
                            $placeholders = implode(', ', array_fill(0, count($normalizedRouteNames), '?'));
                            $legacyName->orWhereRaw(self::normalizedRouteSql($routeNameColumn)." IN ({$placeholders})", $normalizedRouteNames);
                        }
                    });
                };

                $hasClause
                    ? $routeBuilder->orWhere($nameClause)
                    : $routeBuilder->where($nameClause);
            }
        });
    }

    private static function normalizedRouteSql(string $column): string
    {
        return "UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), '=>', '-'), '->', '-'), '→', '-'), '–', '-'), '—', '-'), ' ', ''))";
    }

    /**
     * @return array{all: false, pool_ids: array<int, int>, route_ids: array<int, int>, route_names: array<int, string>, labels: array<int, string>, pool_name: string, target_revenue: float, tenant_id: int}
     */
    private static function emptyScope(string $poolName): array
    {
        return [
            'all' => false,
            'pool_ids' => [],
            'route_ids' => [],
            'route_names' => [],
            'labels' => [],
            'pool_name' => $poolName,
            'target_revenue' => 0.0,
            'tenant_id' => 0,
        ];
    }

    private static function userTenantIdFromColumn(int $userId): int
    {
        $cacheKey = self::requestCacheKey('user-tenant-column:'.$userId);
        if (array_key_exists($cacheKey, self::$userTenantColumnCache)) {
            return self::$userTenantColumnCache[$cacheKey];
        }

        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'tenant_id')) {
            return self::$userTenantColumnCache[$cacheKey] = 0;
        }

        return self::$userTenantColumnCache[$cacheKey] = (int) (DB::table('users')->where('id', $userId)->value('tenant_id') ?? 0);
    }

    private static function requestCacheKey(string $key): string
    {
        $requestId = 'cli';

        try {
            if (app()->bound('request')) {
                $requestId = (string) spl_object_id(app('request'));
            }
        } catch (\Throwable) {
            $requestId = 'cli';
        }

        return $requestId.':'.$key;
    }

    private static function qualifiedColumn(string $alias, string $column): string
    {
        $alias = trim($alias);

        return $alias !== '' ? $alias.'.'.$column : $column;
    }
}
