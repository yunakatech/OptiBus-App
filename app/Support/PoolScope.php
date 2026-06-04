<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PoolScope
{
    public static function tablesReady(): bool
    {
        return Schema::hasTable('pools')
            && Schema::hasTable('pool_route')
            && Schema::hasTable('pool_user')
            && Schema::hasTable('routes');
    }

    /**
     * @return array{all: bool, pool_ids: array<int, int>, route_ids: array<int, int>, route_names: array<int, string>, labels: array<int, string>, pool_name: string, target_revenue: float}
     */
    public static function forCurrentUser(int $poolId = 0, ?int $userId = null): array
    {
        $fallback = [
            'all' => true,
            'pool_ids' => [],
            'route_ids' => [],
            'route_names' => [],
            'labels' => [],
            'pool_name' => 'Semua Pool',
            'target_revenue' => 0.0,
        ];

        if (! self::tablesReady()) {
            return $fallback;
        }

        if ((int) DB::table('pools')->count() === 0) {
            return $fallback;
        }

        $userId ??= (int) (auth()->id() ?? 0);
        $isSuperAdmin = AccessControl::userIsSuperAdmin($userId);
        if ($isSuperAdmin && $poolId <= 0) {
            return $fallback;
        }

        $poolQuery = DB::table('pools')
            ->where('status', 'active')
            ->orderBy('name');

        if ($poolId > 0) {
            $poolQuery->where('id', $poolId);
        }

        if (! $isSuperAdmin) {
            $userPoolIds = self::userPoolIds($userId);
            if ($userPoolIds === []) {
                return self::emptyScope('Belum Ada Pool');
            }

            $poolQuery->whereIn('id', $userPoolIds);
        }

        $pools = $poolQuery->get(['id', 'name', 'target_revenue']);
        $poolIds = $pools->pluck('id')->map(static fn ($value) => (int) $value)->values()->all();

        if ($poolIds === []) {
            return self::emptyScope('Pool Tidak Tersedia');
        }

        $routes = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->whereIn('pr.pool_id', $poolIds)
            ->get(['r.id', 'r.name', 'r.origin', 'r.destination']);

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

        return [
            'all' => false,
            'pool_ids' => $poolIds,
            'route_ids' => $routes->pluck('id')->map(static fn ($value) => (int) $value)->unique()->values()->all(),
            'route_names' => collect($routeNames)->unique()->values()->all(),
            'labels' => collect($labels)->unique()->values()->all(),
            'pool_name' => $pools->count() === 1 ? (string) ($pools->first()->name ?? 'Pool') : 'Pool Saya',
            'target_revenue' => (float) $pools->sum('target_revenue'),
        ];
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

        if (! Schema::hasTable('luggages')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'no_hp');

        $query->whereExists(function (Builder $exists) use ($customerPhoneColumn, $poolId, $userId): void {
            $exists
                ->selectRaw('1')
                ->from('luggages as scoped_luggages')
                ->where(function (Builder $phone) use ($customerPhoneColumn): void {
                    $phone
                        ->whereColumn('scoped_luggages.sender_phone', $customerPhoneColumn)
                        ->orWhereColumn('scoped_luggages.receiver_phone', $customerPhoneColumn);
                });

            self::applyPoolOrRouteScope(
                $exists,
                Schema::hasColumn('luggages', 'pool_id') ? 'scoped_luggages.pool_id' : '',
                Schema::hasColumn('luggages', 'rute_id') ? 'scoped_luggages.rute_id' : '',
                'scoped_luggages.rute',
                $poolId,
                $userId,
            );
        });
    }

    public static function applyCustomerCharterScope(Builder $query, string $customerAlias = 'customer_charter', int $poolId = 0, ?int $userId = null): void
    {
        $scope = self::forCurrentUser($poolId, $userId);
        if ($scope['all']) {
            return;
        }

        if (! Schema::hasTable('charters')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'no_hp');

        $query->whereExists(function (Builder $exists) use ($customerPhoneColumn, $poolId, $userId): void {
            $exists
                ->selectRaw('1')
                ->from('charters as scoped_charters')
                ->whereColumn('scoped_charters.phone', $customerPhoneColumn);

            self::applyCharterScope($exists, 'scoped_charters', $poolId, $userId);
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

        return DB::table('pool_user as pu')
            ->join('pools as p', 'pu.pool_id', '=', 'p.id')
            ->where('pu.user_id', $userId)
            ->where('p.status', 'active')
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

    public static function routeIdForName(string $routeName): int
    {
        $target = self::normalizeRouteName($routeName);
        if ($target === '' || ! Schema::hasTable('routes')) {
            return 0;
        }

        $exactMatches = [];
        $aliasMatches = [];

        foreach (DB::table('routes')->get(['id', 'name', 'origin', 'destination']) as $route) {
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
     * @param array<int, int> $routeIds
     * @param array<int, string> $routeNames
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
     * @return array{all: false, pool_ids: array<int, int>, route_ids: array<int, int>, route_names: array<int, string>, labels: array<int, string>, pool_name: string, target_revenue: float}
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
        ];
    }

    private static function qualifiedColumn(string $alias, string $column): string
    {
        $alias = trim($alias);

        return $alias !== '' ? $alias.'.'.$column : $column;
    }
}
