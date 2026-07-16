<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AdminOpsApiController;
use App\Http\Controllers\Api\OperationsApiController;
use App\Support\AccessControl;
use App\Support\ActivityLog;
use App\Support\DeferredInertia;
use App\Support\PoolScope;
use App\Support\RoleAccessData;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsController extends Controller
{
    public function __construct(
        private readonly RoleAccessData $roleAccessData,
        private readonly AdminOpsApiController $adminOpsApi,
        private readonly OperationsApiController $operationsApi,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lockedMenuView = (bool) ($request->route('locked') ?? false);

        if (! $lockedMenuView) {
            $legacyRoutes = [
                'routes' => 'admin-ops.routes',
                'schedules' => 'admin-ops.schedules',
                'drivers' => 'admin-ops.drivers',
                'services' => 'admin-ops.services',
                'segments' => 'admin-ops.segments',
                'customers' => 'admin-ops.customers',
                'units' => 'admin-ops.units',
                'armadas' => 'admin-ops.armadas',
                'pools' => 'admin-ops.pools',
                'users' => 'admin-ops.users',
                'roles' => 'admin-ops.roles',
                'cancellations' => 'admin-ops.cancellations',
                'reports' => 'report.index',
            ];

            $legacyTab = trim((string) $request->query('tab', ''));
            if ($legacyTab !== '' && isset($legacyRoutes[$legacyTab])) {
                $query = $request->query();
                unset($query['tab']);

                return redirect()->to(route($legacyRoutes[$legacyTab], $query));
            }
        }

        $allowedTabs = ['routes', 'schedules', 'drivers', 'services', 'segments', 'customers', 'units', 'armadas', 'pools', 'users', 'roles', 'cancellations', 'reports'];
        $requestedTab = (string) ($request->route('tab') ?? '');
        $initialTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : null;
        $initialMode = trim((string) ($request->route('mode') ?? ''));
        $hybridTabs = ['schedules', 'drivers', 'segments', 'units', 'armadas', 'pools', 'users'];
        $usesHybridInertia = $lockedMenuView
            && DeferredInertia::opsEnabled()
            && in_array($initialTab, $hybridTabs, true)
            && ! ($initialTab === 'units' && $initialMode === 'layout');

        if ($initialTab === 'roles' && ! AccessControl::userIsSuperAdmin((int) ($request->user()?->id ?? 0))) {
            abort(403, 'Hanya Super Admin yang bisa mengakses halaman role.');
        }

        $recordId = (int) ($request->route('id') ?? 0);

        $stats = [
            'routes' => 0,
            'schedules' => 0,
            'drivers' => 0,
            'luggage_services' => 0,
            'segments' => 0,
            'customers' => 0,
            'armadas' => 0,
            'pools' => 0,
            'cancellations' => 0,
        ];

        if (! $lockedMenuView) {
            $stats = $this->scopedStats();
        }

        $component = 'AdminOps';
        if ($lockedMenuView) {
            $componentMap = [
                'routes' => 'PengaturanRuteReguler',
                'schedules' => 'PengaturanJadwal',
                'drivers' => 'PengaturanDriver',
                'services' => 'PengaturanBagasi',
                'segments' => 'PengaturanSegment',
                'customers' => 'CustomerReguler',
                'units' => 'PengaturanKategoriArmada',
                'armadas' => 'PengaturanArmada',
                'pools' => 'PengaturanPool',
                'users' => 'PengaturanUsers',
                'roles' => 'PengaturanRoles',
                'cancellations' => 'PengaturanLogs',
            ];
            if (is_string($initialTab) && isset($componentMap[$initialTab])) {
                $component = $componentMap[$initialTab];
            }
        }

        return Inertia::render($component, [
            'stats' => $stats,
            'initialTab' => $initialTab,
            'lockedMenuView' => $lockedMenuView,
            'initialMode' => $initialMode !== '' ? $initialMode : null,
            'initialRecordId' => $recordId > 0 ? $recordId : null,
            'roleQuery' => $initialTab === 'roles' ? trim((string) $request->query('q', '')) : '',
            'roleData' => $initialTab === 'roles'
                ? Inertia::defer(fn (): array => $this->roleAccessData->roles($request), 'role-data')
                : null,
            'rolePermissions' => $initialTab === 'roles'
                ? Inertia::defer(fn (): array => $this->roleAccessData->permissions(), 'role-permissions')
                : null,
            'settingsQuery' => $usesHybridInertia
                ? [
                    'q' => trim((string) $request->query('q', '')),
                    'page' => max(1, (int) $request->query('page', 1)),
                    'per_page' => max(10, min(100, (int) $request->query('per_page', 20))),
                    'route_id' => max(0, (int) $request->query('route_id', 0)),
                    'rute' => trim((string) $request->query('rute', '')),
                    'pool_id' => max(0, (int) $request->query('pool_id', 0)),
                    'period' => trim((string) $request->query('period', '')),
                ]
                : null,
            'deferredSettingsEnabled' => $usesHybridInertia,
            'settingsData' => $usesHybridInertia
                ? Inertia::defer(fn (): array => $this->settingsData($request, (string) $initialTab), 'settings-data')
                : null,
            'settingsMasters' => $usesHybridInertia
                ? Inertia::defer(fn (): array => $this->settingsMasters($request, (string) $initialTab), 'settings-masters')
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsData(Request $request, string $tab): array
    {
        try {
            $listRequest = clone $request;
            $listRequest->query->set('paginate', '1');
            $routeOptions = [];
            $segmentRequest = null;

            if ($tab === 'schedules') {
                $routeOptions = $this->routeOptions();
                $routeId = max(0, (int) $listRequest->query('route_id', 0));
                $rute = trim((string) $listRequest->query('rute', ''));

                if ($routeId <= 0 && $rute !== '') {
                    foreach ($routeOptions as $routeOption) {
                        if (trim((string) ($routeOption['name'] ?? '')) === $rute) {
                            $routeId = (int) ($routeOption['id'] ?? 0);
                            break;
                        }
                    }
                }

                if ($routeId <= 0) {
                    $routeId = (int) ($routeOptions[0]['id'] ?? 0);
                }

                if ($routeId > 0) {
                    $listRequest->query->set('route_id', $routeId);
                    foreach ($routeOptions as $routeOption) {
                        if ((int) ($routeOption['id'] ?? 0) === $routeId) {
                            $listRequest->query->set('rute', (string) ($routeOption['name'] ?? ''));
                            break;
                        }
                    }
                } elseif ($rute === '') {
                    $listRequest->query->set('rute', (string) ($routeOptions[0]['name'] ?? ''));
                }

                $segmentRequest = clone $listRequest;
                $segmentRequest->query->remove('paginate');
            }

            if ($tab === 'segments' && (int) $listRequest->query('route_id', 0) <= 0) {
                $routeOptions = $routeOptions !== [] ? $routeOptions : $this->routeOptions();
                $listRequest->query->set('route_id', (int) ($routeOptions[0]['id'] ?? 0));
            }

            $payload = match ($tab) {
                'schedules' => $this->payload($this->adminOpsApi->schedulesIndex($listRequest)),
                'drivers' => $this->payload($this->adminOpsApi->driversIndex($listRequest)),
                'segments' => $this->payload($this->adminOpsApi->segmentsIndex($listRequest)),
                'units' => $this->payload($this->adminOpsApi->unitsIndex()),
                'armadas' => $this->payload($this->adminOpsApi->armadasIndex($listRequest)),
                'pools' => $this->payload($this->adminOpsApi->poolsIndex($listRequest)),
                'users' => $this->payload($this->adminOpsApi->usersIndex($listRequest)),
                default => [],
            };

            $listKey = match ($tab) {
                'schedules' => 'schedules',
                'drivers' => 'drivers',
                'segments' => 'segments',
                'units' => 'units',
                'armadas' => 'armadas',
                'pools' => 'pools',
                'users' => 'users',
                default => '',
            };

            if ($listKey === '') {
                return ['tab' => $tab];
            }

            return [
                'tab' => $tab,
                $listKey => $payload[$listKey] ?? [],
                'pagination' => $payload['pagination'] ?? [
                    'page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                    'last_page' => 1,
                ],
                ...($tab === 'pools' ? ['can_manage' => (bool) ($payload['can_manage'] ?? true)] : []),
                ...($tab === 'schedules' ? [
                    'route_id' => max(0, (int) $listRequest->query('route_id', 0)),
                    'rute' => trim((string) $listRequest->query('rute', '')),
                    'segments' => $this->payload($this->adminOpsApi->segmentsIndex($segmentRequest ?? $listRequest))['segments'] ?? [],
                ] : []),
                ...($tab === 'segments' ? ['route_id' => max(0, (int) $listRequest->query('route_id', 0))] : []),
            ];
        } catch (Throwable $exception) {
            if (! app()->environment('testing') && ! $exception instanceof QueryException) {
                report($exception);
            }

            return match ($tab) {
                'schedules' => [
                    'tab' => $tab,
                    'schedules' => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                    'route_id' => max(0, (int) $request->query('route_id', 0)),
                    'rute' => trim((string) $request->query('rute', '')),
                    'segments' => [],
                ],
                'segments' => [
                    'tab' => $tab,
                    'segments' => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                    'route_id' => max(0, (int) $request->query('route_id', 0)),
                ],
                'drivers' => ['tab' => $tab, 'drivers' => [], 'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1]],
                'units' => ['tab' => $tab, 'units' => [], 'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1]],
                'armadas' => ['tab' => $tab, 'armadas' => [], 'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1]],
                'pools' => ['tab' => $tab, 'pools' => [], 'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1], 'can_manage' => true],
                'users' => ['tab' => $tab, 'users' => [], 'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1]],
                default => ['tab' => $tab],
            };
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsMasters(Request $request, string $tab): array
    {
        try {
            $masterRequest = clone $request;
            $masterRequest->query->replace([]);

            $routes = fn (): array => $this->routeOptions();
            $units = fn (): array => $this->payload($this->adminOpsApi->unitsIndex())['units'] ?? [];
            $armadas = fn (): array => $this->payload($this->operationsApi->armadas($masterRequest))['armadas'] ?? [];
            $pools = fn (): array => $this->payload($this->adminOpsApi->poolOptionsIndex($masterRequest))['pools'] ?? [];

            return match ($tab) {
                'schedules' => ['tab' => $tab, 'routes' => $routes(), 'units' => $units()],
                'drivers' => ['tab' => $tab, 'armadas' => $armadas(), 'pools' => $pools()],
                'segments' => ['tab' => $tab, 'routes' => $routes()],
                'units' => ['tab' => $tab, 'pools' => $pools()],
                'armadas' => [
                    'tab' => $tab,
                    'categories' => $this->payload($this->adminOpsApi->armadaCategoriesIndex())['categories'] ?? [],
                    'units' => $units(),
                    'pools' => $pools(),
                ],
                'pools' => $this->poolMasters($masterRequest, $tab, false),
                'users' => $this->poolMasters($masterRequest, $tab, true),
                default => ['tab' => $tab],
            };
        } catch (Throwable $exception) {
            if (! app()->environment('testing') && ! $exception instanceof QueryException) {
                report($exception);
            }

            return match ($tab) {
                'schedules' => ['tab' => $tab, 'routes' => [], 'units' => []],
                'drivers' => ['tab' => $tab, 'armadas' => [], 'pools' => []],
                'segments' => ['tab' => $tab, 'routes' => []],
                'units' => ['tab' => $tab, 'pools' => []],
                'armadas' => ['tab' => $tab, 'categories' => [], 'units' => [], 'pools' => []],
                'pools' => ['tab' => $tab, 'routes' => []],
                'users' => ['tab' => $tab, 'pools' => [], 'roles' => [], 'can_manage_pools' => true],
                default => ['tab' => $tab],
            };
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function poolMasters(Request $request, string $tab, bool $includePools): array
    {
        $payload = $this->payload($this->adminOpsApi->poolOptionsIndex($request));

        if (! $includePools) {
            return ['tab' => $tab, 'routes' => $payload['routes'] ?? []];
        }

        return [
            'tab' => $tab,
            'pools' => $payload['pools'] ?? [],
            'roles' => AccessControl::rolesForSelect(),
            'can_manage_pools' => (bool) ($payload['can_manage'] ?? true),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function routeOptions(): array
    {
        if (! Schema::hasTable('routes')) {
            return [];
        }

        $query = DB::table('routes')->orderBy('name');
        PoolScope::applyRouteScope($query, 'routes.id', 'routes.name');
        $this->applyTenantScopeIfExists($query, 'routes');

        return $query
            ->get(['id', 'name', 'origin', 'destination'])
            ->map(static fn ($route): array => [
                'id' => (int) $route->id,
                'name' => (string) $route->name,
                'origin' => $route->origin,
                'destination' => $route->destination,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function scopedStats(): array
    {
        return [
            'routes' => $this->countScoped('routes', function (Builder $query): void {
                PoolScope::applyRouteScope($query, 'routes.id', 'routes.name');
                $this->applyTenantScopeIfExists($query, 'routes');
            }),
            'schedules' => $this->countScoped('schedules', function (Builder $query): void {
                PoolScope::applyRouteScope(
                    $query,
                    Schema::hasColumn('schedules', 'route_id') ? 'schedules.route_id' : '',
                    'schedules.rute',
                );
                $this->applyTenantScopeIfExists($query, 'schedules');
            }),
            'drivers' => $this->countScoped('drivers', function (Builder $query): void {
                $this->applyTenantScopeIfExists($query, 'drivers');
                $this->applyPoolScopeIfExists($query, 'drivers');
            }),
            'luggage_services' => $this->countScoped('luggage_services', function (Builder $query): void {
                $this->applyTenantScopeIfExists($query, 'luggage_services');
                $this->applyPoolScopeIfExists($query, 'luggage_services');
            }),
            'segments' => $this->countScoped('segments', function (Builder $query): void {
                PoolScope::applyRouteScope($query, 'segments.route_id', 'segments.rute');
                $this->applyTenantScopeIfExists($query, 'segments');
            }),
            'customers' => $this->countScoped('customers', function (Builder $query): void {
                PoolScope::applyCustomerScope($query, 'customers');
                $this->applyTenantScopeIfExists($query, 'customers');
            }),
            'armadas' => $this->countScoped('armadas', function (Builder $query): void {
                $this->applyTenantScopeIfExists($query, 'armadas');
                $this->applyPoolScopeIfExists($query, 'armadas');
            }),
            'pools' => $this->countScoped('pools', function (Builder $query): void {
                $this->applyTenantScopeIfExists($query, 'pools');
                if (! AccessControl::userIsSuperAdmin((int) (auth()->id() ?? 0)) && PoolScope::tablesReady()) {
                    $poolIds = PoolScope::userPoolIds();
                    $poolIds === []
                        ? $query->whereRaw('1 = 0')
                        : $query->whereIn('pools.id', $poolIds);
                }
            }),
            'cancellations' => ActivityLog::countForTenant(),
        ];
    }

    private function countScoped(string $table, callable $scope): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);
        $scope($query);

        return (int) $query->count();
    }

    private function applyTenantScopeIfExists(Builder $query, string $table): void
    {
        if (! Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        PoolScope::applyTenantScope($query, $table.'.tenant_id');
    }

    private function applyPoolScopeIfExists(Builder $query, string $table): void
    {
        if (! Schema::hasColumn($table, 'pool_id')) {
            return;
        }

        $poolId = PoolScope::currentPoolId(auth()->id());
        if ($poolId > 0) {
            $query->where($table.'.pool_id', $poolId);

            return;
        }

        $scope = PoolScope::forCurrentUser(0, auth()->id());
        if ($scope['all'] ?? true) {
            return;
        }

        $poolIds = array_values(array_map('intval', $scope['pool_ids'] ?? []));
        if ($poolIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($table.'.pool_id', $poolIds);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(JsonResponse $response): array
    {
        return (array) $response->getData(true);
    }
}
