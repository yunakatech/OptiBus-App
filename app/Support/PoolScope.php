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
        foreach ($routes as $route) {
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
            'route_names' => $routes->pluck('name')->map(static fn ($value) => (string) $value)->filter(static fn ($value) => trim($value) !== '')->unique()->values()->all(),
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

        return in_array($routeName, $scope['route_names'], true);
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

        if (! Schema::hasTable('bookings')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerPhoneColumn = self::qualifiedColumn($customerAlias, 'phone');
        $routeIds = $scope['route_ids'];
        $routeNames = $scope['route_names'];

        if ($routeNames === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereExists(function (Builder $exists) use ($customerPhoneColumn, $routeIds, $routeNames): void {
            $exists
                ->selectRaw('1')
                ->from('bookings as scoped_bookings')
                ->whereColumn('scoped_bookings.phone', $customerPhoneColumn);

            self::appendRouteClauses($exists, '', $routeIds, 'scoped_bookings.rute', $routeNames);
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
                $hasClause
                    ? $routeBuilder->orWhereIn($routeNameColumn, $routeNames)
                    : $routeBuilder->whereIn($routeNameColumn, $routeNames);
            }
        });
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
