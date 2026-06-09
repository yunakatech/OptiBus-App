<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AdminOpsApiController;
use App\Http\Controllers\Api\OperationsApiController;
use App\Support\AccessControl;
use App\Support\ActivityLog;
use App\Support\PoolScope;
use App\Support\RoleAccessData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $hybridTabs = ['schedules', 'drivers', 'segments', 'armadas', 'pools', 'users'];
        $usesHybridInertia = $lockedMenuView && in_array($initialTab, $hybridTabs, true);

        if ($initialTab === 'roles' && ! AccessControl::userIsSuperAdmin((int) ($request->user()?->id ?? 0))) {
            abort(403, 'Hanya Super Admin yang bisa mengakses halaman role.');
        }

        $initialMode = trim((string) ($request->route('mode') ?? ''));
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
            $stats = [
                'routes' => Schema::hasTable('routes') ? DB::table('routes')->count() : 0,
                'schedules' => Schema::hasTable('schedules') ? DB::table('schedules')->count() : 0,
                'drivers' => Schema::hasTable('drivers') ? DB::table('drivers')->count() : 0,
                'luggage_services' => Schema::hasTable('luggage_services') ? DB::table('luggage_services')->count() : 0,
                'segments' => Schema::hasTable('segments') ? DB::table('segments')->count() : 0,
                'customers' => Schema::hasTable('customers') ? DB::table('customers')->count() : 0,
                'armadas' => Schema::hasTable('armadas') ? DB::table('armadas')->count() : 0,
                'pools' => Schema::hasTable('pools') ? DB::table('pools')->count() : 0,
                'cancellations' => ActivityLog::count(),
            ];
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
                ]
                : null,
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
        $listRequest = clone $request;
        $listRequest->query->set('paginate', '1');
        $routeOptions = [];

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
        }

        if ($tab === 'segments' && (int) $listRequest->query('route_id', 0) <= 0) {
            $routeOptions = $routeOptions !== [] ? $routeOptions : $this->routeOptions();
            $listRequest->query->set('route_id', (int) ($routeOptions[0]['id'] ?? 0));
        }

        $payload = match ($tab) {
            'schedules' => $this->payload($this->adminOpsApi->schedulesIndex($listRequest)),
            'drivers' => $this->payload($this->adminOpsApi->driversIndex($listRequest)),
            'segments' => $this->payload($this->adminOpsApi->segmentsIndex($listRequest)),
            'armadas' => $this->payload($this->adminOpsApi->armadasIndex($listRequest)),
            'pools' => $this->payload($this->adminOpsApi->poolsIndex($listRequest)),
            'users' => $this->payload($this->adminOpsApi->usersIndex($listRequest)),
            default => [],
        };

        $listKey = match ($tab) {
            'schedules' => 'schedules',
            'drivers' => 'drivers',
            'segments' => 'segments',
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
            ] : []),
            ...($tab === 'segments' ? ['route_id' => max(0, (int) $listRequest->query('route_id', 0))] : []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsMasters(Request $request, string $tab): array
    {
        $masterRequest = clone $request;
        $masterRequest->query->replace([]);

        $routes = fn (): array => $this->routeOptions();
        $units = fn (): array => $this->payload($this->adminOpsApi->unitsIndex())['units'] ?? [];
        $armadas = fn (): array => $this->payload($this->operationsApi->armadas($masterRequest))['armadas'] ?? [];

        return match ($tab) {
            'schedules' => ['tab' => $tab, 'routes' => $routes(), 'units' => $units()],
            'drivers' => ['tab' => $tab, 'armadas' => $armadas()],
            'segments' => ['tab' => $tab, 'routes' => $routes()],
            'armadas' => [
                'tab' => $tab,
                'categories' => $this->payload($this->adminOpsApi->armadaCategoriesIndex())['categories'] ?? [],
                'units' => $units(),
            ],
            'pools' => $this->poolMasters($masterRequest, $tab, false),
            'users' => $this->poolMasters($masterRequest, $tab, true),
            default => ['tab' => $tab],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function poolMasters(Request $request, string $tab, bool $includePools): array
    {
        $payload = $this->payload($this->adminOpsApi->poolsIndex($request));

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
     * @return array<string, mixed>
     */
    private function payload(JsonResponse $response): array
    {
        return (array) $response->getData(true);
    }
}
