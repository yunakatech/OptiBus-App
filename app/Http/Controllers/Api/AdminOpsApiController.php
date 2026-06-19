<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentGateway;
use App\Services\TenantProvisioningService;
use App\Support\AccessControl;
use App\Support\ActivityLog;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use App\Support\RoleAccessData;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOpsApiController extends Controller
{
    public function __construct(
        private readonly RoleAccessData $roleAccessData,
    ) {}

    private ?bool $schedulesHasRouteId = null;

    private ?bool $schedulesHasSeatsColumn = null;

    private ?bool $schedulesHasBopColumn = null;

    private ?bool $scheduleUnitsTableExists = null;

    private ?bool $chartersHasStatusColumn = null;

    private ?bool $chartersHasArmadaIdColumn = null;

    private ?bool $chartersHasArmadaNopolColumn = null;

    private ?bool $chartersHasPoolIdColumn = null;

    private ?bool $chartersHasMasterCarterIdColumn = null;

    private ?bool $luggagesHasPoolIdColumn = null;

    private ?bool $tripAssignmentsHasArmadaId = null;

    private ?bool $tripAssignmentsHasArmadaNopol = null;

    private ?bool $tripAssignmentsHasStatus = null;

    private ?bool $driversHasArmadaId = null;

    private ?bool $driversHasArmadaNopol = null;

    private ?bool $routesHasBopColumn = null;

    private ?bool $routesHasTargetRevenueColumn = null;

    private ?bool $routesHasFixedCostColumn = null;

    private ?bool $poolsHasFixedCostColumn = null;

    private ?bool $driversHasRevenueColumn = null;

    private ?bool $driversHasBopColumn = null;

    private ?bool $driversHasFixedCostColumn = null;

    private ?bool $driversHasTargetRevenueBulananColumn = null;

    private ?bool $driversHasTargetRevenueTahunanColumn = null;

    public function routesIndex(): JsonResponse
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $financials = $this->routeFinancialsForMonth($monthStart, $monthEnd);

        $query = DB::table('routes')
            ->orderBy('name');
        $this->applyRouteScopeToQuery($query, 'routes.id', 'routes.name');
        $this->applyTenantScopeIfExists($query, 'routes');

        $rows = $query->get([
            'id',
            'name',
            'origin',
            'destination',
            $this->hasRoutesBopColumn() ? 'bop' : DB::raw('0 as bop'),
            $this->hasRoutesTargetRevenueColumn() ? 'target_revenue' : DB::raw('0 as target_revenue'),
            $this->hasRoutesFixedCostColumn() ? 'fixed_cost' : DB::raw('0 as fixed_cost'),
        ])
            ->map(fn ($row) => $this->withRouteFinancials($row, $financials))
            ->values();

        return $this->ok(['routes' => $rows]);
    }

    public function routesSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:120'],
            'bop' => ['nullable', 'numeric', 'min:0'],
            'target_revenue' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);

        // Plan limit enforcement: only check when creating NEW routes
        if ($id <= 0 && FeatureGate::enabled() && Schema::hasColumn('routes', 'tenant_id')) {
            if (! FeatureGate::canCreate('master.routes', 'routes', 'tenant_id')) {
                return $this->error(FeatureGate::limitMessage('master.routes') ?? 'Batas rute paket Anda sudah tercapai.', 403);
            }
        }
        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
        ];

        if ($this->hasRoutesBopColumn() && array_key_exists('bop', $data)) {
            $payload['bop'] = (float) ($data['bop'] ?? 0);
        }

        if ($this->hasRoutesTargetRevenueColumn() && array_key_exists('target_revenue', $data)) {
            $payload['target_revenue'] = (float) ($data['target_revenue'] ?? 0);
        }

        if ($this->hasRoutesFixedCostColumn() && array_key_exists('fixed_cost', $data)) {
            $payload['fixed_cost'] = (float) ($data['fixed_cost'] ?? 0);
        }

        if ($id > 0) {
            $routeUpdate = DB::table('routes')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($routeUpdate, 'routes');
            if (! $routeUpdate->exists()) {
                return $this->error('Route not found.', 404);
            }

            DB::table('routes')->where('id', $id)->update($payload);
            if ($this->hasSchedulesRouteId()) {
                $scheduleRouteUpdate = DB::table('schedules')->where('route_id', $id);
                $this->applyWriteTenantScopeIfExists($scheduleRouteUpdate, 'schedules');
                $scheduleRouteUpdate->update(['rute' => $payload['name']]);
            }

            return $this->ok(['message' => 'Route updated.', 'id' => $id]);
        }

        $newId = DB::table('routes')->insertGetId(array_merge($payload, [
            ...$this->tenantPayload('routes'),
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Route created.', 'id' => $newId], 201);
    }

    public function routesDelete(int $id): JsonResponse
    {
        $routeQuery = DB::table('routes')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($routeQuery, 'routes');
        if (! $routeQuery->exists()) {
            return $this->error('Route not found.', 404);
        }

        if ($this->hasSchedulesRouteId()) {
            $scheduleRouteUpdate = DB::table('schedules')->where('route_id', $id);
            $this->applyWriteTenantScopeIfExists($scheduleRouteUpdate, 'schedules');
            $scheduleRouteUpdate->update(['route_id' => null]);
        }
        DB::table('routes')->where('id', $id)->delete();

        return $this->ok(['message' => 'Route deleted.']);
    }

    public function schedulesIndex(Request $request): JsonResponse
    {
        $hasRouteId = $this->hasSchedulesRouteId();
        $hasScheduleUnits = $this->hasScheduleUnitsTable();
        $routeId = (int) $request->query('route_id', 0);
        $rute = trim((string) $request->query('rute', ''));
        $dow = $request->query('dow');

        $query = DB::table('schedules as s')
            ->leftJoin('units as u', 's.unit_id', '=', 'u.id');
        $this->applyTenantScopeIfExists($query, 'schedules', 's');

        if ($hasRouteId) {
            $query->leftJoin('routes as r', 's.route_id', '=', 'r.id');
        }

        $select = ['s.id', 's.rute', 's.dow', 's.jam', 's.units', 's.unit_label', 's.unit_id', 'u.nopol'];
        $select[] = $this->hasSchedulesBopColumn() ? 's.bop' : DB::raw('0 as bop');
        if ($hasRouteId) {
            $select[] = 's.route_id';
            $select[] = DB::raw('COALESCE(r.name, s.rute) as route_name');
        } else {
            $select[] = DB::raw('NULL as route_id');
            $select[] = DB::raw('s.rute as route_name');
        }

        $query
            ->select($select)
            ->orderBy('s.rute')
            ->orderBy('s.dow')
            ->orderBy('s.jam');

        if ($hasRouteId && $routeId > 0) {
            $routeLookup = DB::table('routes')->where('id', $routeId);
            $this->applyTenantScopeIfExists($routeLookup, 'routes');
            $routeName = trim((string) ($routeLookup->value('name') ?? ''));
            $query->where(function (Builder $builder) use ($routeId, $routeName): void {
                $builder->where('s.route_id', $routeId);

                if ($routeName !== '') {
                    $builder->orWhere(function (Builder $legacy) use ($routeName): void {
                        $legacy->whereNull('s.route_id')->where('s.rute', $routeName);
                    });
                }
            });
        } elseif ($rute !== '') {
            $query->where('s.rute', $rute);
        }
        if ($dow !== null && $dow !== '') {
            $query->where('s.dow', (int) $dow);
        }
        $this->applyRouteScopeToQuery($query, $hasRouteId ? 's.route_id' : '', 's.rute');

        $pagination = null;
        if ($request->boolean('paginate')) {
            [$page, $perPage] = $this->paginationParams($request);
            $result = $this->paginateQuery($query, $page, $perPage);
            $rows = collect($result['data']);
            $pagination = $result['meta'];
        } else {
            $rows = $query->get();
        }

        $rows = $rows->map(function ($row) {
            $row->jam = substr((string) $row->jam, 0, 5);

            return $row;
        })->values();

        $optionsBySchedule = [];
        if ($hasScheduleUnits) {
            $scheduleIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
            if (! empty($scheduleIds)) {
                $optionRows = DB::table('schedule_units as su')
                    ->leftJoin('units as u', 'su.unit_id', '=', 'u.id')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->orderBy('su.schedule_id')
                    ->orderBy('su.unit_no');
                $this->applyTenantScopeIfExists($optionRows, 'schedule_units', 'su');
                $optionRows = $optionRows->get([
                    'su.schedule_id',
                    'su.unit_no',
                    'su.label',
                    'su.unit_id',
                    DB::raw('u.nopol as unit_nopol'),
                ]);

                foreach ($optionRows as $item) {
                    $scheduleId = (int) ($item->schedule_id ?? 0);
                    if ($scheduleId <= 0) {
                        continue;
                    }
                    if (! isset($optionsBySchedule[$scheduleId])) {
                        $optionsBySchedule[$scheduleId] = [];
                    }
                    $optionsBySchedule[$scheduleId][] = [
                        'unit_no' => (int) ($item->unit_no ?? 0),
                        'label' => (string) ($item->label ?? ''),
                        'unit_id' => $item->unit_id !== null ? (int) $item->unit_id : null,
                        'nopol' => (string) ($item->unit_nopol ?? ''),
                    ];
                }
            }
        }

        $rows = $rows->map(function ($row) use ($optionsBySchedule) {
            $scheduleId = (int) ($row->id ?? 0);
            $row->unit_options = $optionsBySchedule[$scheduleId] ?? [];

            return $row;
        });

        return $this->ok([
            'schedules' => $rows,
            ...($pagination !== null ? ['pagination' => $pagination] : []),
        ]);
    }

    public function schedulesSave(Request $request): JsonResponse
    {
        $hasRouteId = $this->hasSchedulesRouteId();
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'route_id' => ['nullable', 'integer', 'min:1'],
            'rute' => ['nullable', 'string', 'max:120'],
            'dow' => ['required', 'integer', 'min:0', 'max:6'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'units' => ['nullable', 'integer', 'min:1'],
            'bop' => ['nullable', 'numeric', 'min:0'],
            'unit_label' => ['nullable', 'string', 'max:120'],
            'unit_labels' => ['nullable', 'array'],
            'unit_labels.*' => ['nullable', 'string', 'max:120'],
            'unit_id' => ['nullable', 'integer', 'min:0'],
            'unit_ids' => ['nullable', 'array'],
            'unit_ids.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $routeId = (int) ($data['route_id'] ?? 0);
        $routeName = trim((string) ($data['rute'] ?? ''));
        $jam = $data['jam'].':00';

        if ($hasRouteId && $routeId > 0) {
            $routeQuery = DB::table('routes')->where('id', $routeId);
            $this->applyWriteTenantScopeIfExists($routeQuery, 'routes');
            $route = $routeQuery->first(['id', 'name']);
            if (! $route) {
                return $this->error('Route tidak ditemukan.', 422);
            }
            $routeName = trim((string) $route->name);
        }

        if ($routeName === '') {
            return $this->error('Rute wajib dipilih.', 422);
        }

        if ($hasRouteId && $routeId <= 0) {
            $matched = $this->findRouteByNormalizedName($routeName);
            if ($matched) {
                $routeId = (int) $matched->id;
                $routeName = trim((string) $matched->name);
            }
        }

        if (! PoolScope::canAccessRouteName($routeName)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $duplicateQuery = DB::table('schedules')
            ->where('dow', (int) $data['dow'])
            ->where('jam', $jam)
            ->where(function (Builder $query) use ($hasRouteId, $routeId, $routeName) {
                if ($hasRouteId && $routeId > 0) {
                    $query
                        ->where('route_id', $routeId)
                        ->orWhere(function (Builder $legacy) use ($routeName) {
                            $legacy->whereNull('route_id')->where('rute', $routeName);
                        });

                    return;
                }
                $query->where('rute', $routeName);
            })
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id));
        $this->applyWriteTenantScopeIfExists($duplicateQuery, 'schedules');
        $duplicate = $duplicateQuery->exists();

        if ($duplicate) {
            return $this->error('Jadwal duplikat (rute + hari + jam).', 409);
        }

        $unitsCount = max(1, (int) ($data['units'] ?? 1));
        $rawLabels = is_array($data['unit_labels'] ?? null) ? $data['unit_labels'] : [];
        $fallbackLabel = trim((string) ($data['unit_label'] ?? ''));
        $normalizedLabels = [];
        for ($i = 1; $i <= $unitsCount; $i += 1) {
            $label = trim((string) ($rawLabels[$i - 1] ?? ''));
            if ($label === '' && $fallbackLabel !== '') {
                $label = $fallbackLabel;
            }
            if ($label === '') {
                $label = "Unit {$i}";
            }
            $normalizedLabels[] = $label;
        }

        $rawUnitIds = is_array($data['unit_ids'] ?? null) ? $data['unit_ids'] : [];
        $defaultUnitId = isset($data['unit_id']) ? (int) $data['unit_id'] : 0;
        $normalizedUnitIds = [];
        for ($i = 1; $i <= $unitsCount; $i += 1) {
            $candidate = (int) ($rawUnitIds[$i - 1] ?? 0);
            if ($candidate <= 0 && $defaultUnitId > 0) {
                $candidate = $defaultUnitId;
            }

            $normalizedUnitIds[] = $candidate > 0 ? $candidate : null;
        }

        $lookupUnitIds = array_values(array_unique(array_filter(
            array_map(static fn ($value) => (int) ($value ?? 0), $normalizedUnitIds),
            static fn ($value) => $value > 0,
        )));
        $unitsById = [];
        if (! empty($lookupUnitIds)) {
            $unitsQuery = DB::table('units')
                ->whereIn('id', $lookupUnitIds);
            $this->applyWriteTenantScopeIfExists($unitsQuery, 'units');
            $this->applyPoolScopeIfExists($unitsQuery, 'units');
            $unitsById = $unitsQuery->get(['id', 'kapasitas', 'layout'])
                ->keyBy(static fn ($row) => (int) ($row->id ?? 0))
                ->all();
        }

        foreach ($lookupUnitIds as $unitId) {
            if (! isset($unitsById[$unitId])) {
                return $this->error('Kategori armada pada jadwal tidak ditemukan.', 422);
            }
        }

        $selectedUnitId = (int) ($normalizedUnitIds[0] ?? 0);
        $selectedUnit = $selectedUnitId > 0 ? ($unitsById[$selectedUnitId] ?? null) : null;
        $selectedLayout = null;
        $selectedSeats = 0;
        if ($selectedUnit) {
            $selectedLayout = $this->nullable($selectedUnit->layout ?? null);
            $selectedSeats = $this->countSeatsFromLayoutJson($selectedLayout);
            if ($selectedSeats <= 0) {
                $selectedSeats = max(0, (int) ($selectedUnit->kapasitas ?? 0));
            }
        }

        $payload = [
            'rute' => $routeName,
            'dow' => (int) $data['dow'],
            'jam' => $jam,
            'units' => $unitsCount,
            'unit_label' => $this->nullable($normalizedLabels[0] ?? null),
            'unit_id' => $selectedUnitId > 0 ? $selectedUnitId : null,
            'layout' => $selectedLayout,
        ];
        if ($this->hasSchedulesBopColumn()) {
            $payload['bop'] = (float) ($data['bop'] ?? 0);
        }
        if ($this->hasSchedulesSeatsColumn()) {
            $payload['seats'] = $selectedSeats;
        }
        if ($hasRouteId) {
            $payload['route_id'] = $routeId > 0 ? $routeId : null;
        }

        if ($id > 0) {
            $existingScheduleQuery = DB::table('schedules')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($existingScheduleQuery, 'schedules');
            if (! $existingScheduleQuery->exists()) {
                return $this->error('Schedule not found.', 404);
            }
        }

        try {
            return DB::transaction(function () use ($id, $payload, $normalizedLabels, $normalizedUnitIds) {
                $scheduleId = $id;
                if ($scheduleId > 0) {
                    $scheduleUpdate = DB::table('schedules')->where('id', $scheduleId);
                    $this->applyWriteTenantScopeIfExists($scheduleUpdate, 'schedules');
                    $scheduleUpdate->update($payload);
                } else {
                    $scheduleId = (int) DB::table('schedules')->insertGetId(array_merge($payload, [
                        ...$this->tenantPayload('schedules'),
                        'created_at' => now(),
                    ]));
                }

                if ($this->hasScheduleUnitsTable()) {
                    $scheduleUnitsDelete = DB::table('schedule_units')->where('schedule_id', $scheduleId);
                    $this->applyWriteTenantScopeIfExists($scheduleUnitsDelete, 'schedule_units');
                    $scheduleUnitsDelete->delete();
                    $rows = [];
                    foreach ($normalizedLabels as $idx => $label) {
                        $rows[] = array_merge([
                            'schedule_id' => $scheduleId,
                            'unit_no' => $idx + 1,
                            'label' => $label,
                            'unit_id' => $normalizedUnitIds[$idx] ?? null,
                            'created_at' => now(),
                        ], $this->tenantPayload('schedule_units'));
                    }
                    if (! empty($rows)) {
                        DB::table('schedule_units')->insert($rows);
                    }
                }

                if ($id > 0) {
                    return $this->ok(['message' => 'Schedule updated.', 'id' => $scheduleId]);
                }

                return $this->ok(['message' => 'Schedule created.', 'id' => $scheduleId], 201);
            });
        } catch (QueryException $e) {
            return $this->error('Failed saving schedule.', 500, ['detail' => $e->getMessage()]);
        }
    }

    public function schedulesDelete(int $id): JsonResponse
    {
        $scheduleQuery = DB::table('schedules')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($scheduleQuery, 'schedules');
        if (! $scheduleQuery->exists()) {
            return $this->error('Schedule not found.', 404);
        }

        if ($this->hasScheduleUnitsTable()) {
            $scheduleUnitsDelete = DB::table('schedule_units')->where('schedule_id', $id);
            $this->applyWriteTenantScopeIfExists($scheduleUnitsDelete, 'schedule_units');
            $scheduleUnitsDelete->delete();
        }
        $scheduleDelete = DB::table('schedules')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($scheduleDelete, 'schedules');
        $scheduleDelete->delete();

        return $this->ok(['message' => 'Schedule deleted.']);
    }

    public function driversIndex(Request $request): JsonResponse
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $rows = collect($this->driverRowsForMonth($monthStart, $monthEnd));
        $q = trim((string) $request->query('q', ''));

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows
                ->filter(static fn (array $row): bool => str_contains(mb_strtolower(implode(' ', [
                    (string) ($row['nama'] ?? ''),
                    (string) ($row['phone'] ?? ''),
                    (string) ($row['nopol'] ?? ''),
                    (string) ($row['pool_name'] ?? ''),
                ])), $needle))
                ->values();
        }

        if (! $request->boolean('paginate')) {
            return $this->ok(['drivers' => $rows]);
        }

        [$page, $perPage] = $this->paginationParams($request);
        $pagination = $this->paginationMeta($rows->count(), $page, $perPage);

        return $this->ok([
            'drivers' => $rows
                ->slice(($pagination['page'] - 1) * $pagination['per_page'], $pagination['per_page'])
                ->values(),
            'pagination' => $pagination,
        ]);
    }

    public function driversSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'nama' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'armada_id' => ['nullable', 'integer', 'min:1'],
            'armada_nopol' => ['nullable', 'string', 'max:50'],
            'target_revenue_bulanan' => ['nullable', 'numeric', 'min:0'],
            'target_revenue_tahunan' => ['nullable', 'numeric', 'min:0'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'bop' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $existing = null;
        if ($id > 0) {
            $existingQuery = DB::table('drivers')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($existingQuery, 'drivers');
            $this->applyPoolScopeIfExists($existingQuery, 'drivers');
            $existing = $existingQuery->first(['id', Schema::hasColumn('drivers', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id')]);

            if (! $existing) {
                return $this->error('Driver tidak ditemukan untuk pool aktif.', 404);
            }
        }

        $targetPoolId = $this->resolveWritablePoolIdFromRequest(
            $request,
            'drivers',
            (int) ($existing->pool_id ?? 0),
            $id <= 0,
        );
        if ($targetPoolId < 0) {
            return $this->error($this->poolResolveErrorMessage($targetPoolId), $targetPoolId === -1 ? 403 : 422);
        }

        if ($id <= 0 && FeatureGate::enabled() && Schema::hasColumn('drivers', 'tenant_id')) {
            if (! FeatureGate::canCreate('master.drivers', 'drivers', 'tenant_id')) {
                return $this->error(FeatureGate::limitMessage('master.drivers') ?? 'Batas driver paket Anda sudah tercapai.', 403);
            }
        }
        $armadaId = (int) ($data['armada_id'] ?? 0);
        $requestedArmadaNopol = strtoupper(trim((string) ($data['armada_nopol'] ?? '')));
        $armadaNopol = $requestedArmadaNopol !== '' ? $requestedArmadaNopol : null;

        if (Schema::hasTable('armadas')) {
            if ($armadaId > 0) {
                $armadaQuery = DB::table('armadas')->where('id', $armadaId);
                if (Schema::hasColumn('armadas', 'tenant_id')) {
                    PoolScope::applyTenantScope($armadaQuery, 'tenant_id');
                }
                $this->applyPoolScopeIfExists($armadaQuery, 'armadas', '', $targetPoolId > 0 ? $targetPoolId : null);
                $armada = $armadaQuery->first(['id', 'nopol', Schema::hasColumn('armadas', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id')]);
                if (! $armada) {
                    return $this->error('Nopol armada tidak ditemukan.', 422);
                }
                $armadaNopol = strtoupper(trim((string) ($armada->nopol ?? '')));
            } elseif ($requestedArmadaNopol !== '') {
                $armadaQuery = DB::table('armadas')
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol]);
                if (Schema::hasColumn('armadas', 'tenant_id')) {
                    PoolScope::applyTenantScope($armadaQuery, 'tenant_id');
                }
                $this->applyPoolScopeIfExists($armadaQuery, 'armadas', '', $targetPoolId > 0 ? $targetPoolId : null);
                $armada = $armadaQuery->first(['id', 'nopol', Schema::hasColumn('armadas', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id')]);

                if (! $armada) {
                    return $this->error('Nopol armada tidak ditemukan.', 422);
                }

                $armadaId = (int) $armada->id;
                $armadaNopol = strtoupper(trim((string) ($armada->nopol ?? '')));
            }
        }

        $payload = [
            'nama' => strtoupper(trim((string) $data['nama'])),
            'phone' => $this->nullable($data['phone'] ?? null),
        ];

        if ($this->hasDriversTargetRevenueBulananColumn()) {
            $payload['target_revenue_bulanan'] = (float) ($data['target_revenue_bulanan'] ?? 0);
        }

        if ($this->hasDriversTargetRevenueTahunanColumn() && array_key_exists('target_revenue_tahunan', $data)) {
            $payload['target_revenue_tahunan'] = (float) ($data['target_revenue_tahunan'] ?? 0);
        }

        if ($this->driversHasArmadaId()) {
            $payload['unit_id'] = null;
            $payload['armada_id'] = $armadaId > 0 ? $armadaId : null;
        } else {
            $payload['unit_id'] = isset($data['unit_id']) ? (int) $data['unit_id'] : null;
        }

        if ($this->driversHasArmadaNopol()) {
            $payload['armada_nopol'] = $armadaNopol ?: null;
        }

        if ($this->hasDriversRevenueColumn()) {
            $payload['revenue'] = (float) ($data['revenue'] ?? 0);
        }

        if ($this->hasDriversBopColumn() && array_key_exists('bop', $data)) {
            $payload['bop'] = (float) ($data['bop'] ?? 0);
        }

        if ($this->hasDriversFixedCostColumn()) {
            $payload['fixed_cost'] = (float) ($data['fixed_cost'] ?? 0);
        }

        $payload = array_merge($payload, $this->poolPayload('drivers', $targetPoolId > 0 ? $targetPoolId : null));

        if ($id > 0) {
            $query = DB::table('drivers')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($query, 'drivers');
            $this->applyPoolScopeIfExists($query, 'drivers');
            $updated = $query->update($payload);

            if ($updated === 0) {
                return $this->error('Driver tidak ditemukan untuk pool aktif.', 404);
            }

            return $this->ok(['message' => 'Driver updated.', 'id' => $id]);
        }

        $newId = DB::table('drivers')->insertGetId(array_merge($payload, $this->tenantPayload('drivers'), [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Driver created.', 'id' => $newId], 201);
    }

    public function driversDelete(int $id): JsonResponse
    {
        $query = DB::table('drivers')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($query, 'drivers');
        $this->applyPoolScopeIfExists($query, 'drivers');
        if (! $query->exists()) {
            return $this->error('Driver tidak ditemukan untuk pool aktif.', 404);
        }

        $delete = DB::table('drivers')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($delete, 'drivers');
        $this->applyPoolScopeIfExists($delete, 'drivers');
        $delete->delete();

        return $this->ok(['message' => 'Driver deleted.']);
    }

    public function luggageServicesIndex(): JsonResponse
    {
        $rows = DB::table('luggage_services')
            ->orderBy('name')
            ->when(Schema::hasColumn('luggage_services', 'tenant_id'), function (Builder $q) {
                PoolScope::applyTenantScope($q, 'luggage_services.tenant_id');
            })
            ->when(Schema::hasColumn('luggage_services', 'pool_id'), function (Builder $q): void {
                $this->applyPoolScopeIfExists($q, 'luggage_services');
            })
            ->get(['id', 'name']);

        return $this->ok(['services' => $rows]);
    }

    public function luggageServicesSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => trim((string) $data['name']),
        ];

        if ($id > 0) {
            $query = DB::table('luggage_services')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($query, 'luggage_services');
            $this->applyPoolScopeIfExists($query, 'luggage_services');
            $query->update($payload);

            return $this->ok(['message' => 'Luggage service updated.', 'id' => $id]);
        }

        $newId = DB::table('luggage_services')->insertGetId(array_merge($payload, $this->tenantPayload('luggage_services'), $this->poolPayload('luggage_services'), [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Luggage service created.', 'id' => $newId], 201);
    }

    public function luggageServicesDelete(int $id): JsonResponse
    {
        $query = DB::table('luggage_services')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($query, 'luggage_services');
        $this->applyPoolScopeIfExists($query, 'luggage_services');
        $query->delete();

        return $this->ok(['message' => 'Luggage service deleted.']);
    }

    public function segmentsIndex(Request $request): JsonResponse
    {
        $routeId = (int) $request->query('route_id', 0);
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('segments as s')
            ->leftJoin('routes as r', 's.route_id', '=', 'r.id')
            ->select([
                's.id',
                's.route_id',
                's.rute',
                's.origin',
                's.destination',
                's.harga',
                DB::raw('r.name as route_name'),
            ])
            ->orderBy('s.rute');
        $this->applyTenantScopeIfExists($query, 'segments', 's');

        if ($routeId > 0) {
            $query->where('s.route_id', $routeId);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function (Builder $builder) use ($qLike): void {
                $builder
                    ->where('s.rute', 'like', $qLike)
                    ->orWhere('s.origin', 'like', $qLike)
                    ->orWhere('s.destination', 'like', $qLike)
                    ->orWhere('r.name', 'like', $qLike);
            });
        }
        $this->applyRouteScopeToQuery($query, 's.route_id', 's.rute');

        if (! $request->boolean('paginate')) {
            return $this->ok(['segments' => $query->get()]);
        }

        [$page, $perPage] = $this->paginationParams($request);
        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'segments' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function segmentsSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'route_id' => ['required', 'integer', 'min:1'],
            'rute' => ['required', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:120'],
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $routeQuery = DB::table('routes')->where('id', (int) $data['route_id']);
        $this->applyWriteTenantScopeIfExists($routeQuery, 'routes');
        $route = $routeQuery->first(['id', 'name']);
        if (! $route) {
            return $this->error('Route tidak ditemukan.', 422);
        }

        $routeName = trim((string) ($route->name ?? $data['rute']));
        if (! PoolScope::canAccessRouteName($routeName)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $payload = [
            'route_id' => (int) $data['route_id'],
            'rute' => $routeName !== '' ? $routeName : trim((string) $data['rute']),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
            'harga' => (float) $data['harga'],
        ];

        if ($id > 0) {
            $segmentUpdate = DB::table('segments')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($segmentUpdate, 'segments');
            if (! $segmentUpdate->exists()) {
                return $this->error('Segment not found.', 404);
            }

            $segmentUpdate->update($payload);

            return $this->ok(['message' => 'Segment updated.', 'id' => $id]);
        }

        $newId = DB::table('segments')->insertGetId(array_merge($payload, [
            ...$this->tenantPayload('segments'),
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Segment created.', 'id' => $newId], 201);
    }

    public function segmentsDelete(int $id): JsonResponse
    {
        $segmentDelete = DB::table('segments')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($segmentDelete, 'segments');
        if (! $segmentDelete->exists()) {
            return $this->error('Segment not found.', 404);
        }

        $segmentDelete->delete();

        return $this->ok(['message' => 'Segment deleted.']);
    }

    public function customersIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('customers')
            ->select([
                'id',
                'name',
                'phone',
                'pickup_point',
                'gmaps',
                Schema::hasColumn('customers', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id'),
            ])
            ->orderBy('name');
        PoolScope::applyCustomerScope($query, 'customers');
        $this->applyTenantScopeIfExists($query, 'customers');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike) {
                $builder
                    ->where('name', 'like', $qLike)
                    ->orWhere('phone', 'like', $qLike)
                    ->orWhere('pickup_point', 'like', $qLike)
                    ->orWhere('gmaps', 'like', $qLike);
            });
        }

        $result = $this->paginateQuery($query, $page, $perPage);
        $rows = collect($result['data'] ?? []);
        $poolNames = $this->poolNameMap(
            $rows->pluck('pool_id')
                ->map(static fn ($value): int => (int) $value)
                ->all(),
        );

        $customers = $rows->map(function ($row) use ($poolNames) {
            $poolId = (int) data_get($row, 'pool_id', 0);

            if (is_array($row)) {
                $row['pool_id'] = $poolId > 0 ? $poolId : null;
                $row['pool_name'] = $poolId > 0 ? ($poolNames[$poolId] ?? null) : null;

                return $row;
            }

            $row->pool_id = $poolId > 0 ? $poolId : null;
            $row->pool_name = $poolId > 0 ? ($poolNames[$poolId] ?? null) : null;

            return $row;
        })->values()->all();

        return $this->ok([
            'customers' => $customers,
            'pagination' => $result['meta'],
        ]);
    }

    public function customersSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'pickup_point' => ['nullable', 'string', 'max:180'],
            'gmaps' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'phone' => trim((string) $data['phone']),
            'pickup_point' => $this->nullable($data['pickup_point'] ?? null),
            'gmaps' => $this->nullable($data['gmaps'] ?? $data['address'] ?? null),
        ];
        $poolId = 0;

        if ($id > 0) {
            $customerUpdate = DB::table('customers')->where('id', $id);
            PoolScope::applyCustomerScope($customerUpdate, 'customers');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customers');
            $existing = $customerUpdate->first([
                'id',
                Schema::hasColumn('customers', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id'),
            ]);
            if (! $existing) {
                return $this->error('Customer not found.', 404);
            }

            $poolId = $this->resolveWritablePoolIdFromRequest($request, 'customers', (int) ($existing->pool_id ?? 0), false);
            if ($poolId < 0) {
                return $this->error($this->poolResolveErrorMessage($poolId), 422);
            }

            $customerUpdate->update(array_merge($payload, $this->poolPayload('customers', $poolId > 0 ? $poolId : null)));

            return $this->ok(['message' => 'Customer updated.', 'id' => $id]);
        }

        $existingQuery = DB::table('customers')->where('phone', $payload['phone']);
        PoolScope::applyCustomerScope($existingQuery, 'customers');
        $this->applyWriteTenantScopeIfExists($existingQuery, 'customers');
        $existing = $existingQuery->first([
            'id',
            Schema::hasColumn('customers', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id'),
        ]);
        if ($existing) {
            $poolId = $this->resolveWritablePoolIdFromRequest($request, 'customers', (int) ($existing->pool_id ?? 0), false);
            if ($poolId < 0) {
                return $this->error($this->poolResolveErrorMessage($poolId), 422);
            }

            $customerUpdate = DB::table('customers')->where('id', (int) $existing->id);
            PoolScope::applyCustomerScope($customerUpdate, 'customers');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customers');
            $customerUpdate->update(array_merge($payload, $this->poolPayload('customers', $poolId > 0 ? $poolId : null)));

            return $this->ok(['message' => 'Customer updated by phone.', 'id' => (int) $existing->id]);
        }

        $poolId = $this->resolveWritablePoolIdFromRequest($request, 'customers', 0, true);
        if ($poolId < 0) {
            return $this->error($this->poolResolveErrorMessage($poolId), 422);
        }

        $newId = DB::table('customers')->insertGetId(array_merge($payload, $this->poolPayload('customers', $poolId > 0 ? $poolId : null), [
            ...$this->tenantPayload('customers'),
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Customer created.', 'id' => $newId], 201);
    }

    public function customersDelete(int $id): JsonResponse
    {
        $customerDelete = DB::table('customers')->where('id', $id);
        PoolScope::applyCustomerScope($customerDelete, 'customers');
        $this->applyWriteTenantScopeIfExists($customerDelete, 'customers');
        if (! $customerDelete->exists()) {
            return $this->error('Customer not found.', 404);
        }

        $customerDelete->delete();

        return $this->ok(['message' => 'Customer deleted.']);
    }

    public function customersTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'phone', 'pickup_point', 'gmaps']);
            fputcsv($out, ['Customer Contoh OptiBus', '081234567890', 'Terminal Kayuringin', 'https://maps.google.com/?q=Terminal+Kayuringin']);
            fclose($out);
        }, 'template-customer-reguler.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function customersImport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $data['file'];
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            return $this->error('File import tidak bisa dibaca.', 422);
        }

        $sampleLine = (string) (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)[0] ?? '');
        $delimiter = substr_count($sampleLine, ';') > substr_count($sampleLine, ',') ? ';' : ',';
        $handle = fopen($path, 'r');
        if (! $handle) {
            return $this->error('File import tidak bisa dibuka.', 422);
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers)) {
            fclose($handle);

            return $this->error('Template import kosong atau tidak valid.', 422);
        }

        $columns = [];
        foreach ($headers as $index => $header) {
            $key = $this->normalizeCustomerImportHeader((string) $header);
            if ($key !== null) {
                $columns[$key] = $index;
            }
        }

        if (! isset($columns['name'], $columns['phone'])) {
            fclose($handle);

            return $this->error('Header wajib minimal: name dan phone.', 422);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;
        $poolId = $this->defaultCustomerPoolId();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line += 1;

            if ($this->isBlankCsvRow($row)) {
                continue;
            }

            $name = $this->customerImportValue($row, $columns, 'name');
            $phone = $this->customerImportValue($row, $columns, 'phone');
            $pickupPoint = $this->customerImportValue($row, $columns, 'pickup_point');
            $gmaps = $this->customerImportValue($row, $columns, 'gmaps');

            if ($name === '' || $phone === '') {
                $skipped += 1;
                $errors[] = "Baris {$line}: name dan phone wajib diisi.";

                continue;
            }

            if (mb_strlen($name) > 120 || mb_strlen($phone) > 30 || mb_strlen($pickupPoint) > 180) {
                $skipped += 1;
                $errors[] = "Baris {$line}: panjang name/phone/pickup_point melebihi batas.";

                continue;
            }

            $payload = [
                'name' => strtoupper($name),
                'phone' => $phone,
                'pickup_point' => $pickupPoint !== '' ? $pickupPoint : null,
                'gmaps' => $gmaps !== '' ? $gmaps : null,
            ];

            $existingQuery = DB::table('customers')->where('phone', $phone);
            PoolScope::applyCustomerScope($existingQuery, 'customers');
            $this->applyWriteTenantScopeIfExists($existingQuery, 'customers');
            $existingId = $existingQuery->value('id');
            if ($existingId) {
                $customerUpdate = DB::table('customers')->where('id', (int) $existingId);
                PoolScope::applyCustomerScope($customerUpdate, 'customers');
                $this->applyWriteTenantScopeIfExists($customerUpdate, 'customers');
                $customerUpdate->update($payload);
                $this->assignCustomerPoolIfMissing((int) $existingId, $poolId);
                $updated += 1;

                continue;
            }

            DB::table('customers')->insert(array_merge($payload, $poolId > 0 ? ['pool_id' => $poolId] : [], [
                ...$this->tenantPayload('customers'),
                'created_at' => now(),
            ]));
            $created += 1;
        }

        fclose($handle);

        return $this->ok([
            'message' => 'Import customer selesai.',
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10),
        ]);
    }

    public function cancellationsIndex(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = (int) $request->query('limit', 0);
        if ($limit > 0) {
            $perPage = max(10, min(100, $limit));
        } else {
            [, $perPage] = $this->paginationParams($request);
        }
        $offset = max(0, ($page - 1) * $perPage);
        $items = ActivityLog::recent($perPage, $offset);
        $total = ActivityLog::count();
        $lastPage = max(1, (int) ceil($total / $perPage));

        return $this->ok([
            'cancellations' => $items,
            'pagination' => [
                'page' => min($page, $lastPage),
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }

    public function reportsSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', Rule::in(['booking', 'charter', 'bagasi'])],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'route_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $type = $validated['type'] ?? 'booking';
        $poolId = (int) ($validated['pool_id'] ?? 0);
        $routeId = (int) ($validated['route_id'] ?? 0);
        $routeFilter = $this->resolveAccessibleRouteFilter($routeId, $poolId);
        [$from, $to] = $this->normalizeDateRange($from, $to);
        [$page, $perPage] = $this->paginationParams($request);
        $rangeKey = implode(':', [
            $type,
            $from,
            $to,
            $poolId,
            $routeId,
            (int) (auth()->id() ?? 0),
            $page,
            $perPage,
            $this->reportCacheSignatureForType($type),
        ]);

        $report = Cache::remember("admin-ops:reports-summary:{$rangeKey}", now()->addSeconds(30), function () use ($type, $from, $to, $page, $perPage, $poolId, $routeFilter): array {
            return $this->buildTypedReport($type, $from, $to, $page, $perPage, $poolId, $routeFilter);
        });

        return $this->ok($report);
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function buildTypedReport(string $type, string $from, string $to, int $page, int $perPage, int $poolId = 0, array $routeFilter = []): array
    {
        return match ($type) {
            'charter' => $this->buildCharterReport($from, $to, $page, $perPage, $poolId, $routeFilter),
            'bagasi' => $this->buildLuggageReport($from, $to, $page, $perPage, $poolId, $routeFilter),
            default => $this->buildBookingReport($from, $to, $page, $perPage, $poolId, $routeFilter),
        };
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function buildBookingReport(string $from, string $to, int $page, int $perPage, int $poolId = 0, array $routeFilter = []): array
    {
        $baseQuery = DB::table('bookings as b')
            ->whereBetween('b.tanggal', [$from, $to]);
        $this->applyNotCanceledFilter($baseQuery, 'b.status');
        $this->applyRouteScopeToQuery($baseQuery, '', 'b.rute', $poolId);
        $this->applyTenantScopeIfExists($baseQuery, 'bookings', 'b');
        $this->applyResolvedRouteFilter(
            $baseQuery,
            $routeFilter,
            Schema::hasColumn('bookings', 'route_id') ? 'b.route_id' : '',
            'b.rute',
        );

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('COALESCE(SUM(COALESCE(b.price, 0) - COALESCE(b.discount, 0)), 0) as revenue_total')
            ->first();
        $pagination = $this->paginationMeta((int) ($summaryRow->total_rows ?? 0), $page, $perPage);

        $rows = (clone $baseQuery)
            ->orderByDesc('b.tanggal')
            ->orderByDesc('b.jam')
            ->orderByDesc('b.id')
            ->forPage($pagination['page'], $pagination['per_page'])
            ->get([
                'b.id',
                'b.tanggal',
                'b.jam',
                'b.name',
                'b.phone',
                'b.rute',
                'b.pickup_point',
                'b.unit',
                'b.seat',
                'b.pembayaran',
                'b.status',
                DB::raw('COALESCE(b.discount, 0) as discount'),
                DB::raw('(COALESCE(b.price, 0) - COALESCE(b.discount, 0)) as total'),
            ])
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'tanggal' => (string) ($row->tanggal ?? ''),
                'jam' => (string) ($row->jam ?? ''),
                'name' => (string) ($row->name ?? ''),
                'phone' => (string) ($row->phone ?? ''),
                'rute' => (string) ($row->rute ?? ''),
                'pickup_point' => (string) ($row->pickup_point ?? ''),
                'unit' => (string) ($row->unit ?? ''),
                'seat' => (string) ($row->seat ?? ''),
                'pembayaran' => (string) ($row->pembayaran ?? ''),
                'status' => (string) ($row->status ?? ''),
                'discount' => (float) ($row->discount ?? 0),
                'total' => (float) ($row->total ?? 0),
            ])
            ->values()
            ->all();

        $revenueTotal = (float) ($summaryRow->revenue_total ?? 0);
        $scopeMeta = $this->routeScopeReportMeta($poolId);
        $target = (float) ($scopeMeta['target_revenue'] ?? 0);
        $bookingBop = $this->estimateReportBookingBop($from, $to, $poolId, $routeFilter);

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'booking',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => $revenueTotal,
                'bop_total' => $bookingBop,
                'margin_total' => $revenueTotal - $bookingBop,
                'achievement_percent' => $target > 0 ? round(($revenueTotal / $target) * 100, 1) : 0,
            ] + $scopeMeta,
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function estimateReportBookingBop(string $from, string $to, int $poolId = 0, array $routeFilter = []): float
    {
        if (! Schema::hasTable('routes') || ! Schema::hasColumn('routes', 'bop') || ! Schema::hasTable('bookings')) {
            return 0.0;
        }

        $routeQuery = DB::table('bookings')
            ->whereBetween('tanggal', [$from, $to])
            ->select('rute')
            ->distinct();
        $this->applyNotCanceledFilter($routeQuery, 'status');
        $this->applyRouteScopeToQuery($routeQuery, '', 'rute', $poolId);
        $this->applyTenantScopeIfExists($routeQuery, 'bookings');
        $this->applyResolvedRouteFilter(
            $routeQuery,
            $routeFilter,
            Schema::hasColumn('bookings', 'route_id') ? 'route_id' : '',
            'rute',
        );

        $routes = $routeQuery->pluck('rute')->all();

        if (empty($routes)) {
            return 0.0;
        }

        $routesQuery = DB::table('routes')->whereIn('name', $routes);
        $this->applyTenantScopeIfExists($routesQuery, 'routes');

        return (float) $routesQuery->sum('bop');
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function buildCharterReport(string $from, string $to, int $page, int $perPage, int $poolId = 0, array $routeFilter = []): array
    {
        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();

        $select = [
            'c.id',
            'c.start_date',
            'c.end_date',
            'c.departure_time',
            'c.name',
            'c.phone',
            'c.pickup_point',
            'c.drop_point',
            'c.driver_name',
            'c.layanan',
            'c.payment_status',
            'c.bop_status',
            'c.price',
            DB::raw($hasStatusColumn ? 'c.status as status' : "CASE WHEN c.payment_status = 'Canceled' THEN 'canceled' WHEN c.bop_status = 'done' THEN 'done' ELSE 'active' END as status"),
            DB::raw('u.nopol as unit_nopol'),
        ];

        if ($hasArmadaNopolColumn) {
            $select[] = 'c.armada_nopol';
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $baseQuery = DB::table('charters as c')
            ->whereBetween('c.start_date', [$from, $to]);
        $this->applyActiveCharterReportFilter($baseQuery);
        $this->applyCharterPoolScope($baseQuery, $poolId);
        $this->applyTenantScopeIfExists($baseQuery, 'charters', 'c');
        $routeName = trim((string) ($routeFilter['name'] ?? ''));
        if (($routeFilter['requested'] ?? false) && $routeName === '') {
            $baseQuery->whereRaw('1 = 0');
        } elseif ($routeName !== '') {
            $baseQuery->where(function (Builder $q) use ($routeName): void {
                $q->where('c.pickup_point', 'like', "%{$routeName}%")
                    ->orWhere('c.drop_point', 'like', "%{$routeName}%");
            });
        }

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('COALESCE(SUM(COALESCE(c.price, 0)), 0) as revenue_total')
            ->selectRaw('COALESCE(SUM(COALESCE(c.bop_price, 0)), 0) as bop_total')
            ->first();
        $pagination = $this->paginationMeta((int) ($summaryRow->total_rows ?? 0), $page, $perPage);

        $rows = (clone $baseQuery)
            ->leftJoin('units as u', 'c.unit_id', '=', 'u.id')
            ->orderByDesc('c.start_date')
            ->orderByDesc('c.id')
            ->forPage($pagination['page'], $pagination['per_page'])
            ->get($select)
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'start_date' => (string) ($row->start_date ?? ''),
                'end_date' => (string) ($row->end_date ?? ''),
                'departure_time' => (string) ($row->departure_time ?? ''),
                'name' => (string) ($row->name ?? ''),
                'phone' => (string) ($row->phone ?? ''),
                'pickup_point' => (string) ($row->pickup_point ?? ''),
                'drop_point' => (string) ($row->drop_point ?? ''),
                'driver_name' => (string) ($row->driver_name ?? ''),
                'layanan' => (string) ($row->layanan ?? ''),
                'payment_status' => (string) ($row->payment_status ?? ''),
                'bop_status' => (string) ($row->bop_status ?? ''),
                'status' => (string) ($row->status ?? ''),
                'unit_nopol' => (string) ($row->unit_nopol ?? ''),
                'armada_nopol' => (string) ($row->armada_nopol ?? ''),
                'total' => (float) ($row->price ?? 0),
            ])
            ->values()
            ->all();

        $revenueTotal = (float) ($summaryRow->revenue_total ?? 0);
        $bopTotal = (float) ($summaryRow->bop_total ?? 0);
        $scopeMeta = $this->routeScopeReportMeta($poolId);
        $target = (float) ($scopeMeta['target_revenue'] ?? 0);

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'charter',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => $revenueTotal,
                'bop_total' => $bopTotal,
                'margin_total' => $revenueTotal - $bopTotal,
                'achievement_percent' => $target > 0 ? round(($revenueTotal / $target) * 100, 1) : 0,
            ] + $scopeMeta,
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function buildLuggageReport(string $from, string $to, int $page, int $perPage, int $poolId = 0, array $routeFilter = []): array
    {
        [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);

        $baseQuery = DB::table('luggages as l')
            ->whereBetween('l.created_at', [$createdFrom, $createdTo]);
        $this->applyNotCanceledFilter($baseQuery, 'l.status');
        $this->applyTenantScopeIfExists($baseQuery, 'luggages', 'l');
        $this->applyPoolOrRouteScopeToQuery(
            $baseQuery,
            $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
            Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
            'l.rute',
            $poolId,
        );
        $this->applyResolvedRouteFilter(
            $baseQuery,
            $routeFilter,
            Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
            'l.rute',
        );

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('COALESCE(SUM(COALESCE(l.price, 0)), 0) as revenue_total')
            ->first();
        $pagination = $this->paginationMeta((int) ($summaryRow->total_rows ?? 0), $page, $perPage);

        $rows = (clone $baseQuery)
            ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id')
            ->orderByDesc('l.created_at')
            ->orderByDesc('l.id')
            ->forPage($pagination['page'], $pagination['per_page'])
            ->get([
                'l.id',
                DB::raw('DATE(l.created_at) as tanggal'),
                'l.created_at',
                'l.kode_resi',
                'l.sender_name',
                'l.sender_phone',
                'l.receiver_name',
                'l.receiver_phone',
                'l.quantity',
                'l.payment_status',
                'l.status',
                'l.price',
                DB::raw('s.name as service_name'),
            ])
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'tanggal' => (string) ($row->tanggal ?? ''),
                'created_at' => (string) ($row->created_at ?? ''),
                'kode_resi' => (string) ($row->kode_resi ?? ''),
                'sender_name' => (string) ($row->sender_name ?? ''),
                'sender_phone' => (string) ($row->sender_phone ?? ''),
                'receiver_name' => (string) ($row->receiver_name ?? ''),
                'receiver_phone' => (string) ($row->receiver_phone ?? ''),
                'quantity' => (int) ($row->quantity ?? 0),
                'payment_status' => (string) ($row->payment_status ?? ''),
                'status' => $this->normalizeLuggageStatus($row->status ?? null),
                'service_name' => (string) ($row->service_name ?? ''),
                'total' => (float) ($row->price ?? 0),
            ])
            ->values()
            ->all();

        $revenueTotal = (float) ($summaryRow->revenue_total ?? 0);
        $scopeMeta = $this->routeScopeReportMeta($poolId);
        $target = (float) ($scopeMeta['target_revenue'] ?? 0);

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'bagasi',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => $revenueTotal,
                'bop_total' => 0,
                'margin_total' => $revenueTotal,
                'achievement_percent' => $target > 0 ? round(($revenueTotal / $target) * 100, 1) : 0,
            ] + $scopeMeta,
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    private function reportCacheSignatureForType(string $type): string
    {
        $poolTables = $this->poolTablesReady() ? ['pools', 'pool_route', 'pool_user'] : [];

        return match ($type) {
            'charter' => $this->buildTablesMutationSignature(array_values(array_filter([
                'charters',
                'units',
                $this->chartersHasArmadaNopolColumn() ? 'armadas' : null,
                ...$poolTables,
            ]))),
            'bagasi' => $this->buildTablesMutationSignature(array_merge(['luggages', 'luggage_services'], $poolTables)),
            default => $this->buildTablesMutationSignature(array_merge(['bookings'], $poolTables)),
        };
    }

    public function reportsBookingsCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'route_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $poolId = (int) ($validated['pool_id'] ?? 0);
        $routeFilter = $this->resolveAccessibleRouteFilter((int) ($validated['route_id'] ?? 0), $poolId);
        [$from, $to] = $this->normalizeDateRange($from, $to);
        $filename = "bookings-report-{$from}-to-{$to}.csv";

        $query = DB::table('bookings')
            ->whereBetween('tanggal', [$from, $to])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->orderBy('rute');
        $this->applyNotCanceledFilter($query, 'status');
        $this->applyRouteScopeToQuery($query, '', 'rute', $poolId);
        $this->applyTenantScopeIfExists($query, 'bookings');
        $this->applyResolvedRouteFilter(
            $query,
            $routeFilter,
            Schema::hasColumn('bookings', 'route_id') ? 'route_id' : '',
            'rute',
        );

        $rows = $query->get([
            'id',
            'rute',
            'tanggal',
            'jam',
            'unit',
            'seat',
            'name',
            'phone',
            'pickup_point',
            'pembayaran',
            'status',
            'price',
            'discount',
            'created_at',
        ]);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'rute', 'tanggal', 'jam', 'unit', 'seat', 'name', 'phone', 'pickup_point', 'pembayaran', 'status', 'price', 'discount', 'created_at']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->rute,
                    $row->tanggal,
                    substr((string) $row->jam, 0, 5),
                    $row->unit,
                    $row->seat,
                    $row->name,
                    $row->phone,
                    $row->pickup_point,
                    $row->pembayaran,
                    $row->status,
                    $row->price,
                    $row->discount,
                    $row->created_at,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function reportsRevenueCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', 'in:reguler,bagasi,charter'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'route_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        [$from, $to] = $this->normalizeDateRange($from, $to);
        $type = $validated['type'] ?? 'reguler';
        $poolId = (int) ($validated['pool_id'] ?? 0);
        $routeFilter = $this->resolveAccessibleRouteFilter((int) ($validated['route_id'] ?? 0), $poolId);

        $filename = "report-{$type}-{$from}-to-{$to}.csv";

        if ($type === 'bagasi') {
            [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);
            $query = DB::table('luggages as l')
                ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id')
                ->whereBetween('l.created_at', [$createdFrom, $createdTo])
                ->orderByDesc('l.created_at');
            $this->applyNotCanceledFilter($query, 'l.status');
            $this->applyTenantScopeIfExists($query, 'luggages', 'l');
            $this->applyPoolOrRouteScopeToQuery(
                $query,
                $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
                Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
                'l.rute',
                $poolId,
            );
            $this->applyResolvedRouteFilter(
                $query,
                $routeFilter,
                Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
                'l.rute',
            );

            $rows = $query->get([
                DB::raw('date(l.created_at) as tanggal'),
                DB::raw('l.sender_name as name'),
                DB::raw('l.receiver_name as phone'),
                DB::raw('s.name as rute'),
                DB::raw('l.price as final_price'),
            ]);

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Tanggal', 'Pengirim', 'Penerima', 'Layanan', 'Total']);
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->tanggal,
                        $row->name,
                        $row->phone,
                        $row->rute,
                        (float) $row->final_price,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($type === 'charter') {
            $query = DB::table('charters as c')
                ->whereBetween('c.start_date', [$from, $to])
                ->orderByDesc('c.start_date');
            $this->applyActiveCharterReportFilter($query, 'c');
            $this->applyCharterPoolScope($query, $poolId);
            $this->applyTenantScopeIfExists($query, 'charters', 'c');
            $routeName = trim((string) ($routeFilter['name'] ?? ''));
            if (($routeFilter['requested'] ?? false) && $routeName === '') {
                $query->whereRaw('1 = 0');
            } elseif ($routeName !== '') {
                $query->where(function (Builder $builder) use ($routeName): void {
                    $builder
                        ->where('c.pickup_point', 'like', "%{$routeName}%")
                        ->orWhere('c.drop_point', 'like', "%{$routeName}%");
                });
            }

            $rows = $query->get([
                DB::raw('c.start_date as tanggal'),
                'c.name',
                'c.phone',
                'c.pickup_point',
                'c.drop_point',
                DB::raw('c.price as final_price'),
            ]);

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Tanggal', 'Nama Penyewa', 'Nomor HP', 'Jemput - Tujuan', 'Total']);
                foreach ($rows as $row) {
                    $rute = ($row->pickup_point ?? '-').' - '.($row->drop_point ?? '-');
                    fputcsv($out, [
                        $row->tanggal,
                        $row->name,
                        $row->phone,
                        $rute,
                        (float) $row->final_price,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $query = DB::table('bookings as b')
            ->whereBetween('b.tanggal', [$from, $to])
            ->orderByDesc('b.tanggal');
        $this->applyNotCanceledFilter($query, 'b.status');
        $this->applyRouteScopeToQuery($query, '', 'b.rute', $poolId);
        $this->applyTenantScopeIfExists($query, 'bookings', 'b');
        $this->applyResolvedRouteFilter(
            $query,
            $routeFilter,
            Schema::hasColumn('bookings', 'route_id') ? 'b.route_id' : '',
            'b.rute',
        );

        $rows = $query->get([
            'b.tanggal',
            'b.name',
            'b.phone',
            'b.rute',
            DB::raw('COALESCE(b.discount, 0) as discount'),
            DB::raw('(COALESCE(b.price, 0) - COALESCE(b.discount, 0)) as final_price'),
        ]);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Nama Penumpang', 'Nomor HP', 'Rute', 'Potongan', 'Total']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->tanggal,
                    $row->name,
                    $row->phone,
                    $row->rute,
                    (float) $row->discount,
                    (float) $row->final_price,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function chartersIndex(Request $request): JsonResponse
    {
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $scope = strtolower(trim((string) $request->query('scope', 'active')));
        $paymentStatus = trim((string) $request->query('payment_status', ''));
        $bopStatus = trim((string) $request->query('bop_status', ''));
        $unitId = (int) $request->query('unit_id', 0);
        $armadaId = (int) $request->query('armada_id', 0);
        [$page, $perPage] = $this->paginationParams($request);
        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
        $hasMasterCarterIdColumn = $this->chartersHasMasterCarterIdColumn();
        $canJoinArmadas = $hasArmadaIdColumn && Schema::hasTable('armadas');

        $query = DB::table('charters as c')
            ->leftJoin('units as u', 'c.unit_id', '=', 'u.id');

        if ($canJoinArmadas) {
            $query->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
        }

        $select = [
            'c.id',
            'c.name',
            'c.company_name',
            'c.phone',
            'c.start_date',
            'c.end_date',
            'c.departure_time',
            'c.pickup_point',
            'c.drop_point',
            'c.unit_id',
            'c.driver_name',
            'c.price',
            'c.layanan',
            'c.bop_price',
            'c.bop_status',
            'c.down_payment',
            'c.payment_status',
            'c.created_at',
            DB::raw($hasStatusColumn ? 'c.status as status' : "CASE WHEN c.payment_status = 'Canceled' THEN 'canceled' WHEN c.bop_status = 'done' THEN 'done' ELSE 'active' END as status"),
            DB::raw('u.nopol as unit_nopol'),
            DB::raw('u.category as unit_category'),
        ];

        $select[] = $hasPoolIdColumn ? 'c.pool_id' : DB::raw('NULL as pool_id');
        $select[] = $hasMasterCarterIdColumn ? 'c.master_carter_id' : DB::raw('NULL as master_carter_id');

        if ($hasArmadaIdColumn) {
            $select[] = 'c.armada_id';
        } else {
            $select[] = DB::raw('NULL as armada_id');
        }

        if ($hasArmadaNopolColumn && $canJoinArmadas) {
            $select[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
        } elseif ($hasArmadaNopolColumn) {
            $select[] = 'c.armada_nopol';
        } elseif ($canJoinArmadas) {
            $select[] = DB::raw('a.nopol as armada_nopol');
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $query->select($select);

        if ($from !== '' && $to !== '') {
            $query->whereBetween('c.start_date', [$from, $to]);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike, $hasArmadaNopolColumn, $canJoinArmadas) {
                $builder
                    ->where('c.name', 'like', $qLike)
                    ->orWhere('c.phone', 'like', $qLike)
                    ->orWhere('c.driver_name', 'like', $qLike)
                    ->orWhere('c.pickup_point', 'like', $qLike)
                    ->orWhere('c.drop_point', 'like', $qLike)
                    ->orWhere('u.nopol', 'like', $qLike)
                    ->orWhere('u.category', 'like', $qLike);

                if ($hasArmadaNopolColumn) {
                    $builder->orWhere('c.armada_nopol', 'like', $qLike);
                }

                if ($canJoinArmadas) {
                    $builder
                        ->orWhere('a.nopol', 'like', $qLike)
                        ->orWhere('a.kategori', 'like', $qLike)
                        ->orWhere('a.merk', 'like', $qLike);
                }
            });
        }
        if ($paymentStatus !== '') {
            $query->where('c.payment_status', $paymentStatus);
        }
        if ($bopStatus !== '') {
            $query->where('c.bop_status', $bopStatus);
        }
        if ($unitId > 0) {
            $query->where('c.unit_id', $unitId);
        }
        if ($armadaId > 0 && $hasArmadaIdColumn) {
            $query->where('c.armada_id', $armadaId);
        }
        if ($status !== '' && $hasStatusColumn) {
            $query->where('c.status', $status);
        }
        if ($scope === 'history') {
            if ($hasStatusColumn) {
                $query->where('c.status', 'done');
            } else {
                $query->where('c.bop_status', 'done');
            }
        } elseif ($scope === 'active') {
            if ($hasStatusColumn) {
                $query->where('c.status', '!=', 'done');
            } else {
                $query->where(function (Builder $builder) {
                    $builder->whereNull('c.bop_status')->orWhere('c.bop_status', '!=', 'done');
                });
            }
        }
        $this->applyCharterPoolScope($query);
        $this->applyTenantScopeIfExists($query, 'charters', 'c');
        $this->orderChartersByNearestDeparture($query, $scope);

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'charters' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    private function orderChartersByNearestDeparture(Builder $query, string $scope): void
    {
        if ($scope === 'history') {
            $query
                ->orderByDesc('c.start_date')
                ->orderByRaw('c.departure_time IS NULL')
                ->orderByDesc('c.departure_time')
                ->orderByDesc('c.id');

            return;
        }

        $today = Carbon::today()->toDateString();
        $driver = DB::connection()->getDriverName();

        $distanceExpression = match ($driver) {
            'pgsql' => 'ABS(c.start_date::date - ?::date)',
            'sqlite' => 'ABS(julianday(c.start_date) - julianday(?))',
            'mysql', 'mariadb' => 'ABS(DATEDIFF(c.start_date, ?))',
            default => null,
        };

        if ($distanceExpression !== null) {
            $query
                ->orderByRaw($distanceExpression.' ASC', [$today])
                ->orderByRaw('CASE WHEN c.start_date >= ? THEN 0 ELSE 1 END', [$today])
                ->orderBy('c.start_date')
                ->orderByRaw('c.departure_time IS NULL')
                ->orderBy('c.departure_time')
                ->orderBy('c.id');

            return;
        }

        $query
            ->orderByRaw('CASE WHEN c.start_date >= ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN c.start_date >= ? THEN c.start_date ELSE NULL END ASC', [$today])
            ->orderByRaw('CASE WHEN c.start_date < ? THEN c.start_date ELSE NULL END DESC', [$today])
            ->orderByRaw('c.departure_time IS NULL')
            ->orderBy('c.departure_time')
            ->orderBy('c.id');
    }

    public function chartersShow(int $id): JsonResponse
    {
        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
        $hasMasterCarterIdColumn = $this->chartersHasMasterCarterIdColumn();
        $canJoinArmadas = $hasArmadaIdColumn && Schema::hasTable('armadas');

        $query = DB::table('charters as c')
            ->leftJoin('units as u', 'c.unit_id', '=', 'u.id')
            ->where('c.id', $id);

        if ($canJoinArmadas) {
            $query->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
        }
        $this->applyCharterPoolScope($query);
        $this->applyTenantScopeIfExists($query, 'charters', 'c');

        $select = [
            'c.id',
            'c.name',
            'c.company_name',
            'c.phone',
            'c.start_date',
            'c.end_date',
            'c.departure_time',
            'c.pickup_point',
            'c.drop_point',
            'c.unit_id',
            'c.driver_name',
            'c.price',
            'c.layanan',
            'c.bop_price',
            'c.bop_status',
            'c.down_payment',
            'c.payment_status',
            'c.created_at',
            DB::raw($hasStatusColumn ? 'c.status as status' : "CASE WHEN c.payment_status = 'Canceled' THEN 'canceled' WHEN c.bop_status = 'done' THEN 'done' ELSE 'active' END as status"),
            DB::raw('u.nopol as unit_nopol'),
            DB::raw('u.category as unit_category'),
        ];

        $select[] = $hasPoolIdColumn ? 'c.pool_id' : DB::raw('NULL as pool_id');
        $select[] = $hasMasterCarterIdColumn ? 'c.master_carter_id' : DB::raw('NULL as master_carter_id');

        if ($hasArmadaIdColumn) {
            $select[] = 'c.armada_id';
        } else {
            $select[] = DB::raw('NULL as armada_id');
        }

        if ($hasArmadaNopolColumn && $canJoinArmadas) {
            $select[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
        } elseif ($hasArmadaNopolColumn) {
            $select[] = 'c.armada_nopol';
        } elseif ($canJoinArmadas) {
            $select[] = DB::raw('a.nopol as armada_nopol');
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $row = $query->select($select)->first();

        if (! $row) {
            return $this->error('Charter not found.', 404);
        }

        return $this->ok(['charter' => $row]);
    }

    public function chartersSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
            'master_carter_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'pickup_point' => ['nullable', 'string', 'max:180'],
            'drop_point' => ['nullable', 'string', 'max:180'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'armada_id' => ['nullable', 'integer', 'min:1'],
            'armada_nopol' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'layanan' => ['nullable', 'string', 'max:120'],
            'bop_price' => ['nullable', 'numeric', 'min:0'],
            'bop_status' => ['nullable', 'string', 'max:30'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['active', 'canceled', 'done'])],
        ]);

        $id = (int) ($data['id'] ?? 0);

        // Starter plan: max 3 charters per month
        if ($id <= 0 && FeatureGate::enabled()) {
            $charterPlan = FeatureGate::currentPlan();
            if ($charterPlan && $charterPlan->plan_slug === 'starter') {
                $thisMonthCount = (int) DB::table('charters')
                    ->whereBetween('start_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->count();
                if ($thisMonthCount >= 3) {
                    return $this->error('Paket Starter hanya bisa membuat 3 carter per bulan. Silakan upgrade ke Pro.', 403);
                }
            }
        }

        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
        $hasMasterCarterIdColumn = $this->chartersHasMasterCarterIdColumn();
        $before = [];
        if ($id > 0) {
            $existingQuery = DB::table('charters')->where('id', $id);
            $this->applyTenantScopeIfExists($existingQuery, 'charters');
            $existing = $existingQuery->first();
            if (! $existing) {
                return $this->error('Charter not found.', 404);
            }
            $before = (array) $existing;

            if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['pickup_point'] ?? '', $before['drop_point'] ?? ''])) {
                return $this->error('Anda tidak memiliki akses ke data charter ini.', 403);
            }

            $isDone = $hasStatusColumn
                ? strtolower(trim((string) ($existing->status ?? ''))) === 'done'
                : strtolower(trim((string) ($existing->bop_status ?? ''))) === 'done';
            if ($isDone) {
                return $this->error('Charter selesai tidak dapat diedit lagi.', 409);
            }
        }

        $unitId = isset($data['unit_id']) ? (int) $data['unit_id'] : 0;
        $selectedUnit = null;
        $selectedUnitCategory = '';
        if ($unitId > 0) {
            $unitQuery = DB::table('units')
                ->select(['id', 'nopol', 'category'])
                ->where('id', $unitId);
            $this->applyWriteTenantScopeIfExists($unitQuery, 'units');
            $this->applyPoolScopeIfExists($unitQuery, 'units');
            $selectedUnit = $unitQuery->first();

            if (! $selectedUnit) {
                return $this->error('Kategori armada tidak ditemukan.', 422);
            }

            $selectedUnitCategory = trim((string) ($selectedUnit->category ?? ''));
        }

        $armadaId = (int) ($data['armada_id'] ?? 0);
        $requestedArmadaNopol = strtoupper(trim((string) ($data['armada_nopol'] ?? '')));
        $armadaNopol = $requestedArmadaNopol !== '' ? $requestedArmadaNopol : null;

        if (Schema::hasTable('armadas')) {
            $matchedArmada = null;

            if ($armadaId > 0) {
                $armadaQuery = DB::table('armadas')
                    ->select(['id', 'nopol', 'kategori'])
                    ->where('id', $armadaId);
                $this->applyWriteTenantScopeIfExists($armadaQuery, 'armadas');
                $this->applyPoolScopeIfExists($armadaQuery, 'armadas');
                $matchedArmada = $armadaQuery->first();

                if (! $matchedArmada) {
                    return $this->error('Armada tidak ditemukan.', 422);
                }
            } elseif ($requestedArmadaNopol !== '') {
                $armadaQuery = DB::table('armadas')
                    ->select(['id', 'nopol', 'kategori'])
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol]);
                $this->applyWriteTenantScopeIfExists($armadaQuery, 'armadas');
                $this->applyPoolScopeIfExists($armadaQuery, 'armadas');
                $matchedArmada = $armadaQuery->first();
            }

            if ($matchedArmada) {
                $armadaId = (int) $matchedArmada->id;
                $armadaNopol = strtoupper(trim((string) ($matchedArmada->nopol ?? '')));
                $armadaCategory = trim((string) ($matchedArmada->kategori ?? ''));

                if (
                    $selectedUnitCategory !== ''
                    && $armadaCategory !== ''
                    && strcasecmp($selectedUnitCategory, $armadaCategory) !== 0
                ) {
                    return $this->error('Nopol tidak sesuai kategori armada yang dipilih.', 422);
                }
            }
        }

        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'company_name' => $this->nullable($data['company_name'] ?? null),
            'phone' => $this->nullable($data['phone'] ?? null),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'departure_time' => isset($data['departure_time']) && $data['departure_time'] !== '' ? $data['departure_time'].':00' : null,
            'pickup_point' => $this->nullable($data['pickup_point'] ?? null),
            'drop_point' => $this->nullable($data['drop_point'] ?? null),
            'unit_id' => $unitId > 0 ? $unitId : null,
            'driver_name' => $this->nullable($data['driver_name'] ?? null),
            'price' => (float) ($data['price'] ?? 0),
            'layanan' => $this->nullable($data['layanan'] ?? null) ?? 'Regular',
            'bop_price' => (float) ($data['bop_price'] ?? 0),
            'bop_status' => $this->nullable($data['bop_status'] ?? null) ?? 'pending',
            'down_payment' => (float) ($data['down_payment'] ?? 0),
            'payment_status' => $this->nullable($data['payment_status'] ?? null) ?? 'Belum Bayar',
        ];

        if ($hasPoolIdColumn) {
            $poolId = $this->resolveTransactionPoolId(
                (int) ($data['pool_id'] ?? 0),
                0,
                [$payload['pickup_point'] ?? '', $payload['drop_point'] ?? ''],
                (int) ($before['pool_id'] ?? 0),
            );

            if ($poolId < 0) {
                return $this->error($this->transactionPoolErrorMessage($poolId), 422);
            }

            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        $masterCarterId = (int) ($data['master_carter_id'] ?? 0);
        if ($masterCarterId > 0) {
            if (! Schema::hasTable('master_carter')) {
                return $this->error('Master Carter tidak ditemukan.', 422);
            }

            $masterCarterQuery = DB::table('master_carter')->where('id', $masterCarterId);
            $this->applyWriteTenantScopeIfExists($masterCarterQuery, 'master_carter');
            $this->applyPoolScopeIfExists($masterCarterQuery, 'master_carter', '', $poolId ?? 0);
            if (! $masterCarterQuery->exists()) {
                return $this->error('Master Carter tidak ditemukan.', 422);
            }
        }

        if ($hasMasterCarterIdColumn) {
            $payload['master_carter_id'] = $masterCarterId > 0 ? $masterCarterId : null;
        }

        if ($hasArmadaIdColumn) {
            $payload['armada_id'] = $armadaId > 0 ? $armadaId : null;
        }

        if ($hasArmadaNopolColumn) {
            $payload['armada_nopol'] = $armadaNopol ? strtoupper(trim((string) $armadaNopol)) : null;
        }

        if ($hasStatusColumn && isset($data['status'])) {
            $payload['status'] = (string) $data['status'];
        }

        try {
            if ($id > 0) {
                $charterUpdate = DB::table('charters')->where('id', $id);
                $this->applyWriteTenantScopeIfExists($charterUpdate, 'charters');
                $charterUpdate->update($payload);
                $this->syncCustomerCharterFromCharterPayload($payload);
                $this->syncMasterCarterFromCharterPayload($payload);
                ActivityLog::write(
                    'CHARTER',
                    'Carter '.$this->charterIdentityLabel($payload + $before).' diperbarui',
                    $this->charterChangeSummary($before, $payload + $before),
                    $this->activityActor(),
                    ['charter_id' => $id],
                );

                return $this->ok(['message' => 'Charter updated.', 'id' => $id]);
            }

            if ($hasStatusColumn && ! isset($payload['status'])) {
                $payload['status'] = 'active';
            }
            $payload = array_merge($payload, $this->tenantPayload('charters'));

            $newId = DB::table('charters')->insertGetId(array_merge($payload, ['created_at' => now()]));
            $this->syncCustomerCharterFromCharterPayload($payload);
            $this->syncMasterCarterFromCharterPayload($payload);
            ActivityLog::write(
                'CHARTER',
                'Carter baru: '.$this->charterIdentityLabel($payload),
                $this->charterCreateSummary($payload),
                $this->activityActor(),
                ['charter_id' => $newId],
            );

            return $this->ok(['message' => 'Charter created.', 'id' => $newId], 201);
        } catch (QueryException $e) {
            return $this->error('Gagal menyimpan data carter. Periksa relasi pool, armada, atau customer terkait.', 422, [
                'detail' => $e->getMessage(),
            ]);
        }
    }

    public function chartersDelete(int $id): JsonResponse
    {
        $charterQuery = DB::table('charters')->where('id', $id);
        $this->applyTenantScopeIfExists($charterQuery, 'charters');
        $before = (array) ($charterQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Charter not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['pickup_point'] ?? '', $before['drop_point'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data charter ini.', 403);
        }

        if ($this->chartersHasStatusColumn()) {
            $charterUpdate = DB::table('charters')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($charterUpdate, 'charters');
            $updated = $charterUpdate->update(['status' => 'canceled']);
            if ($updated === 0) {
                return $this->error('Charter not found.', 404);
            }

            ActivityLog::write(
                'CHARTER',
                'Carter '.$this->charterIdentityLabel($before).' dibatalkan',
                $this->buildSummaryParts([
                    $this->formatChangeLine('Status', (string) ($before['status'] ?? 'active'), 'canceled'),
                    $this->charterTripSummary($before),
                ]),
                $this->activityActor(),
                ['charter_id' => $id],
            );

            return $this->ok(['message' => 'Charter canceled.', 'updated' => $updated]);
        }

        $charterUpdate = DB::table('charters')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($charterUpdate, 'charters');
        $updated = $charterUpdate->update(['payment_status' => 'Canceled']);
        if ($updated === 0) {
            return $this->error('Charter not found.', 404);
        }

        ActivityLog::write(
            'CHARTER',
            'Carter '.$this->charterIdentityLabel($before).' dibatalkan',
            $this->buildSummaryParts([
                $this->formatChangeLine('Pembayaran', (string) ($before['payment_status'] ?? ''), 'Canceled'),
                $this->charterTripSummary($before),
            ]),
            $this->activityActor(),
            ['charter_id' => $id],
        );

        return $this->ok(['message' => 'Charter canceled.', 'updated' => $updated]);
    }

    public function chartersBulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);
        $ids = collect($data['ids'])->map(static fn ($value) => (int) $value)->unique()->values()->all();
        $allowedIds = $this->accessibleCharterIds($ids);
        if (count($allowedIds) !== count($ids)) {
            return $this->error('Ada data charter di luar akses pool user.', 403);
        }

        if ($this->chartersHasStatusColumn()) {
            $updated = DB::table('charters')
                ->whereIn('id', $allowedIds)
                ->update(['status' => 'canceled']);

            return $this->ok(['message' => 'Bulk cancel charters done.', 'updated' => $updated]);
        }

        $updated = DB::table('charters')
            ->whereIn('id', $allowedIds)
            ->update(['payment_status' => 'Canceled']);

        return $this->ok(['message' => 'Bulk cancel charters done.', 'updated' => $updated]);
    }

    public function chartersMarkBopDone(int $id): JsonResponse
    {
        $before = (array) (DB::table('charters')->where('id', $id)->first() ?? []);
        if ($before === []) {
            return $this->error('Charter not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['pickup_point'] ?? '', $before['drop_point'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data charter ini.', 403);
        }
        $updated = DB::table('charters')->where('id', $id)->update(['bop_status' => 'done']);
        if ($updated === 0) {
            return $this->error('Charter not found.', 404);
        }
        ActivityLog::write(
            'CHARTER',
            'BOP carter '.$this->charterIdentityLabel($before).' diselesaikan',
            $this->buildSummaryParts([
                $this->formatChangeLine('BOP', (string) ($before['bop_status'] ?? 'pending'), 'done'),
                $this->charterTripSummary($before),
            ]),
            $this->activityActor(),
            ['charter_id' => $id],
        );

        return $this->ok(['message' => 'BOP marked done.', 'id' => $id]);
    }

    public function chartersMarkPaid(int $id): JsonResponse
    {
        $before = (array) (DB::table('charters')->where('id', $id)->first() ?? []);
        if ($before === []) {
            return $this->error('Charter not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['pickup_point'] ?? '', $before['drop_point'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data charter ini.', 403);
        }
        $updated = DB::table('charters')->where('id', $id)->update(['payment_status' => 'Lunas']);
        if ($updated === 0) {
            return $this->error('Charter not found.', 404);
        }
        ActivityLog::write(
            'PAYMENT',
            'Pembayaran carter '.$this->charterIdentityLabel($before).' diperbarui',
            $this->buildSummaryParts([
                $this->formatChangeLine('Pembayaran', (string) ($before['payment_status'] ?? 'Belum Bayar'), 'Lunas'),
                $this->charterTripSummary($before),
            ]),
            $this->activityActor(),
            ['charter_id' => $id],
        );

        return $this->ok(['message' => 'Charter payment marked Lunas.', 'id' => $id]);
    }

    public function chartersMarkDone(int $id): JsonResponse
    {
        $row = DB::table('charters')
            ->where('id', $id)
            ->first();
        if (! $row) {
            return $this->error('Charter not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($row->pool_id ?? 0), [$row->pickup_point ?? '', $row->drop_point ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data charter ini.', 403);
        }

        $paymentStatus = strtolower(trim((string) ($row->payment_status ?? '')));
        if ($paymentStatus !== 'lunas') {
            return $this->error('Charter belum lunas. Tandai lunas terlebih dahulu.', 422);
        }

        if ($this->chartersHasStatusColumn()) {
            $currentStatus = strtolower(trim((string) ($row->status ?? 'active')));
            if ($currentStatus === 'canceled') {
                return $this->error('Charter yang sudah cancel tidak bisa diselesaikan.', 409);
            }

            DB::table('charters')->where('id', $id)->update(['status' => 'done']);
            ActivityLog::write(
                'CHARTER',
                'Perjalanan carter '.$this->charterIdentityLabel((array) $row).' diselesaikan',
                $this->formatChangeLine('Status', (string) ($row->status ?? 'active'), 'done'),
                $this->activityActor(),
                ['charter_id' => $id],
            );

            return $this->ok(['message' => 'Charter marked done.', 'id' => $id]);
        }

        DB::table('charters')->where('id', $id)->update(['bop_status' => 'done']);
        ActivityLog::write(
            'CHARTER',
            'Perjalanan carter '.$this->charterIdentityLabel((array) $row).' diselesaikan',
            $this->formatChangeLine('BOP', (string) ($row->bop_status ?? 'pending'), 'done'),
            $this->activityActor(),
            ['charter_id' => $id],
        );

        return $this->ok(['message' => 'Charter marked done.', 'id' => $id]);
    }

    public function luggagesIndex(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paginationParams($request);
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $status = trim((string) $request->query('status', ''));
        $paymentStatus = trim((string) $request->query('payment_status', ''));
        $q = trim((string) $request->query('q', ''));
        $hasRoutesTable = Schema::hasTable('routes');
        $hasTripAssignmentLink = Schema::hasColumn('luggages', 'trip_assignment_id') && Schema::hasTable('trip_assignments');
        $canJoinDrivers = $hasTripAssignmentLink && Schema::hasTable('drivers');
        $canJoinArmadas = $hasTripAssignmentLink && $this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas');
        $hasTripAssignmentArmadaNopol = $hasTripAssignmentLink && $this->tripAssignmentsHasArmadaNopol();

        $query = DB::table('luggages as l')
            ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id');

        if ($hasRoutesTable) {
            $query->leftJoin('routes as r', 'l.rute_id', '=', 'r.id');
        }

        if ($hasTripAssignmentLink) {
            $query->leftJoin('trip_assignments as t', 'l.trip_assignment_id', '=', 't.id');
        }

        if ($canJoinDrivers) {
            $query->leftJoin('drivers as d', 't.driver_id', '=', 'd.id');
        }

        if ($canJoinArmadas) {
            $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
        }

        $query
            ->select([
                'l.id',
                $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : DB::raw('NULL as pool_id'),
                'l.sender_name',
                'l.sender_phone',
                'l.sender_address',
                'l.receiver_name',
                'l.receiver_phone',
                'l.receiver_address',
                'l.service_id',
                'l.rute_id',
                'l.rute',
                'l.tanggal',
                'l.unit_id',
                $hasTripAssignmentLink ? 'l.trip_assignment_id' : DB::raw('NULL as trip_assignment_id'),
                'l.pengirim_id',
                'l.penerima_id',
                'l.quantity',
                'l.notes',
                'l.price',
                'l.status',
                'l.payment_status',
                'l.kode_resi',
                'l.created_at',
                DB::raw('s.name as service_name'),
                $hasRoutesTable ? DB::raw('r.name as route_name') : DB::raw('NULL as route_name'),
                $hasTripAssignmentLink ? DB::raw('t.tanggal as departure_date') : DB::raw('NULL as departure_date'),
                $hasTripAssignmentLink ? DB::raw('t.jam as departure_time') : DB::raw('NULL as departure_time'),
                $hasTripAssignmentLink ? DB::raw('t.unit as departure_unit') : DB::raw('NULL as departure_unit'),
                $canJoinDrivers ? DB::raw('d.nama as departure_driver_name') : DB::raw('NULL as departure_driver_name'),
            ])
            ->orderByDesc('l.id');

        if ($hasTripAssignmentArmadaNopol && $canJoinArmadas) {
            $query->addSelect(DB::raw('COALESCE(t.armada_nopol, a.nopol) as departure_armada_nopol'));
        } elseif ($hasTripAssignmentArmadaNopol) {
            $query->addSelect(DB::raw('t.armada_nopol as departure_armada_nopol'));
        } elseif ($canJoinArmadas) {
            $query->addSelect(DB::raw('a.nopol as departure_armada_nopol'));
        } else {
            $query->addSelect(DB::raw('NULL as departure_armada_nopol'));
        }

        if ($from !== '' && $to !== '') {
            [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);
            $query->whereBetween('l.created_at', [$createdFrom, $createdTo]);
        }
        if ($status !== '') {
            $this->applyLuggageStatusFilter($query, 'l.status', $this->luggageStatusAliases($status));
        }
        if ($paymentStatus !== '') {
            $query->where('l.payment_status', $paymentStatus);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike, $hasRoutesTable, $canJoinDrivers, $hasTripAssignmentArmadaNopol, $canJoinArmadas) {
                $builder
                    ->where('l.sender_name', 'like', $qLike)
                    ->orWhere('l.sender_phone', 'like', $qLike)
                    ->orWhere('l.receiver_name', 'like', $qLike)
                    ->orWhere('l.receiver_phone', 'like', $qLike)
                    ->orWhere('l.kode_resi', 'like', $qLike)
                    ->orWhere('l.notes', 'like', $qLike)
                    ->orWhere('l.rute', 'like', $qLike)
                    ->orWhere('l.tanggal', 'like', $qLike)
                    ->orWhere('s.name', 'like', $qLike);

                if ($hasRoutesTable) {
                    $builder->orWhere('r.name', 'like', $qLike);
                }

                if ($canJoinDrivers) {
                    $builder->orWhere('d.nama', 'like', $qLike);
                }

                if ($hasTripAssignmentArmadaNopol) {
                    $builder->orWhere('t.armada_nopol', 'like', $qLike);
                }

                if ($canJoinArmadas) {
                    $builder->orWhere('a.nopol', 'like', $qLike);
                }
            });
        }
        $this->applyPoolOrRouteScopeToQuery(
            $query,
            $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
            Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
            'l.rute',
        );
        $this->applyTenantScopeIfExists($query, 'luggages', 'l');

        $result = $this->paginateQuery($query, $page, $perPage);
        $rows = collect($result['data'])
            ->map(function ($row) {
                $item = (array) $row;
                $item['status'] = $this->normalizeLuggageStatus($item['status'] ?? null);
                $item['route_name'] = trim((string) ($item['route_name'] ?? '')) !== ''
                    ? (string) $item['route_name']
                    : (string) ($item['rute'] ?? '');

                return $item;
            })
            ->values()
            ->all();

        return $this->ok([
            'luggages' => $rows,
            'pagination' => $result['meta'],
        ]);
    }

    public function luggagesSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
            'sender_name' => ['required', 'string', 'max:120'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'sender_address' => ['nullable', 'string'],
            'receiver_name' => ['required', 'string', 'max:120'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'receiver_address' => ['nullable', 'string'],
            'service_id' => ['nullable', 'integer', 'min:1'],
            'layanan_id' => ['nullable', 'integer', 'min:1'],
            'rute_id' => ['nullable', 'integer', 'min:1'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
            'payment_status' => ['nullable', 'string', 'max:30'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $serviceId = (int) ($data['service_id'] ?? $data['layanan_id'] ?? 0);
        $routeId = (int) ($data['rute_id'] ?? 0);
        $before = [];
        if ($id > 0) {
            $luggageQuery = DB::table('luggages')->where('id', $id);
            $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
            $before = (array) ($luggageQuery->first() ?? []);
        }
        if ($id > 0 && $before === []) {
            return $this->error('Luggage not found.', 404);
        }
        if ($id > 0 && ! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }

        $senderPhone = $this->normalizePhone((string) $data['sender_phone']);
        $receiverPhone = $this->normalizePhone((string) $data['receiver_phone']);
        $senderName = strtoupper(trim((string) $data['sender_name']));
        $receiverName = strtoupper(trim((string) $data['receiver_name']));
        $senderAddress = $this->nullable($data['sender_address'] ?? null);
        $receiverAddress = $this->nullable($data['receiver_address'] ?? null);
        $routeName = '';
        if ($routeId > 0) {
            $routeQuery = DB::table('routes')->where('id', $routeId);
            $this->applyWriteTenantScopeIfExists($routeQuery, 'routes');
            $routeName = (string) ($routeQuery->value('name') ?? '');
            if ($routeName === '') {
                return $this->error('Rute tidak ditemukan.', 422);
            }
        }
        $poolId = 0;

        if ($this->luggagesHasPoolIdColumn()) {
            $poolId = $this->resolveTransactionPoolId(
                (int) ($data['pool_id'] ?? 0),
                $routeId,
                [$routeName],
                (int) ($before['pool_id'] ?? 0),
            );

            if ($poolId < 0) {
                return $this->error($this->transactionPoolErrorMessage($poolId), 422);
            }
        }

        $customerPoolId = $poolId > 0 ? $poolId : PoolScope::customerPoolId($routeId);
        if ($customerPoolId <= 0) {
            $customerPoolId = $this->defaultCustomerPoolId('customer_bagasi');
        }

        $unitId = (int) ($data['unit_id'] ?? 0);
        if ($unitId > 0 && Schema::hasTable('units')) {
            $unitQuery = DB::table('units')->where('id', $unitId);
            $this->applyWriteTenantScopeIfExists($unitQuery, 'units');
            $this->applyPoolScopeIfExists($unitQuery, 'units', '', $poolId > 0 ? $poolId : null);
            if (! $unitQuery->exists()) {
                return $this->error('Kategori armada tidak ditemukan untuk pool aktif.', 422);
            }
        }

        $pengirimId = $this->upsertCustomerBagasi($senderName, $senderPhone, $senderAddress, 'pengirim', $customerPoolId);
        $penerimaId = $this->upsertCustomerBagasi($receiverName, $receiverPhone, $receiverAddress, 'penerima', $customerPoolId);

        $inputPrice = (float) ($data['price'] ?? 0);
        $mappedPrice = $this->resolveMappedLuggagePrice($routeId, $serviceId);
        $resolvedPrice = $inputPrice > 0 ? $inputPrice : $mappedPrice;

        $payload = [
            'sender_name' => $senderName,
            'sender_phone' => $senderPhone,
            'sender_address' => $senderAddress,
            'receiver_name' => $receiverName,
            'receiver_phone' => $receiverPhone,
            'receiver_address' => $receiverAddress,
            'service_id' => $serviceId > 0 ? $serviceId : null,
            'layanan_id' => $serviceId > 0 ? $serviceId : null,
            'quantity' => max(1, (int) ($data['quantity'] ?? 1)),
            'notes' => $this->nullable($data['notes'] ?? null),
            'price' => $resolvedPrice,
            'status' => $this->normalizeLuggageStatus($data['status'] ?? null),
            'payment_status' => $this->nullable($data['payment_status'] ?? null) ?? 'Belum Bayar',
            'rute_id' => $routeId > 0 ? $routeId : null,
            'rute' => $routeName !== '' ? $routeName : null,
            'tanggal' => $data['tanggal'] ?? now()->toDateString(),
            'unit_id' => $unitId > 0 ? $unitId : null,
            'pengirim_id' => $pengirimId > 0 ? $pengirimId : null,
            'penerima_id' => $penerimaId > 0 ? $penerimaId : null,
        ];

        if ($this->luggagesHasPoolIdColumn()) {
            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }
        $payload = array_merge($payload, $this->tenantPayload('luggages'));

        if ($id > 0) {
            $previous = (object) $before;
            $luggageUpdate = DB::table('luggages')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($luggageUpdate, 'luggages');
            $luggageUpdate->update($payload);
            if ($previous) {
                $this->appendLuggageLogByResi(
                    $this->ensureLuggageResi($id),
                    (string) $payload['status'],
                    $this->luggageChangeSummary((array) $previous, $payload + (array) $previous),
                );
            }

            return $this->ok(['message' => 'Luggage updated.', 'id' => $id]);
        }

        $newId = DB::table('luggages')->insertGetId(array_merge($payload, [
            'kode_resi' => $this->nextLuggageResi(),
            'created_at' => now(),
        ]));
        $resi = $this->ensureLuggageResi($newId);
        $this->appendLuggageLogByResi($resi, (string) $payload['status'], $this->luggageCreateSummary($payload));

        return $this->ok(['message' => 'Luggage created.', 'id' => $newId], 201);
    }

    public function luggagesDelete(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $before = (array) ($luggageQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }

        $luggageDelete = DB::table('luggages')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($luggageDelete, 'luggages');
        $luggageDelete->delete();

        return $this->ok(['message' => 'Luggage deleted.']);
    }

    public function luggagesBulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);
        $ids = collect($data['ids'])->map(static fn ($value) => (int) $value)->unique()->values()->all();
        $allowedIds = $this->accessibleLuggageIds($ids);
        if (count($allowedIds) !== count($ids)) {
            return $this->error('Ada data bagasi di luar akses pool user.', 403);
        }

        $deleted = DB::table('luggages')->whereIn('id', $allowedIds)->delete();

        return $this->ok(['message' => 'Bulk delete luggages done.', 'deleted' => $deleted]);
    }

    public function luggagesBulkStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
            'status' => ['required', 'string', 'max:40'],
            'payment_status' => ['nullable', 'string', 'max:30'],
        ]);
        $ids = collect($data['ids'])->map(static fn ($value) => (int) $value)->unique()->values()->all();
        $allowedIds = $this->accessibleLuggageIds($ids);
        if (count($allowedIds) !== count($ids)) {
            return $this->error('Ada data bagasi di luar akses pool user.', 403);
        }

        $payload = ['status' => $this->normalizeLuggageStatus($data['status'] ?? null)];
        if (isset($data['payment_status'])) {
            $payload['payment_status'] = trim((string) $data['payment_status']);
        }

        $beforeRows = DB::table('luggages')
            ->whereIn('id', $allowedIds)
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->id => (array) $row]);

        $updated = DB::table('luggages')
            ->whereIn('id', $allowedIds)
            ->update($payload);

        if ($updated > 0) {
            $rows = DB::table('luggages')->whereIn('id', $allowedIds)->get(['id', 'kode_resi']);
            foreach ($rows as $row) {
                $before = $beforeRows->get((int) $row->id, []);
                $resi = $row->kode_resi ?: $this->ensureLuggageResi((int) $row->id);
                $this->appendLuggageLogByResi(
                    $resi,
                    (string) $payload['status'],
                    $this->buildSummaryParts([
                        $this->luggageChangeSummary($before, $payload + $before),
                        'Update massal',
                    ]),
                );
            }
        }

        return $this->ok(['message' => 'Bulk update luggages done.', 'updated' => $updated]);
    }

    public function luggagesMarkPaid(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $before = (array) ($luggageQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }
        $luggageUpdate = DB::table('luggages')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($luggageUpdate, 'luggages');
        $updated = $luggageUpdate->update(['payment_status' => 'Lunas']);
        if ($updated === 0) {
            return $this->error('Luggage not found.', 404);
        }
        $this->appendLuggageLogByResi(
            $this->ensureLuggageResi($id),
            'payment',
            $this->luggageChangeSummary($before, ['payment_status' => 'Lunas'] + $before),
        );

        return $this->ok(['message' => 'Luggage payment marked Lunas.', 'id' => $id]);
    }

    public function luggagesMarkActive(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $before = (array) ($luggageQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }
        $status = $this->luggagePickedUpStatus();
        $luggageUpdate = DB::table('luggages')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($luggageUpdate, 'luggages');
        $updated = $luggageUpdate->update(['status' => $status]);
        if ($updated === 0) {
            return $this->error('Luggage not found.', 404);
        }
        $this->appendLuggageLogByResi(
            $this->ensureLuggageResi($id),
            $status,
            $this->luggageChangeSummary($before, ['status' => $status] + $before),
        );

        return $this->ok(['message' => 'Luggage status marked pickup.', 'id' => $id]);
    }

    public function luggagesMarkDone(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $before = (array) ($luggageQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }
        $status = $this->luggageArrivedStatus();
        $luggageUpdate = DB::table('luggages')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($luggageUpdate, 'luggages');
        $updated = $luggageUpdate->update(['status' => $status]);
        if ($updated === 0) {
            return $this->error('Luggage not found.', 404);
        }
        $this->appendLuggageLogByResi(
            $this->ensureLuggageResi($id),
            $status,
            $this->luggageChangeSummary($before, ['status' => $status] + $before),
        );

        return $this->ok(['message' => 'Luggage status marked arrived.', 'id' => $id]);
    }

    public function luggagesMarkCanceled(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $before = (array) ($luggageQuery->first() ?? []);
        if ($before === []) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($before['pool_id'] ?? 0), [$before['rute'] ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }
        $luggageUpdate = DB::table('luggages')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($luggageUpdate, 'luggages');
        $updated = $luggageUpdate->update(['status' => 'canceled']);
        if ($updated === 0) {
            return $this->error('Luggage not found.', 404);
        }
        $this->appendLuggageLogByResi(
            $this->ensureLuggageResi($id),
            'canceled',
            $this->buildSummaryParts([
                $this->luggageChangeSummary($before, ['status' => 'canceled'] + $before),
                'Alasan: Pengiriman bagasi dibatalkan',
            ]),
        );

        return $this->ok(['message' => 'Luggage status marked canceled.', 'id' => $id]);
    }

    public function luggagesTracking(int $id): JsonResponse
    {
        $luggageQuery = DB::table('luggages')
            ->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $luggage = $luggageQuery->first([
            'id',
            $this->luggagesHasPoolIdColumn() ? 'pool_id' : DB::raw('NULL as pool_id'),
            Schema::hasColumn('luggages', 'tenant_id') ? 'tenant_id' : DB::raw('NULL as tenant_id'),
            'rute',
            'kode_resi',
            'sender_name',
            'receiver_name',
            'status',
            'payment_status',
        ]);

        if (! $luggage) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($luggage->pool_id ?? 0), [$luggage->rute ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }

        $resi = $luggage->kode_resi ?: $this->ensureLuggageResi($id);
        $logs = DB::table('bagasi_logs')
            ->where('kode_resi', $resi);
        if (Schema::hasColumn('bagasi_logs', 'tenant_id') && (int) ($luggage->tenant_id ?? 0) > 0) {
            $logs->where('tenant_id', (int) $luggage->tenant_id);
        } else {
            $this->applyTenantScopeIfExists($logs, 'bagasi_logs');
        }

        $logs = $logs
            ->orderByDesc('id')
            ->get(['id', 'kode_resi', 'status', 'notes', 'created_by_username', 'created_at'])
            ->map(function ($row) {
                $row->status = $this->normalizeLuggageStatus($row->status ?? null);

                return $row;
            })
            ->values();

        return $this->ok([
            'luggage' => [
                'id' => (int) $luggage->id,
                'kode_resi' => $luggage->kode_resi,
                'sender_name' => $luggage->sender_name,
                'receiver_name' => $luggage->receiver_name,
                'status' => $this->normalizeLuggageStatus($luggage->status ?? null),
                'payment_status' => $luggage->payment_status,
            ],
            'logs' => $logs,
        ]);
    }

    public function luggagesTrackingAdd(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
        ]);

        $luggageQuery = DB::table('luggages')->where('id', $id);
        $this->applyTenantScopeIfExists($luggageQuery, 'luggages');
        $luggage = $luggageQuery->first([
            'id',
            $this->luggagesHasPoolIdColumn() ? 'pool_id' : DB::raw('NULL as pool_id'),
            'rute',
        ]);
        if (! $luggage) {
            return $this->error('Luggage not found.', 404);
        }

        if (! $this->transactionPoolSnapshotAccessible((int) ($luggage->pool_id ?? 0), [$luggage->rute ?? ''])) {
            return $this->error('Anda tidak memiliki akses ke data bagasi ini.', 403);
        }

        $resi = $this->ensureLuggageResi($id);
        $this->appendLuggageLogByResi(
            $resi,
            $this->normalizeLuggageStatus($data['status'] ?? null),
            $this->nullable($data['notes'] ?? null),
        );

        return $this->ok(['message' => 'Tracking log added.', 'id' => $id, 'kode_resi' => $resi]);
    }

    public function assignmentsIndex(Request $request): JsonResponse
    {
        $tanggal = trim((string) $request->query('tanggal', ''));
        $rute = trim((string) $request->query('rute', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('trip_assignments as t')
            ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id');

        if ($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas')) {
            $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
        }

        $select = [
            't.id',
            't.rute',
            't.tanggal',
            't.jam',
            't.unit',
            't.driver_id',
            'd.nama',
            'd.phone',
        ];

        if ($this->tripAssignmentsHasArmadaId()) {
            $select[] = 't.armada_id';
        }

        if ($this->tripAssignmentsHasArmadaNopol() && $this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas')) {
            $select[] = DB::raw('COALESCE(t.armada_nopol, a.nopol) as armada_nopol');
        } elseif ($this->tripAssignmentsHasArmadaNopol()) {
            $select[] = 't.armada_nopol';
        } elseif ($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas')) {
            $select[] = DB::raw('a.nopol as armada_nopol');
        }

        $query = $query
            ->select($select)
            ->orderByDesc('t.tanggal')
            ->orderBy('t.jam');

        if ($tanggal !== '') {
            $query->where('t.tanggal', $tanggal);
        }
        if ($from !== '' && $to !== '') {
            $query->whereBetween('t.tanggal', [$from, $to]);
        }
        if ($rute !== '') {
            $query->where('t.rute', $rute);
        }
        $this->applyRouteScopeToQuery($query, '', 't.rute');

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'assignments' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function assignmentsConflicts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
            'driver_id' => ['required', 'integer', 'min:1'],
        ]);

        $conflicts = $this->assignmentConflicts(
            $data['tanggal'],
            $data['jam'].':00',
            (int) $data['unit'],
            (int) $data['driver_id'],
            (int) ($data['id'] ?? 0),
        );

        return $this->ok([
            'has_conflict' => count($conflicts) > 0,
            'conflicts' => $conflicts,
        ]);
    }

    public function assignmentsSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
            'driver_id' => ['required', 'integer', 'min:1'],
            'armada_id' => ['nullable', 'integer', 'min:1'],
            'armada_nopol' => ['nullable', 'string', 'max:50'],
            'allow_conflict' => ['nullable', 'boolean'],
        ]);

        $armadaId = (int) ($data['armada_id'] ?? 0);
        $requestedArmadaNopol = strtoupper(trim((string) ($data['armada_nopol'] ?? '')));
        $armadaNopol = $requestedArmadaNopol !== '' ? $requestedArmadaNopol : null;
        $driverQuery = DB::table('drivers')->where('id', (int) $data['driver_id']);
        $this->applyWriteTenantScopeIfExists($driverQuery, 'drivers');
        $this->applyPoolScopeIfExists($driverQuery, 'drivers');
        if (! $driverQuery->exists()) {
            if ($this->currentUserIsSuperAdmin()) {
                $legacyDriverQuery = DB::table('drivers')->where('id', (int) $data['driver_id']);
                if ($legacyDriverQuery->exists()) {
                    $driverQuery = $legacyDriverQuery;
                }
            }

            if (! $driverQuery->exists()) {
                return $this->error('Driver tidak ditemukan untuk pool aktif.', 422);
            }
        }

        if (Schema::hasTable('armadas')) {
            if ($armadaId <= 0 && $requestedArmadaNopol !== '') {
                $matchedArmada = DB::table('armadas')
                    ->select('id', 'nopol')
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol]);
                $this->applyWriteTenantScopeIfExists($matchedArmada, 'armadas');
                $this->applyPoolScopeIfExists($matchedArmada, 'armadas');
                $matchedArmada = $matchedArmada->first();

                if (! $matchedArmada && $this->currentUserIsSuperAdmin()) {
                    $matchedArmada = DB::table('armadas')
                        ->select('id', 'nopol')
                        ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol])
                        ->first();
                }

                if ($matchedArmada) {
                    $armadaId = (int) $matchedArmada->id;
                    $armadaNopol = strtoupper(trim((string) $matchedArmada->nopol));
                }
            } elseif ($armadaId > 0) {
                $storedArmada = DB::table('armadas')->where('id', $armadaId);
                $this->applyWriteTenantScopeIfExists($storedArmada, 'armadas');
                $this->applyPoolScopeIfExists($storedArmada, 'armadas');
                $storedNopol = $storedArmada->value('nopol');

                if (! $storedNopol && $this->currentUserIsSuperAdmin()) {
                    $storedNopol = DB::table('armadas')->where('id', $armadaId)->value('nopol');
                }

                if (! $storedNopol) {
                    return $this->error('Armada tidak ditemukan untuk pool aktif.', 422);
                }

                $armadaNopol = strtoupper(trim((string) $storedNopol));
            }
        }

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'rute' => trim((string) $data['rute']),
            'tanggal' => $data['tanggal'],
            'jam' => $data['jam'].':00',
            'unit' => (int) $data['unit'],
            'driver_id' => (int) $data['driver_id'],
        ];

        if ($this->tripAssignmentsHasArmadaId()) {
            $payload['armada_id'] = $armadaId > 0 ? $armadaId : null;
        }

        if ($this->tripAssignmentsHasArmadaNopol()) {
            $payload['armada_nopol'] = $armadaNopol ? strtoupper(trim((string) $armadaNopol)) : null;
        }

        $conflicts = $this->assignmentConflicts(
            $payload['tanggal'],
            $payload['jam'],
            $payload['unit'],
            $payload['driver_id'],
            $id,
        );
        $allowConflict = (bool) ($data['allow_conflict'] ?? false);
        if (count($conflicts) > 0 && ! $allowConflict) {
            return $this->error('assignment_conflict', 409, ['conflicts' => $conflicts]);
        }

        if ($id > 0) {
            DB::table('trip_assignments')->where('id', $id)->update($payload);

            return $this->ok([
                'message' => 'Assignment updated.',
                'id' => $id,
                'armada_id' => $payload['armada_id'] ?? null,
                'armada_nopol' => $payload['armada_nopol'] ?? null,
            ]);
        }

        $existingId = DB::table('trip_assignments')
            ->where('rute', $payload['rute'])
            ->where('tanggal', $payload['tanggal'])
            ->where('jam', $payload['jam'])
            ->where('unit', $payload['unit'])
            ->value('id');

        if ($existingId) {
            $updatePayload = [
                'driver_id' => $payload['driver_id'],
            ];
            if ($this->tripAssignmentsHasArmadaId()) {
                $updatePayload['armada_id'] = $payload['armada_id'] ?? null;
            }
            if ($this->tripAssignmentsHasArmadaNopol()) {
                $updatePayload['armada_nopol'] = $payload['armada_nopol'] ?? null;
            }
            DB::table('trip_assignments')->where('id', (int) $existingId)->update($updatePayload);

            return $this->ok([
                'message' => 'Assignment updated by trip.',
                'id' => (int) $existingId,
                'armada_id' => $payload['armada_id'] ?? null,
                'armada_nopol' => $payload['armada_nopol'] ?? null,
            ]);
        }

        $newId = DB::table('trip_assignments')->insertGetId(array_merge($payload, $this->tenantPayload('trip_assignments'), ['created_at' => now()]));

        return $this->ok([
            'message' => 'Assignment created.',
            'id' => $newId,
            'armada_id' => $payload['armada_id'] ?? null,
            'armada_nopol' => $payload['armada_nopol'] ?? null,
        ], 201);
    }

    public function assignmentsDelete(int $id): JsonResponse
    {
        DB::table('trip_assignments')->where('id', $id)->delete();

        return $this->ok(['message' => 'Assignment deleted.']);
    }

    public function assignmentsBulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $deleted = DB::table('trip_assignments')->whereIn('id', $data['ids'])->delete();

        return $this->ok(['message' => 'Bulk delete assignments done.', 'deleted' => $deleted]);
    }

    public function customerBagasiIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('customer_bagasi')
            ->select(['id', 'nama', 'no_hp', 'alamat', 'tipe'])
            ->orderBy('nama');
        PoolScope::applyCustomerBagasiScope($query, 'customer_bagasi');
        $this->applyTenantScopeIfExists($query, 'customer_bagasi');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike) {
                $builder
                    ->where('nama', 'like', $qLike)
                    ->orWhere('no_hp', 'like', $qLike)
                    ->orWhere('alamat', 'like', $qLike)
                    ->orWhere('tipe', 'like', $qLike);
            });
        }

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'customers' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function customerBagasiSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'nama' => ['required', 'string', 'max:120'],
            'no_hp' => ['required', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'tipe' => ['nullable', 'string', 'max:20'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'nama' => strtoupper(trim((string) $data['nama'])),
            'no_hp' => trim((string) $data['no_hp']),
            'alamat' => $this->nullable($data['alamat'] ?? null),
            'tipe' => $this->nullable($data['tipe'] ?? null) ?? 'pengirim',
        ];
        $poolId = $this->defaultCustomerPoolId('customer_bagasi');

        if ($id > 0) {
            $customerUpdate = DB::table('customer_bagasi')->where('id', $id);
            PoolScope::applyCustomerBagasiScope($customerUpdate, 'customer_bagasi');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_bagasi');
            if (! $customerUpdate->exists()) {
                return $this->error('Customer bagasi not found.', 404);
            }

            $customerUpdate->update($payload);
            $this->assignCustomerPoolIfMissing($id, $poolId, 'customer_bagasi');

            return $this->ok(['message' => 'Customer bagasi updated.', 'id' => $id]);
        }

        $existingQuery = DB::table('customer_bagasi')->where('no_hp', $payload['no_hp']);
        PoolScope::applyCustomerBagasiScope($existingQuery, 'customer_bagasi');
        $this->applyWriteTenantScopeIfExists($existingQuery, 'customer_bagasi');
        $existingId = (int) ($existingQuery->value('id') ?? 0);
        if ($existingId > 0) {
            $customerUpdate = DB::table('customer_bagasi')->where('id', $existingId);
            PoolScope::applyCustomerBagasiScope($customerUpdate, 'customer_bagasi');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_bagasi');
            $customerUpdate->update($payload);
            $this->assignCustomerPoolIfMissing($existingId, $poolId, 'customer_bagasi');

            return $this->ok(['message' => 'Customer bagasi updated by phone.', 'id' => $existingId]);
        }

        $newId = DB::table('customer_bagasi')->insertGetId(array_merge(
            $payload,
            $poolId > 0 ? ['pool_id' => $poolId] : [],
            $this->tenantPayload('customer_bagasi'),
            ['created_at' => now()],
        ));

        return $this->ok(['message' => 'Customer bagasi created.', 'id' => $newId], 201);
    }

    public function customerBagasiDelete(int $id): JsonResponse
    {
        $customerDelete = DB::table('customer_bagasi')->where('id', $id);
        PoolScope::applyCustomerBagasiScope($customerDelete, 'customer_bagasi');
        $this->applyWriteTenantScopeIfExists($customerDelete, 'customer_bagasi');
        if (! $customerDelete->exists()) {
            return $this->error('Customer bagasi not found.', 404);
        }

        $customerDelete->delete();

        return $this->ok(['message' => 'Customer bagasi deleted.']);
    }

    public function customerCharterIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $qPhone = preg_replace('/\D+/', '', $q) ?? '';
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('customer_charter')
            ->select(['id', 'nama', 'no_hp', 'alamat', 'company'])
            ->orderBy('nama');
        PoolScope::applyCustomerCharterScope($query, 'customer_charter');
        $this->applyTenantScopeIfExists($query, 'customer_charter');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $qLowerLike = '%'.strtolower($q).'%';
            $phoneLike = '%'.$qPhone.'%';
            $query->where(function ($builder) use ($qLike, $qLowerLike, $qPhone, $phoneLike) {
                $builder
                    ->whereRaw("LOWER(COALESCE(nama, '')) LIKE ?", [$qLowerLike])
                    ->orWhere('no_hp', 'like', $qLike)
                    ->orWhereRaw("LOWER(COALESCE(alamat, '')) LIKE ?", [$qLowerLike])
                    ->orWhereRaw("LOWER(COALESCE(company, '')) LIKE ?", [$qLowerLike]);

                if ($qPhone !== '') {
                    $builder->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(no_hp, ''), ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?",
                        [$phoneLike],
                    );
                }
            });
        }

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'customers' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function customerCharterSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'nama' => ['required', 'string', 'max:120'],
            'no_hp' => ['required', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'company' => ['nullable', 'string', 'max:180'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'nama' => strtoupper(trim((string) $data['nama'])),
            'no_hp' => trim((string) $data['no_hp']),
            'alamat' => $this->nullable($data['alamat'] ?? null),
            'company' => $this->nullable($data['company'] ?? null),
        ];
        $poolId = $this->defaultCustomerPoolId('customer_charter');

        if ($id > 0) {
            $customerUpdate = DB::table('customer_charter')->where('id', $id);
            PoolScope::applyCustomerCharterScope($customerUpdate, 'customer_charter');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_charter');
            if (! $customerUpdate->exists()) {
                return $this->error('Customer charter not found.', 404);
            }

            $customerUpdate->update($payload);
            $this->assignCustomerPoolIfMissing($id, $poolId, 'customer_charter');

            return $this->ok(['message' => 'Customer charter updated.', 'id' => $id]);
        }

        $existingQuery = DB::table('customer_charter')->where('no_hp', $payload['no_hp']);
        PoolScope::applyCustomerCharterScope($existingQuery, 'customer_charter');
        $this->applyWriteTenantScopeIfExists($existingQuery, 'customer_charter');
        $existingId = (int) ($existingQuery->value('id') ?? 0);
        if ($existingId > 0) {
            $customerUpdate = DB::table('customer_charter')->where('id', $existingId);
            PoolScope::applyCustomerCharterScope($customerUpdate, 'customer_charter');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_charter');
            $customerUpdate->update($payload);
            $this->assignCustomerPoolIfMissing($existingId, $poolId, 'customer_charter');

            return $this->ok(['message' => 'Customer charter updated by phone.', 'id' => $existingId]);
        }

        $newId = DB::table('customer_charter')->insertGetId(array_merge(
            $payload,
            $poolId > 0 ? ['pool_id' => $poolId] : [],
            $this->tenantPayload('customer_charter'),
            ['created_at' => now()],
        ));

        return $this->ok(['message' => 'Customer charter created.', 'id' => $newId], 201);
    }

    public function customerCharterDelete(int $id): JsonResponse
    {
        $customerDelete = DB::table('customer_charter')->where('id', $id);
        PoolScope::applyCustomerCharterScope($customerDelete, 'customer_charter');
        $this->applyWriteTenantScopeIfExists($customerDelete, 'customer_charter');
        if (! $customerDelete->exists()) {
            return $this->error('Customer charter not found.', 404);
        }

        $customerDelete->delete();

        return $this->ok(['message' => 'Customer charter deleted.']);
    }

    public function charterRoutesMasterIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('master_carter')
            ->select([
                'id',
                'name',
                'origin',
                'destination',
                'duration',
                'rental_price',
                'bop_price',
                'notes',
                'created_at',
            ])
            ->orderBy('name')
            ->orderBy('origin')
            ->orderBy('destination')
            ->when(Schema::hasColumn('master_carter', 'tenant_id'), function (Builder $q) {
                PoolScope::applyTenantScope($q, 'master_carter.tenant_id');
            });
        $this->applyPoolScopeIfExists($query, 'master_carter');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike) {
                $builder
                    ->where('name', 'like', $qLike)
                    ->orWhere('origin', 'like', $qLike)
                    ->orWhere('destination', 'like', $qLike)
                    ->orWhere('duration', 'like', $qLike)
                    ->orWhere('notes', 'like', $qLike);
            });
        }

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'routes' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function charterRoutesMasterSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:180'],
            'origin' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:120'],
            'duration' => ['nullable', 'string', 'max:50'],
            'rental_price' => ['nullable', 'numeric', 'min:0'],
            'bop_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
            'duration' => $this->nullable($data['duration'] ?? null) ?? 'Regular',
            'rental_price' => (float) ($data['rental_price'] ?? 0),
            'bop_price' => (float) ($data['bop_price'] ?? 0),
            'notes' => $this->nullable($data['notes'] ?? null),
        ];

        try {
            if ($id > 0) {
                $routeUpdate = DB::table('master_carter')->where('id', $id);
                $this->applyWriteTenantScopeIfExists($routeUpdate, 'master_carter');
                $this->applyPoolScopeIfExists($routeUpdate, 'master_carter');
                if (! $routeUpdate->exists()) {
                    return $this->error('Rute carter not found.', 404);
                }

                $routeUpdate->update($payload);

                return $this->ok(['message' => 'Rute carter updated.', 'id' => $id]);
            }

            $newId = DB::table('master_carter')->insertGetId(array_merge($payload, $this->tenantPayload('master_carter'), $this->poolPayload('master_carter'), ['created_at' => now()]));

            return $this->ok(['message' => 'Rute carter created.', 'id' => $newId], 201);
        } catch (QueryException $e) {
            return $this->error('Gagal menyimpan master carter.', 422, [
                'detail' => $e->getMessage(),
            ]);
        }
    }

    public function charterRoutesMasterDelete(int $id): JsonResponse
    {
        $routeDelete = DB::table('master_carter')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($routeDelete, 'master_carter');
        $this->applyPoolScopeIfExists($routeDelete, 'master_carter');
        if (! $routeDelete->exists()) {
            return $this->error('Rute carter not found.', 404);
        }

        $routeDelete->delete();

        return $this->ok(['message' => 'Rute carter deleted.']);
    }

    public function unitsIndex(): JsonResponse
    {
        $status = trim((string) request()->query('status', ''));

        $query = DB::table('units')
            ->orderBy('nopol')
            ->select([
                'id',
                'nopol',
                'merek',
                'type',
                'category',
                'tahun',
                'warna',
                'kapasitas',
                'status',
                'layout',
                Schema::hasColumn('units', 'pool_id') ? 'pool_id' : DB::raw('NULL as pool_id'),
            ])
            ->when(Schema::hasColumn('units', 'tenant_id'), function (Builder $q) {
                $tenantId = PoolScope::tenantId();
                if ($tenantId > 0) {
                    $q->where('units.tenant_id', $tenantId);
                }
            });
        $this->applyPoolScopeIfExists($query, 'units');

        if ($status !== '') {
            $query->where('status', $status);
        }

        $rawRows = $query->get();
        $poolNames = $this->poolNameMap($rawRows->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $rows = $rawRows
            ->map(function ($row) use ($poolNames) {
                $row->category = $this->normalizeUnitCategory($row->category ?? null);
                $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
                $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

                return $row;
            })
            ->values();

        return $this->ok(['units' => $rows]);
    }

    public function unitsSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'nopol' => ['required', 'string', 'max:50'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
            'merek' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'string', Rule::in(['Minibus', 'Mediumbus', 'Bigbus', 'Bigbun', 'Micro Bus', 'Microbus'])],
            'tahun' => ['nullable', 'integer', 'min:0', 'max:2100'],
            'warna' => ['nullable', 'string', 'max:120'],
            'kapasitas' => ['nullable', 'integer', 'min:0', 'max:200'],
            'status' => ['nullable', 'string', 'max:20'],
            'layout' => ['nullable', 'string'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $nopol = strtoupper(trim((string) $data['nopol']));
        $existing = null;
        if ($id > 0) {
            $existingQuery = DB::table('units')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($existingQuery, 'units');
            $this->applyPoolScopeIfExists($existingQuery, 'units');
            $existing = $existingQuery->first();

            if (! $existing) {
                return $this->error('Unit tidak ditemukan untuk pool aktif.', 404);
            }
        }

        $targetPoolId = $this->resolveWritablePoolIdFromRequest(
            $request,
            'units',
            (int) ($existing->pool_id ?? 0),
            $id <= 0,
        );
        if ($targetPoolId < 0) {
            return $this->error($this->poolResolveErrorMessage($targetPoolId), $targetPoolId === -1 ? 403 : 422);
        }

        $duplicate = DB::table('units')
            ->whereRaw('UPPER(nopol) = ?', [$nopol])
            ->when(Schema::hasColumn('units', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'tenant_id');
            })
            ->when(Schema::hasColumn('units', 'pool_id') && $targetPoolId > 0, fn (Builder $q) => $q->where('pool_id', $targetPoolId))
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicate) {
            return $this->error('Nopol sudah terdaftar.', 409);
        }

        $payload = [
            'nopol' => $nopol,
            // Preserve legacy fields that are no longer shown in the simplified UI.
            'merek' => $this->nullable($data['merek'] ?? ($existing->merek ?? null)),
            'type' => $this->nullable($data['type'] ?? ($existing->type ?? null)),
            'category' => $this->normalizeUnitCategory($data['category'] ?? null),
            'tahun' => max(0, (int) ($data['tahun'] ?? ($existing->tahun ?? 0))),
            'warna' => $this->nullable($data['warna'] ?? ($existing->warna ?? null)),
            'kapasitas' => (int) ($data['kapasitas'] ?? 0),
            'status' => $this->nullable($data['status'] ?? null) ?? 'Aktif',
            'layout' => $this->nullable($data['layout'] ?? null),
        ];

        if ($id > 0) {
            $query = DB::table('units')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($query, 'units');
            $this->applyPoolScopeIfExists($query, 'units');
            $updated = $query->update(array_merge($payload, $this->poolPayload('units', $targetPoolId > 0 ? $targetPoolId : null)));

            if ($updated === 0) {
                return $this->error('Unit tidak ditemukan untuk pool aktif.', 404);
            }

            return $this->ok(['message' => 'Unit updated.', 'id' => $id]);
        }

        $newId = DB::table('units')->insertGetId(array_merge($payload, $this->tenantPayload('units'), $this->poolPayload('units', $targetPoolId > 0 ? $targetPoolId : null), [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Unit created.', 'id' => $newId], 201);
    }

    private function normalizeUnitCategory(mixed $value): string
    {
        $normalized = strtolower(preg_replace('/\s+/', '', trim((string) $value)) ?? '');

        return match ($normalized) {
            'mediumbus' => 'Mediumbus',
            'bigbus', 'bigbun' => 'Bigbus',
            'minibus' => 'Minibus',
            default => 'Minibus',
        };
    }

    private function hasRoutesBopColumn(): bool
    {
        return $this->routesHasBopColumn ??= Schema::hasTable('routes') && Schema::hasColumn('routes', 'bop');
    }

    private function hasRoutesTargetRevenueColumn(): bool
    {
        return $this->routesHasTargetRevenueColumn ??= Schema::hasTable('routes') && Schema::hasColumn('routes', 'target_revenue');
    }

    private function hasRoutesFixedCostColumn(): bool
    {
        return $this->routesHasFixedCostColumn ??= Schema::hasTable('routes') && Schema::hasColumn('routes', 'fixed_cost');
    }

    private function hasPoolsFixedCostColumn(): bool
    {
        return $this->poolsHasFixedCostColumn ??= Schema::hasTable('pools') && Schema::hasColumn('pools', 'fixed_cost');
    }

    private function hasDriversRevenueColumn(): bool
    {
        return $this->driversHasRevenueColumn ??= Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'revenue');
    }

    private function hasDriversBopColumn(): bool
    {
        return $this->driversHasBopColumn ??= Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'bop');
    }

    private function hasDriversFixedCostColumn(): bool
    {
        return $this->driversHasFixedCostColumn ??= Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'fixed_cost');
    }

    private function hasDriversTargetRevenueBulananColumn(): bool
    {
        return $this->driversHasTargetRevenueBulananColumn ??= Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'target_revenue_bulanan');
    }

    private function hasDriversTargetRevenueTahunanColumn(): bool
    {
        return $this->driversHasTargetRevenueTahunanColumn ??= Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'target_revenue_tahunan');
    }

    public function unitsDelete(int $id): JsonResponse
    {
        $unitQuery = DB::table('units')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($unitQuery, 'units');
        $this->applyPoolScopeIfExists($unitQuery, 'units');
        if (! $unitQuery->exists()) {
            return $this->error('Unit tidak ditemukan untuk pool aktif.', 404);
        }

        DB::table('schedules')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('drivers')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('charters')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('luggages')->where('unit_id', $id)->update(['unit_id' => null]);
        $delete = DB::table('units')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($delete, 'units');
        $this->applyPoolScopeIfExists($delete, 'units');
        $delete->delete();

        return $this->ok(['message' => 'Unit deleted.']);
    }

    public function armadaCategoriesIndex(): JsonResponse
    {
        if (! Schema::hasTable('units')) {
            return $this->ok(['categories' => []]);
        }

        $rows = DB::table('units')
            ->select('category')
            ->when(Schema::hasColumn('units', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'units.tenant_id');
            })
            ->when(Schema::hasColumn('units', 'pool_id'), function (Builder $q): void {
                $this->applyPoolScopeIfExists($q, 'units');
            })
            ->whereNotNull('category')
            ->whereRaw('TRIM(category) <> ?', [''])
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(fn ($item) => $this->normalizeUnitCategory($item))
            ->unique()
            ->values();

        return $this->ok(['categories' => $rows]);
    }

    public function armadasIndex(Request $request): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->ok(['armadas' => []]);
        }

        $q = trim((string) $request->query('q', ''));
        $kategori = trim((string) $request->query('kategori', ''));
        $acType = trim((string) $request->query('ac_type', ''));

        $query = DB::table('armadas')
            ->when(Schema::hasColumn('armadas', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'armadas.tenant_id');
            })
            ->select($this->armadaSelectColumns())
            ->orderBy('nopol');
        $this->applyPoolScopeIfExists($query, 'armadas');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function (Builder $builder) use ($qLike) {
                $builder->where('nopol', 'like', $qLike);

                foreach (['nomor_rangka', 'merk', 'kategori', 'platform_gps'] as $column) {
                    if (Schema::hasColumn('armadas', $column)) {
                        $builder->orWhere($column, 'like', $qLike);
                    }
                }
            });
        }

        if ($kategori !== '' && Schema::hasColumn('armadas', 'kategori')) {
            $query->where('kategori', $kategori);
        }

        if (in_array($acType, ['AC', 'Non-AC'], true) && Schema::hasColumn('armadas', 'ac_type')) {
            $query->where('ac_type', $acType);
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);

        $pagination = null;
        if ($request->boolean('paginate')) {
            [$page, $perPage] = $this->paginationParams($request);
            $result = $this->paginateQuery($query, $page, $perPage);
            $rows = collect($result['data']);
            $pagination = $result['meta'];
        } else {
            $rows = $query->get();
        }

        $poolNames = $this->poolNameMap($rows->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $rows = $rows
            ->map(function ($row) use ($financials, $poolNames) {
                $row = $this->withArmadaFinancials($row, $financials);
                $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
                $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

                return $row;
            })
            ->values();

        return $this->ok([
            'armadas' => $rows,
            ...($pagination !== null ? ['pagination' => $pagination] : []),
        ]);
    }

    private function armadaSelectColumns(): array
    {
        $columnDefaults = [
            'id' => '0',
            'merk' => 'NULL',
            'tahun' => '0',
            'warna' => 'NULL',
            'nopol' => "''",
            'nomor_rangka' => 'NULL',
            'kategori' => 'NULL',
            'ac_type' => "'AC'",
            'platform_gps' => 'NULL',
            'api_gps' => 'NULL',
            'revenue' => '0',
            'bop' => '0',
            'fixed_cost' => '0',
            'target_bulanan' => '0',
            'target_tahunan' => '0',
            'pool_id' => 'NULL',
        ];

        return collect($columnDefaults)
            ->map(static fn (string $default, string $column): mixed => Schema::hasColumn('armadas', $column)
                ? $column
                : DB::raw($default.' as '.$column))
            ->values()
            ->all();
    }

    public function armadasSave(Request $request): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->error('Tabel armada belum tersedia. Jalankan migration terlebih dahulu.', 500);
        }

        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
            'merk' => ['nullable', 'string', 'max:120'],
            'tahun' => ['nullable', 'integer', 'min:0', 'max:2100'],
            'warna' => ['nullable', 'string', 'max:80'],
            'nopol' => ['required', 'string', 'max:50'],
            'nomor_rangka' => ['nullable', 'string', 'max:120'],
            'kategori' => ['required', 'string', 'max:120'],
            'ac_type' => ['required', Rule::in(['AC', 'Non-AC'])],
            'platform_gps' => ['nullable', 'string', 'max:120'],
            'api_gps' => ['nullable', 'string', 'max:255'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'bop' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
            'target_revenue' => ['nullable', 'numeric', 'min:0'],
            'target_bulanan' => ['nullable', 'numeric', 'min:0'],
            'target_tahunan' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $existing = null;
        if ($id > 0) {
            $existingQuery = DB::table('armadas')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($existingQuery, 'armadas');
            $this->applyPoolScopeIfExists($existingQuery, 'armadas');
            $existing = $existingQuery->first(['id', 'pool_id']);

            if (! $existing) {
                return $this->error('Armada tidak ditemukan untuk pool aktif.', 404);
            }
        }

        $targetPoolId = $this->resolveWritablePoolIdFromRequest(
            $request,
            'armadas',
            (int) ($existing->pool_id ?? 0),
            $id <= 0,
        );
        if ($targetPoolId < 0) {
            return $this->error($this->poolResolveErrorMessage($targetPoolId), $targetPoolId === -1 ? 403 : 422);
        }

        if ($id <= 0 && FeatureGate::enabled() && Schema::hasColumn('armadas', 'tenant_id')) {
            if (! FeatureGate::canCreate('master.armadas', 'armadas', 'tenant_id')) {
                return $this->error(FeatureGate::limitMessage('master.armadas') ?? 'Batas armada paket Anda sudah tercapai.', 403);
            }
        }

        $nopol = strtoupper(trim((string) $data['nopol']));

        $duplicate = DB::table('armadas')
            ->whereRaw('UPPER(nopol) = ?', [$nopol])
            ->when(Schema::hasColumn('armadas', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'tenant_id');
            })
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicate) {
            return $this->error('Nopol armada sudah terdaftar.', 409);
        }

        $payload = [
            'merk' => $this->nullable($data['merk'] ?? null),
            'tahun' => (int) ($data['tahun'] ?? 0),
            'warna' => $this->nullable($data['warna'] ?? null),
            'nopol' => $nopol,
            'nomor_rangka' => $this->nullable($data['nomor_rangka'] ?? null),
            'kategori' => trim((string) ($data['kategori'] ?? '')),
            'ac_type' => trim((string) ($data['ac_type'] ?? 'AC')),
            'platform_gps' => $this->nullable($data['platform_gps'] ?? null),
            'api_gps' => $this->nullable($data['api_gps'] ?? null),
            'fixed_cost' => (float) ($data['fixed_cost'] ?? 0),
            'target_bulanan' => (float) ($data['target_revenue'] ?? $data['target_bulanan'] ?? 0),
        ];

        if (array_key_exists('revenue', $data)) {
            $payload['revenue'] = (float) ($data['revenue'] ?? 0);
        }

        if (array_key_exists('bop', $data)) {
            $payload['bop'] = (float) ($data['bop'] ?? 0);
        }

        $payload = $this->filterPayloadColumns('armadas', $payload);
        $payload = array_merge($payload, $this->poolPayload('armadas', $targetPoolId > 0 ? $targetPoolId : null));

        if ($id > 0) {
            $query = DB::table('armadas')->where('id', $id);
            $this->applyWriteTenantScopeIfExists($query, 'armadas');
            $this->applyPoolScopeIfExists($query, 'armadas');
            $updated = $query->update($payload);

            if ($updated === 0) {
                return $this->error('Armada tidak ditemukan untuk tenant ini.', 404);
            }

            return $this->ok(['message' => 'Armada updated.', 'id' => $id]);
        }

        $newId = DB::table('armadas')->insertGetId(array_merge($payload, $this->tenantPayload('armadas'), [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Armada created.', 'id' => $newId], 201);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterPayloadColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(static fn (mixed $_value, string $column): bool => Schema::hasColumn($table, $column))
            ->all();
    }

    public function armadasDelete(int $id): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->ok(['message' => 'Armada deleted.']);
        }

        $armadaQuery = DB::table('armadas')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($armadaQuery, 'armadas');
        $this->applyPoolScopeIfExists($armadaQuery, 'armadas');

        $nopol = (string) ($armadaQuery->value('nopol') ?? '');
        if ($nopol === '') {
            return $this->error('Armada tidak ditemukan untuk tenant ini.', 404);
        }

        if ($this->chartersHasArmadaIdColumn()) {
            $payload = ['armada_id' => null];
            if ($this->chartersHasArmadaNopolColumn()) {
                $payload['armada_nopol'] = null;
            }

            DB::table('charters')->where('armada_id', $id)->update($payload);
        } elseif ($this->chartersHasArmadaNopolColumn() && $nopol !== '') {
            DB::table('charters')
                ->whereRaw('UPPER(armada_nopol) = ?', [strtoupper(trim($nopol))])
                ->update(['armada_nopol' => null]);
        }

        if ($this->driversHasArmadaId()) {
            $payload = ['armada_id' => null];
            if ($this->driversHasArmadaNopol()) {
                $payload['armada_nopol'] = null;
            }
            DB::table('drivers')->where('armada_id', $id)->update($payload);
        } elseif ($this->driversHasArmadaNopol() && $nopol !== '') {
            DB::table('drivers')
                ->whereRaw('UPPER(armada_nopol) = ?', [strtoupper(trim($nopol))])
                ->update(['armada_nopol' => null]);
        }

        $deleteQuery = DB::table('armadas')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($deleteQuery, 'armadas');
        $this->applyPoolScopeIfExists($deleteQuery, 'armadas');
        $deleteQuery->delete();

        return $this->ok(['message' => 'Armada deleted.']);
    }

    public function armadasShow(int $id): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->error('Data armada tidak tersedia.', 404);
        }

        $query = DB::table('armadas')->where('id', $id);
        if (Schema::hasColumn('armadas', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($query, 'armadas');

        $row = $query->first($this->armadaSelectColumns());

        if (! $row) {
            return $this->error('Armada tidak ditemukan.', 404);
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);

        $row = $this->withArmadaFinancials($row, $financials);
        $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
        $poolNames = $this->poolNameMap([$row->pool_id ?? 0]);
        $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

        return $this->ok(['armada' => $row]);
    }

    public function poolSwitch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pool_id' => ['required', 'integer', 'min:0'],
        ]);

        $poolId = (int) ($data['pool_id'] ?? 0);

        if ($poolId > 0 && $this->poolTablesReady()) {
            $poolQuery = DB::table('pools')
                ->where('id', $poolId)
                ->where('status', 'active');

            if (Schema::hasColumn('pools', 'tenant_id')) {
                PoolScope::applyTenantScope($poolQuery, 'tenant_id');
            }

            $pool = $poolQuery->first(['id', 'name']);

            if (! $pool) {
                return $this->error('Pool tidak ditemukan atau tidak aktif.', 422);
            }

            $scope = PoolScope::forCurrentUser(0, (int) ($request->user()?->id ?? 0), false);
            if (! ($scope['all'] ?? true) && ! in_array($poolId, $scope['pool_ids'] ?? [], true)) {
                return $this->error('Anda tidak memiliki akses ke pool ini.', 403);
            }
        }

        session(['active_pool_id' => $poolId]);
        $poolName = $poolId > 0
            ? (string) (DB::table('pools')->where('id', $poolId)->value('name') ?? 'Pool')
            : 'Semua Pool';

        return $this->ok([
            'message' => $poolId > 0 ? "Pool aktif: {$poolName}" : 'Menampilkan semua pool.',
            'pool_id' => $poolId,
            'pool_name' => $poolName,
        ]);
    }

    public function poolsIndex(Request $request): JsonResponse
    {
        if (! $this->poolTablesReady()) {
            return $this->ok([
                'pools' => [],
                'routes' => Schema::hasTable('routes')
                    ? DB::table('routes')->orderBy('name')->get(['id', 'name', 'origin', 'destination'])
                    : [],
                'can_manage' => true,
                ...($request->boolean('paginate')
                    ? ['pagination' => $this->paginationMeta(0, 1, max(10, min(100, (int) $request->query('per_page', 20))))]
                    : []),
            ]);
        }

        $canManage = $this->currentUserIsSuperAdmin();
        $allowedPoolIds = $canManage ? [] : $this->currentUserPoolIds();
        $q = trim((string) $request->query('q', ''));
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $routeFinancials = $this->routeFinancialsForMonth($monthStart, $monthEnd);

        $poolQuery = DB::table('pools')
            ->when(Schema::hasColumn('pools', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'pools.tenant_id');
            })
            ->select([
                'id',
                'name',
                'code',
                'target_revenue',
                $this->hasPoolsFixedCostColumn() ? 'fixed_cost' : DB::raw('0 as fixed_cost'),
                'status',
                'notes',
                'created_at',
            ])
            ->orderBy('name');

        if (! $canManage) {
            if ($allowedPoolIds === []) {
                return $this->ok([
                    'pools' => [],
                    'routes' => [],
                    'can_manage' => false,
                    ...($request->boolean('paginate')
                        ? ['pagination' => $this->paginationMeta(0, 1, max(10, min(100, (int) $request->query('per_page', 20))))]
                        : []),
                ]);
            }

            $poolQuery->whereIn('id', $allowedPoolIds)->where('status', 'active');
        }

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $poolQuery->where(function (Builder $builder) use ($qLike): void {
                $builder
                    ->where('name', 'like', $qLike)
                    ->orWhere('code', 'like', $qLike)
                    ->orWhere('notes', 'like', $qLike);
            });
        }

        $pagination = null;
        if ($request->boolean('paginate')) {
            [$page, $perPage] = $this->paginationParams($request);
            $result = $this->paginateQuery($poolQuery, $page, $perPage);
            $pools = collect($result['data']);
            $pagination = $result['meta'];
        } else {
            $pools = $poolQuery->get();
        }
        $poolIds = $pools->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $routesByPool = [];

        if ($poolIds !== []) {
            $routeRows = DB::table('pool_route as pr')
                ->join('routes as r', 'pr.route_id', '=', 'r.id')
                ->whereIn('pr.pool_id', $poolIds)
                ->orderBy('r.name')
                ->get(['pr.pool_id', 'r.id', 'r.name', 'r.origin', 'r.destination']);

            foreach ($routeRows as $row) {
                $poolId = (int) ($row->pool_id ?? 0);
                $routesByPool[$poolId] ??= ['ids' => [], 'names' => []];
                $routesByPool[$poolId]['ids'][] = (int) ($row->id ?? 0);

                $routeName = trim((string) ($row->name ?? ''));
                $origin = trim((string) ($row->origin ?? ''));
                $destination = trim((string) ($row->destination ?? ''));

                foreach ([$routeName, $origin, $destination] as $label) {
                    if ($label !== '') {
                        $routesByPool[$poolId]['names'][] = $label;
                    }
                }

                if ($origin !== '' && $destination !== '') {
                    $routesByPool[$poolId]['names'][] = $origin.' - '.$destination;
                }
            }
        }

        $rows = $pools->map(function ($pool) use ($routesByPool, $routeFinancials): array {
            $poolId = (int) ($pool->id ?? 0);
            $routes = $routesByPool[$poolId] ?? ['ids' => [], 'names' => []];
            $financials = $this->sumRouteFinancials($routes['ids'], $routeFinancials);

            return [
                'id' => $poolId,
                'name' => (string) ($pool->name ?? ''),
                'code' => (string) ($pool->code ?? ''),
                'target_revenue' => (float) ($pool->target_revenue ?? 0),
                'fixed_cost' => (float) ($pool->fixed_cost ?? 0),
                'charter_revenue' => (float) ($financials['charter_revenue'] ?? 0),
                'departure_revenue' => (float) ($financials['departure_revenue'] ?? 0),
                'luggage_revenue' => (float) ($financials['luggage_revenue'] ?? 0),
                'revenue' => (float) ($financials['revenue'] ?? 0),
                'charter_bop' => (float) ($financials['charter_bop'] ?? 0),
                'departure_bop' => (float) ($financials['departure_bop'] ?? 0),
                'bop' => (float) ($financials['bop'] ?? 0),
                'status' => (string) ($pool->status ?? 'active'),
                'notes' => (string) ($pool->notes ?? ''),
                'created_at' => (string) ($pool->created_at ?? ''),
                'route_ids' => array_values(array_filter($routes['ids'], static fn ($id) => (int) $id > 0)),
                'route_names' => collect($routes['names'])
                    ->map(static fn ($name) => trim((string) $name))
                    ->filter(static fn ($name) => $name !== '')
                    ->unique()
                    ->values()
                    ->all(),
            ];
        })->values();

        $routeQuery = DB::table('routes')->orderBy('name');
        if (! $canManage) {
            $routeIds = [];
            foreach ($rows as $pool) {
                $routeIds = array_merge($routeIds, $pool['route_ids']);
            }
            $routeIds = array_values(array_unique(array_map(static fn ($id) => (int) $id, $routeIds)));

            if ($routeIds === []) {
                $routeQuery->whereRaw('1 = 0');
            } else {
                $routeQuery->whereIn('id', $routeIds);
            }
        }

        return $this->ok([
            'pools' => $rows,
            'routes' => $routeQuery->get(['id', 'name', 'origin', 'destination']),
            'can_manage' => $canManage,
            ...($pagination !== null ? ['pagination' => $pagination] : []),
        ]);
    }

    public function poolsSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('pool.manage')) {
            return $response;
        }

        if (! $this->poolTablesReady()) {
            return $this->error('Tabel pool belum tersedia. Jalankan migration terlebih dahulu.', 409);
        }

        $id = (int) $request->input('id', 0);

        if ($id <= 0 && FeatureGate::enabled() && Schema::hasColumn('pools', 'tenant_id')) {
            if (! FeatureGate::canCreate('tenant.multiple_pools', 'pools', 'tenant_id')) {
                return $this->error(FeatureGate::limitMessage('tenant.multiple_pools') ?? 'Batas pool/cabang paket Anda sudah tercapai.', 403);
            }
        }

        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('pools', 'code')->ignore($id)],
            'target_revenue' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
            'route_ids' => ['nullable', 'array'],
            'route_ids.*' => ['integer', 'min:1'],
        ]);

        $routeIds = collect($data['route_ids'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->filter(static fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        if ($routeIds !== []) {
            $validRouteIds = DB::table('routes')
                ->whereIn('id', $routeIds)
                ->pluck('id')
                ->map(static fn ($value) => (int) $value)
                ->all();

            if (count($validRouteIds) !== count($routeIds)) {
                return $this->error('Ada rute yang tidak ditemukan.', 422);
            }
        }

        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'code' => $this->nullable($data['code'] ?? null),
            'target_revenue' => (float) ($data['target_revenue'] ?? 0),
            'status' => (string) ($data['status'] ?? 'active'),
            'notes' => $this->nullable($data['notes'] ?? null),
        ];

        if ($this->hasPoolsFixedCostColumn()) {
            $payload['fixed_cost'] = (float) ($data['fixed_cost'] ?? 0);
        }

        return DB::transaction(function () use ($id, $payload, $routeIds): JsonResponse {
            if ($id > 0) {
                $query = DB::table('pools')->where('id', $id);
                if (Schema::hasColumn('pools', 'tenant_id')) {
                    PoolScope::applyTenantScope($query, 'tenant_id');
                }
                $query->update(array_merge($payload, ['updated_at' => now()]));
                $poolId = $id;
            } else {
                $poolId = (int) DB::table('pools')->insertGetId(array_merge($payload, $this->tenantPayload('pools'), [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            if ($routeIds === []) {
                DB::table('pool_route')->where('pool_id', $poolId)->delete();
            } else {
                DB::table('pool_route')->where('pool_id', $poolId)->whereNotIn('route_id', $routeIds)->delete();
                DB::table('pool_route')->whereIn('route_id', $routeIds)->where('pool_id', '!=', $poolId)->delete();

                $existingRouteIds = DB::table('pool_route')
                    ->where('pool_id', $poolId)
                    ->pluck('route_id')
                    ->map(static fn ($value) => (int) $value)
                    ->all();

                $now = now();
                $rows = [];
                foreach (array_diff($routeIds, $existingRouteIds) as $routeId) {
                    $rows[] = [
                        'pool_id' => $poolId,
                        'route_id' => (int) $routeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('pool_route')->insert($rows);
                }
            }

            return $this->ok([
                'message' => $id > 0 ? 'Pool updated.' : 'Pool created.',
                'id' => $poolId,
            ], $id > 0 ? 200 : 201);
        });
    }

    public function poolsDelete(int $id): JsonResponse
    {
        if ($response = $this->requirePermission('pool.manage')) {
            return $response;
        }

        if (! $this->poolTablesReady()) {
            return $this->error('Tabel pool belum tersedia.', 409);
        }

        DB::transaction(function () use ($id): void {
            DB::table('pool_user')->where('pool_id', $id)->delete();
            DB::table('pool_route')->where('pool_id', $id)->delete();
            DB::table('pools')->where('id', $id)->delete();
        });

        return $this->ok(['message' => 'Pool deleted.']);
    }

    public function rolesIndex(Request $request): JsonResponse
    {
        if ($response = $this->requireSuperAdmin()) {
            return $response;
        }

        if (! AccessControl::tablesReady()) {
            return $this->error('Tabel role dan permission belum tersedia. Jalankan migration terlebih dahulu.', 503);
        }

        return $this->ok([
            ...$this->roleAccessData->roles($request),
            ...$this->roleAccessData->permissions(),
        ]);
    }

    public function rolesSave(Request $request): JsonResponse
    {
        if ($response = $this->requireSuperAdmin()) {
            return $response;
        }

        if (! AccessControl::tablesReady()) {
            return $this->error('Tabel role dan permission belum tersedia. Jalankan migration terlebih dahulu.', 503);
        }

        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'min:1'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $existing = $id > 0
            ? DB::table('roles')->where('id', $id)->first(['id', 'slug', 'is_system'])
            : null;

        if ($id > 0 && ! $existing) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        $existingSlug = (string) ($existing?->slug ?? '');
        $slug = $id > 0 && (bool) ($existing?->is_system ?? false)
            ? $existingSlug
            : Str::slug((string) ($data['slug'] ?? '') !== '' ? (string) $data['slug'] : (string) $data['name']);

        if ($slug === '') {
            return $this->error('Slug role tidak valid.', 422);
        }

        $duplicateSlug = DB::table('roles')
            ->where('slug', $slug)
            ->when($id > 0, static fn (Builder $query) => $query->where('id', '!=', $id))
            ->exists();

        if ($duplicateSlug) {
            return $this->error('Slug role sudah digunakan.', 422);
        }

        $permissionIds = collect($data['permission_ids'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->filter(static fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        $allPermissionIds = DB::table('permissions')
            ->pluck('id')
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();

        if ($slug === 'super-admin') {
            $permissionIds = $allPermissionIds;
        }

        $validPermissionCount = $permissionIds === []
            ? 0
            : DB::table('permissions')->whereIn('id', $permissionIds)->count();

        if ($validPermissionCount !== count($permissionIds)) {
            return $this->error('Ada permission yang tidak ditemukan.', 422);
        }

        $now = now();
        $payload = [
            'name' => trim((string) $data['name']),
            'slug' => $slug,
            'description' => $this->nullable($data['description'] ?? null),
            'updated_at' => $now,
        ];

        $newId = $id;
        DB::transaction(function () use ($id, $payload, $permissionIds, $now, &$newId): void {
            if ($id > 0) {
                DB::table('roles')->where('id', $id)->update($payload);
                $roleId = $id;
            } else {
                $roleId = (int) DB::table('roles')->insertGetId(array_merge($payload, [
                    'is_system' => false,
                    'created_at' => $now,
                ]));
            }

            DB::table('role_permission')->where('role_id', $roleId)->delete();

            $rows = array_map(static fn (int $permissionId): array => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ], $permissionIds);

            if ($rows !== []) {
                DB::table('role_permission')->insert($rows);
            }

            $newId = $roleId;
        });

        ActivityLog::write($id > 0 ? 'role.updated' : 'role.created', $id > 0 ? 'Role hak akses diperbarui' : 'Role hak akses dibuat', '', null, [
            'role_id' => (int) ($newId ?? $id),
            'role_slug' => $slug,
            'permission_count' => count($permissionIds),
        ]);

        return $this->ok([
            'message' => $id > 0 ? 'Role updated.' : 'Role created.',
            'id' => (int) ($newId ?? $id),
        ], $id > 0 ? 200 : 201);
    }

    public function rolesDelete(int $id): JsonResponse
    {
        if ($response = $this->requireSuperAdmin()) {
            return $response;
        }

        if (! AccessControl::tablesReady()) {
            return $this->error('Tabel role dan permission belum tersedia. Jalankan migration terlebih dahulu.', 503);
        }

        $role = DB::table('roles')->where('id', $id)->first(['id', 'name', 'slug', 'is_system']);
        if (! $role) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        if ((bool) ($role->is_system ?? false)) {
            return $this->error('Role sistem tidak bisa dihapus.', 409);
        }

        $userCount = (int) DB::table('user_role')->where('role_id', $id)->count();
        if ($userCount > 0) {
            return $this->error('Role masih digunakan oleh user. Lepaskan role dari user terlebih dahulu.', 409);
        }

        DB::transaction(function () use ($id): void {
            DB::table('role_permission')->where('role_id', $id)->delete();
            DB::table('roles')->where('id', $id)->delete();
        });

        ActivityLog::write('role.deleted', 'Role hak akses dihapus', '', null, [
            'role_id' => $id,
            'role_slug' => (string) ($role->slug ?? ''),
        ]);

        return $this->ok(['message' => 'Role deleted.']);
    }

    public function usersIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);
        $select = ['id', 'name', 'email', 'email_verified_at', 'created_at'];
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $select[] = 'is_super_admin';
        } else {
            $select[] = DB::raw('0 as is_super_admin');
        }

        $query = DB::table('users')
            ->when(Schema::hasColumn('users', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'users.tenant_id');
            })
            ->select($select)
            ->orderBy('name');

        if (! $this->currentUserIsSuperAdmin() && $this->poolTablesReady()) {
            $poolIds = $this->currentUserPoolIds();
            if ($poolIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereExists(function (Builder $exists) use ($poolIds): void {
                    $exists
                        ->selectRaw('1')
                        ->from('pool_user as scoped_pool_user')
                        ->whereColumn('scoped_pool_user.user_id', 'users.id')
                        ->whereIn('scoped_pool_user.pool_id', $poolIds);
                });
            }
        }

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike) {
                $builder
                    ->where('name', 'like', $qLike)
                    ->orWhere('email', 'like', $qLike);
            });
        }

        $result = $this->paginateQuery($query, $page, $perPage);
        $users = collect($result['data'])->map(function ($row): array {
            $item = (array) $row;
            $item['is_super_admin'] = (bool) ($item['is_super_admin'] ?? false);
            $item['pool_ids'] = [];
            $item['pool_names'] = [];
            $item['role_ids'] = [];
            $item['role_names'] = [];

            return $item;
        })->values();
        $userIds = $users->pluck('id')->map(static fn ($id) => (int) $id)->all();

        if ($this->poolTablesReady() && $users->isNotEmpty()) {
            $poolRows = DB::table('pool_user as pu')
                ->join('pools as p', 'pu.pool_id', '=', 'p.id')
                ->whereIn('pu.user_id', $userIds)
                ->orderBy('p.name')
                ->get(['pu.user_id', 'p.id', 'p.name']);

            $poolMap = [];
            foreach ($poolRows as $pool) {
                $userId = (int) ($pool->user_id ?? 0);
                $poolMap[$userId] ??= ['ids' => [], 'names' => []];
                $poolMap[$userId]['ids'][] = (int) ($pool->id ?? 0);
                $poolMap[$userId]['names'][] = (string) ($pool->name ?? '');
            }

            $users = $users->map(function (array $user) use ($poolMap): array {
                $mapped = $poolMap[(int) ($user['id'] ?? 0)] ?? ['ids' => [], 'names' => []];
                $user['pool_ids'] = $mapped['ids'];
                $user['pool_names'] = $mapped['names'];

                return $user;
            })->values();
        }

        if (AccessControl::tablesReady() && $users->isNotEmpty()) {
            $roleRows = DB::table('user_role as ur')
                ->join('roles as r', 'ur.role_id', '=', 'r.id')
                ->whereIn('ur.user_id', $userIds)
                ->orderBy('r.name')
                ->get(['ur.user_id', 'r.id', 'r.name']);

            $roleMap = [];
            foreach ($roleRows as $role) {
                $userId = (int) ($role->user_id ?? 0);
                $roleMap[$userId] ??= ['ids' => [], 'names' => []];
                $roleMap[$userId]['ids'][] = (int) ($role->id ?? 0);
                $roleMap[$userId]['names'][] = (string) ($role->name ?? '');
            }

            $users = $users->map(function (array $user) use ($roleMap): array {
                $mapped = $roleMap[(int) ($user['id'] ?? 0)] ?? ['ids' => [], 'names' => []];
                $user['role_ids'] = $mapped['ids'];
                $user['role_names'] = $mapped['names'];

                return $user;
            })->values();
        }

        return $this->ok([
            'users' => $users,
            'roles' => AccessControl::rolesForSelect(),
            'pagination' => $result['meta'],
        ]);
    }

    public function usersSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('user.manage')) {
            return $response;
        }

        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore((int) $request->input('id', 0)),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'is_super_admin' => ['nullable', 'boolean'],
            'pool_ids' => ['nullable', 'array'],
            'pool_ids.*' => ['integer', 'min:1'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'min:1'],
        ]);

        $id = (int) ($data['id'] ?? 0);

        if ($id <= 0 && FeatureGate::enabled() && Schema::hasColumn('users', 'tenant_id')) {
            if (! FeatureGate::canCreate('user.management', 'users', 'tenant_id')) {
                return $this->error(FeatureGate::limitMessage('user.management') ?? 'Batas user paket Anda sudah tercapai.', 403);
            }
        }

        $payload = [
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
        ];

        $password = trim((string) ($data['password'] ?? ''));
        if ($password !== '') {
            $payload['password'] = Hash::make($password);
        }

        $roleIds = collect($data['role_ids'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->filter(static fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
        $superAdminRoleId = AccessControl::tablesReady()
            ? (int) (DB::table('roles')->where('slug', 'super-admin')->value('id') ?? 0)
            : 0;
        $keepsSuperAdminRole = $superAdminRoleId > 0 && in_array($superAdminRoleId, $roleIds, true);

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $wantsSuperAdmin = (bool) ($data['is_super_admin'] ?? false);
            if ($id > 0 && ! $wantsSuperAdmin && ! $keepsSuperAdminRole && $this->isUserSuperAdmin($id) && $this->superAdminCount() <= 1) {
                return $this->error('Minimal harus ada satu Super Admin.', 409);
            }
            $payload['is_super_admin'] = $wantsSuperAdmin;
        }

        $poolIds = collect($data['pool_ids'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->filter(static fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        if ($this->poolTablesReady() && $poolIds !== []) {
            $validPoolIds = DB::table('pools')->whereIn('id', $poolIds)->pluck('id')->map(static fn ($value) => (int) $value)->all();
            if (count($validPoolIds) !== count($poolIds)) {
                return $this->error('Ada pool yang tidak ditemukan.', 422);
            }
        }

        if (AccessControl::tablesReady() && $roleIds !== []) {
            $validRoleIds = DB::table('roles')->whereIn('id', $roleIds)->pluck('id')->map(static fn ($value) => (int) $value)->all();
            if (count($validRoleIds) !== count($roleIds)) {
                return $this->error('Ada role yang tidak ditemukan.', 422);
            }
        }

        if ($id > 0) {
            $query = DB::table('users')->where('id', $id);
            if (Schema::hasColumn('users', 'tenant_id')) {
                PoolScope::applyTenantScope($query, 'tenant_id');
            }
            $query->update(array_merge($payload, ['updated_at' => now()]));
            $this->syncUserPools($id, $poolIds);
            $this->syncUserRoles($id, $roleIds);

            return $this->ok(['message' => 'User updated.', 'id' => $id]);
        }

        if (! isset($payload['password'])) {
            return $this->error('Password wajib untuk user baru.', 422);
        }

        $newId = DB::table('users')->insertGetId(array_merge($payload, $this->tenantPayload('users'), [
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $this->syncUserPools((int) $newId, $poolIds);
        $this->syncUserRoles((int) $newId, $roleIds);

        return $this->ok(['message' => 'User created.', 'id' => $newId], 201);
    }

    public function usersDelete(int $id): JsonResponse
    {
        $authUserId = (int) (auth()->id() ?? 0);

        if ($id === $authUserId) {
            return $this->error('Tidak bisa menghapus akun yang sedang login.', 409);
        }

        $totalUsers = (int) DB::table('users')->count();
        if ($totalUsers <= 1) {
            return $this->error('Minimal harus ada satu user admin.', 409);
        }

        if ($this->isUserSuperAdmin($id) && $this->superAdminCount() <= 1) {
            return $this->error('Minimal harus ada satu Super Admin.', 409);
        }

        if ($this->poolTablesReady()) {
            DB::table('pool_user')->where('user_id', $id)->delete();
        }
        if (AccessControl::tablesReady()) {
            DB::table('user_role')->where('user_id', $id)->delete();
        }
        DB::table('users')->where('id', $id)->delete();

        return $this->ok(['message' => 'User deleted.']);
    }

    public function usersVerify(int $id): JsonResponse
    {
        if ($response = $this->requirePermission('user.manage')) {
            return $response;
        }

        $user = $this->userForAdminAction($id);
        if (! $user) {
            return $this->error('User tidak ditemukan.', 404);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
            event(new Verified($user));
        }

        ActivityLog::write('user.verified', 'User diverifikasi manual', (string) $user->email, null, [
            'user_id' => $user->id,
        ]);

        return $this->ok(['message' => 'User verified.']);
    }

    public function usersUnverify(int $id): JsonResponse
    {
        if ($response = $this->requirePermission('user.manage')) {
            return $response;
        }

        $authUserId = (int) (auth()->id() ?? 0);
        if ($id === $authUserId) {
            return $this->error('Tidak bisa membatalkan verifikasi akun yang sedang login.', 409);
        }

        $user = $this->userForAdminAction($id);
        if (! $user) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $user->forceFill(['email_verified_at' => null])->save();

        ActivityLog::write('user.unverified', 'Verifikasi user dibatalkan', (string) $user->email, null, [
            'user_id' => $user->id,
        ]);

        return $this->ok(['message' => 'User unverified.']);
    }

    public function usersSendVerification(int $id): JsonResponse
    {
        if ($response = $this->requirePermission('user.manage')) {
            return $response;
        }

        $user = $this->userForAdminAction($id);
        if (! $user) {
            return $this->error('User tidak ditemukan.', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email user sudah terverifikasi.', 409);
        }

        $user->sendEmailVerificationNotification();

        ActivityLog::write('user.verification_sent', 'Link verifikasi user dikirim', (string) $user->email, null, [
            'user_id' => $user->id,
        ]);

        return $this->ok(['message' => 'Verification email sent.']);
    }

    private function userForAdminAction(int $id): ?User
    {
        if ($id <= 0 || ! Schema::hasTable('users')) {
            return null;
        }

        $query = DB::table('users')->where('id', $id);
        if (Schema::hasColumn('users', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'users.tenant_id');
        }

        if (! $this->currentUserIsSuperAdmin() && $this->poolTablesReady()) {
            $poolIds = $this->currentUserPoolIds();
            if ($poolIds === []) {
                return null;
            }

            $query->whereExists(function (Builder $exists) use ($poolIds): void {
                $exists
                    ->selectRaw('1')
                    ->from('pool_user as scoped_pool_user')
                    ->whereColumn('scoped_pool_user.user_id', 'users.id')
                    ->whereIn('scoped_pool_user.pool_id', $poolIds);
            });
        }

        if (! $query->exists()) {
            return null;
        }

        return User::query()->whereKey($id)->first();
    }

    private function poolTablesReady(): bool
    {
        return Schema::hasTable('pools')
            && Schema::hasTable('pool_route')
            && Schema::hasTable('pool_user')
            && Schema::hasTable('routes');
    }

    private function defaultCustomerPoolId(string $table = 'customers'): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'pool_id')) {
            return 0;
        }

        return PoolScope::customerPoolId();
    }

    private function assignCustomerPoolIfMissing(int $customerId, int $poolId, string $table = 'customers'): void
    {
        if ($customerId <= 0 || $poolId <= 0 || ! Schema::hasTable($table) || ! Schema::hasColumn($table, 'pool_id')) {
            return;
        }

        $query = DB::table($table)
            ->where('id', $customerId)
            ->whereNull('pool_id');
        $this->applyWriteTenantScopeIfExists($query, $table);
        $query->update(['pool_id' => $poolId]);
    }

    private function currentUserIsSuperAdmin(): bool
    {
        $userId = (int) (auth()->id() ?? 0);

        return AccessControl::userIsSuperAdmin($userId);
    }

    private function requireSuperAdmin(): ?JsonResponse
    {
        if ($this->currentUserIsSuperAdmin()) {
            return null;
        }

        return $this->error('Hanya Super Admin yang bisa mengubah konfigurasi ini.', 403);
    }

    private function requirePermission(string $permission): ?JsonResponse
    {
        $userId = (int) (auth()->id() ?? 0);
        if (AccessControl::can($userId, $permission)) {
            return null;
        }

        return $this->error('Anda tidak memiliki akses untuk aksi ini.', 403);
    }

    private function isUserSuperAdmin(int $userId): bool
    {
        return AccessControl::userIsSuperAdmin($userId);
    }

    private function superAdminCount(): int
    {
        if (! Schema::hasTable('users')) {
            return 0;
        }

        if (! AccessControl::tablesReady() && ! Schema::hasColumn('users', 'is_super_admin')) {
            return (int) DB::table('users')->count();
        }

        $query = DB::table('users')->select('users.id');

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $query->where('users.is_super_admin', true);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (AccessControl::tablesReady()) {
            $roleUserIds = DB::table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('roles.slug', 'super-admin')
                ->pluck('user_role.user_id')
                ->map(static fn ($value) => (int) $value)
                ->all();

            if ($roleUserIds !== []) {
                $query->orWhereIn('users.id', $roleUserIds);
            }
        }

        return (int) $query->distinct()->count('users.id');
    }

    /**
     * @return array<int, int>
     */
    private function currentUserPoolIds(): array
    {
        return PoolScope::userPoolIds();
    }

    /**
     * @param  array<int, int>  $poolIds
     */
    private function syncUserPools(int $userId, array $poolIds): void
    {
        if (! $this->poolTablesReady() || $userId <= 0) {
            return;
        }

        if ($poolIds === []) {
            DB::table('pool_user')->where('user_id', $userId)->delete();

            return;
        }

        DB::table('pool_user')->where('user_id', $userId)->whereNotIn('pool_id', $poolIds)->delete();

        $existingPoolIds = DB::table('pool_user')
            ->where('user_id', $userId)
            ->pluck('pool_id')
            ->map(static fn ($value) => (int) $value)
            ->all();

        $now = now();
        $rows = [];
        foreach (array_diff($poolIds, $existingPoolIds) as $poolId) {
            $rows[] = [
                'user_id' => $userId,
                'pool_id' => (int) $poolId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('pool_user')->insert($rows);
        }
    }

    /**
     * @param  array<int, int>  $roleIds
     */
    private function syncUserRoles(int $userId, array $roleIds): void
    {
        if (! AccessControl::tablesReady() || $userId <= 0) {
            return;
        }

        if ($roleIds === []) {
            DB::table('user_role')->where('user_id', $userId)->delete();

            return;
        }

        DB::table('user_role')->where('user_id', $userId)->whereNotIn('role_id', $roleIds)->delete();

        $existingRoleIds = DB::table('user_role')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->map(static fn ($value) => (int) $value)
            ->all();

        $now = now();
        $rows = [];
        foreach (array_diff($roleIds, $existingRoleIds) as $roleId) {
            $rows[] = [
                'user_id' => $userId,
                'role_id' => (int) $roleId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('user_role')->insert($rows);
        }
    }

    /**
     * @return array{all: bool, pool_ids: array<int, int>, route_ids: array<int, int>, route_names: array<int, string>, labels: array<int, string>, pool_name: string, target_revenue: float}
     */
    private function routeScopeForCurrentUser(int $poolId = 0): array
    {
        $scope = PoolScope::forCurrentUser($poolId);

        return [
            'all' => (bool) ($scope['all'] ?? true),
            'pool_ids' => $scope['pool_ids'] ?? [],
            'route_ids' => $scope['route_ids'] ?? [],
            'route_names' => $scope['route_names'] ?? [],
            'labels' => $scope['labels'] ?? [],
            'pool_name' => (string) ($scope['pool_name'] ?? 'Semua Pool'),
            'target_revenue' => (float) ($scope['target_revenue'] ?? 0),
        ];

    }

    private function applyRouteScopeToQuery(Builder $query, string $routeIdColumn = '', string $routeNameColumn = '', int $poolId = 0): void
    {
        $scope = $this->routeScopeForCurrentUser($poolId);
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
            $hasClause = false;
            if ($routeIdColumn !== '' && $routeIds !== []) {
                $builder->whereIn($routeIdColumn, $routeIds);
                $hasClause = true;
            }

            if ($routeNameColumn !== '' && $routeNames !== []) {
                if ($hasClause) {
                    $builder->orWhereIn($routeNameColumn, $routeNames);
                } else {
                    $builder->whereIn($routeNameColumn, $routeNames);
                }
            }
        });
    }

    private function applyPoolOrRouteScopeToQuery(
        Builder $query,
        string $poolColumn = '',
        string $routeIdColumn = '',
        string $routeNameColumn = '',
        int $poolId = 0,
    ): void {
        $scope = $this->routeScopeForCurrentUser($poolId);
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
                        $this->appendRouteScopeClauses($legacy, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames);
                    });
                }

                return;
            }

            $this->appendRouteScopeClauses($builder, $routeIdColumn, $routeIds, $routeNameColumn, $routeNames);
        });
    }

    /**
     * @param  array<int, int>  $routeIds
     * @param  array<int, string>  $routeNames
     */
    private function appendRouteScopeClauses(Builder $builder, string $routeIdColumn, array $routeIds, string $routeNameColumn, array $routeNames): void
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

    private function applyCharterPoolScope(Builder $query, int $poolId = 0): void
    {
        $scope = $this->routeScopeForCurrentUser($poolId);
        if ($scope['all']) {
            return;
        }

        $poolIds = $scope['pool_ids'];
        $labels = $scope['labels'];
        if (($this->chartersHasPoolIdColumn() && $poolIds !== []) || $labels !== []) {
            $query->where(function (Builder $builder) use ($poolIds, $labels): void {
                $hasClause = false;

                if ($this->chartersHasPoolIdColumn() && $poolIds !== []) {
                    $builder->whereIn('c.pool_id', $poolIds);
                    $hasClause = true;
                }

                if ($labels !== []) {
                    if ($hasClause && $this->chartersHasPoolIdColumn()) {
                        $builder->orWhere(function (Builder $legacy) use ($labels): void {
                            $legacy
                                ->whereNull('c.pool_id')
                                ->where(function (Builder $routeBuilder) use ($labels): void {
                                    $routeBuilder
                                        ->whereIn('c.pickup_point', $labels)
                                        ->orWhereIn('c.drop_point', $labels);
                                });
                        });
                    } elseif ($hasClause) {
                        $builder->orWhere(function (Builder $routeBuilder) use ($labels): void {
                            $routeBuilder
                                ->whereIn('c.pickup_point', $labels)
                                ->orWhereIn('c.drop_point', $labels);
                        });
                    } else {
                        $builder
                            ->whereIn('c.pickup_point', $labels)
                            ->orWhereIn('c.drop_point', $labels);
                    }
                }
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolveTransactionPoolId(int $requestedPoolId = 0, int $routeId = 0, array $labels = [], int $existingPoolId = 0): int
    {
        if (! $this->poolTablesReady() || (int) DB::table('pools')->count() === 0) {
            return 0;
        }

        $scope = $this->routeScopeForCurrentUser();
        $allPoolIds = DB::table('pools')
            ->where('status', 'active')
            ->pluck('id')
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();
        $allowedPoolIds = $scope['all'] ? $allPoolIds : $scope['pool_ids'];

        if ($allowedPoolIds === []) {
            return -1;
        }

        $isAllowed = static fn (int $poolId): bool => $poolId > 0 && in_array($poolId, $allowedPoolIds, true);
        $mappedPoolId = $this->poolIdForRouteId($routeId);

        if ($mappedPoolId <= 0) {
            foreach ($labels as $label) {
                $labelPoolId = $this->poolIdForRouteLabel((string) $label);

                if ($labelPoolId > 0) {
                    $mappedPoolId = $labelPoolId;

                    break;
                }
            }
        }

        if ($requestedPoolId > 0) {
            if (! $isAllowed($requestedPoolId)) {
                return -1;
            }

            if ($mappedPoolId > 0 && $mappedPoolId !== $requestedPoolId) {
                return -3;
            }

            return $requestedPoolId;
        }

        if ($mappedPoolId > 0) {
            return $isAllowed($mappedPoolId) ? $mappedPoolId : -1;
        }

        if ($existingPoolId > 0) {
            return $isAllowed($existingPoolId) ? $existingPoolId : -1;
        }

        if (count($allowedPoolIds) === 1) {
            return (int) $allowedPoolIds[0];
        }

        return -2;
    }

    private function transactionPoolErrorMessage(int $code): string
    {
        return match ($code) {
            -1 => 'Pool tidak sesuai dengan akses user.',
            -3 => 'Rute yang dipilih sudah dimapping ke pool lain.',
            default => 'Pilih Perwakilan/Pool untuk data ini.',
        };
    }

    private function transactionPoolSnapshotAccessible(int $poolId = 0, array $labels = []): bool
    {
        $scope = $this->routeScopeForCurrentUser();
        if ($scope['all']) {
            return true;
        }

        if ($poolId > 0 && in_array($poolId, $scope['pool_ids'], true)) {
            return true;
        }

        if ($poolId > 0) {
            return false;
        }

        $allowedLabels = array_map(fn (string $value): string => $this->normalizeRouteLabel($value), $scope['labels']);
        foreach ($labels as $label) {
            if (in_array($this->normalizeRouteLabel((string) $label), $allowedLabels, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function accessibleCharterIds(array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map(static fn ($id) => (int) $id, $ids)), static fn ($id) => $id > 0));
        if ($ids === []) {
            return [];
        }

        $query = DB::table('charters as c')->whereIn('c.id', $ids);
        $this->applyCharterPoolScope($query);
        $this->applyTenantScopeIfExists($query, 'charters', 'c');

        return $query
            ->pluck('c.id')
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function accessibleLuggageIds(array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map(static fn ($id) => (int) $id, $ids)), static fn ($id) => $id > 0));
        if ($ids === []) {
            return [];
        }

        $query = DB::table('luggages as l')->whereIn('l.id', $ids);
        $this->applyPoolOrRouteScopeToQuery(
            $query,
            $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
            Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
            'l.rute',
        );
        $this->applyTenantScopeIfExists($query, 'luggages', 'l');

        return $query
            ->pluck('l.id')
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    private function poolIdForRouteId(int $routeId): int
    {
        if ($routeId <= 0 || ! $this->poolTablesReady()) {
            return 0;
        }

        $query = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->where('pr.route_id', $routeId);
        $this->applyTenantScopeIfExists($query, 'routes', 'r');

        return (int) ($query->value('pr.pool_id') ?? 0);
    }

    private function poolIdForRouteLabel(string $label): int
    {
        $normalized = $this->normalizeRouteLabel($label);
        if ($normalized === '' || ! $this->poolTablesReady()) {
            return 0;
        }

        $routes = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->when(Schema::hasColumn('routes', 'tenant_id'), function (Builder $query): void {
                PoolScope::applyTenantScope($query, 'r.tenant_id');
            })
            ->get(['pr.pool_id', 'r.name', 'r.origin', 'r.destination']);

        foreach ($routes as $route) {
            foreach (['name', 'origin', 'destination'] as $field) {
                if ($this->normalizeRouteLabel((string) ($route->{$field} ?? '')) === $normalized) {
                    return (int) ($route->pool_id ?? 0);
                }
            }
        }

        return 0;
    }

    private function normalizeRouteLabel(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? '';
    }

    /**
     * @return array{pool_id: int, pool_name: string, target_revenue: float}
     */
    private function routeScopeReportMeta(int $poolId = 0): array
    {
        $scope = $this->routeScopeForCurrentUser($poolId);

        return [
            'pool_id' => $poolId,
            'pool_name' => (string) $scope['pool_name'],
            'target_revenue' => (float) $scope['target_revenue'],
        ];
    }

    /**
     * @return array{requested: bool, id: int, name: string}
     */
    private function resolveAccessibleRouteFilter(int $routeId = 0, int $poolId = 0): array
    {
        if ($routeId <= 0 || ! Schema::hasTable('routes')) {
            return ['requested' => false, 'id' => 0, 'name' => ''];
        }

        $query = DB::table('routes')->where('id', $routeId);
        $this->applyTenantScopeIfExists($query, 'routes');
        $this->applyRouteScopeToQuery($query, 'routes.id', 'routes.name', $poolId);
        $route = $query->first(['id', 'name']);

        return [
            'requested' => true,
            'id' => (int) ($route->id ?? 0),
            'name' => trim((string) ($route->name ?? '')),
        ];
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function applyResolvedRouteFilter(Builder $query, array $routeFilter, string $routeIdColumn = '', string $routeNameColumn = ''): void
    {
        if (! ($routeFilter['requested'] ?? false)) {
            return;
        }

        $resolvedRouteId = (int) ($routeFilter['id'] ?? 0);
        $resolvedRouteName = trim((string) ($routeFilter['name'] ?? ''));

        if ($routeIdColumn !== '' && $resolvedRouteId > 0) {
            $query->where($routeIdColumn, $resolvedRouteId);

            return;
        }

        if ($routeNameColumn !== '' && $resolvedRouteName !== '') {
            $query->where($routeNameColumn, $resolvedRouteName);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function nullable(?string $value): ?string
    {
        $v = trim((string) ($value ?? ''));

        return $v === '' ? null : $v;
    }

    private function normalizeCustomerImportHeader(string $header): ?string
    {
        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', trim($header)) ?? '';
        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[\s\-.]+/', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'name', 'nama', 'customer', 'customer_name', 'nama_customer' => 'name',
            'phone', 'no_hp', 'nohp', 'nomor_hp', 'hp', 'telepon', 'telp', 'whatsapp', 'wa' => 'phone',
            'pickup_point', 'pickup', 'titik_jemput', 'lokasi_jemput', 'alamat_jemput' => 'pickup_point',
            'gmaps', 'maps', 'google_maps', 'google_map', 'link_maps', 'url_maps', 'address', 'alamat' => 'gmaps',
            default => null,
        };
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $columns
     */
    private function customerImportValue(array $row, array $columns, string $key): string
    {
        if (! array_key_exists($key, $columns)) {
            return '';
        }

        return trim((string) ($row[$columns[$key]] ?? ''));
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isBlankCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function paginationParams(Request $request): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(10, min(100, (int) $request->query('per_page', 20)));

        return [$page, $perPage];
    }

    private function normalizePhone(string $value): string
    {
        $trimmed = trim($value);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return $digits !== '' ? $digits : $trimmed;
    }

    private function countSeatsFromLayoutJson(?string $layout): int
    {
        $decoded = json_decode(trim((string) ($layout ?? '')), true);
        if (! is_array($decoded)) {
            return 0;
        }

        $count = 0;
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $type = strtolower(trim((string) ($cell['type'] ?? $cell['kind'] ?? '')));
                    if ($type === 'seat') {
                        $count += 1;
                    }

                    continue;
                }

                if (! is_string($cell) && ! is_numeric($cell)) {
                    continue;
                }

                $token = strtoupper(trim((string) $cell));
                if ($token === '' || in_array($token, ['DRIVER', 'AISLE', '-', '_'], true)) {
                    continue;
                }

                $count += 1;
            }
        }

        return $count;
    }

    private function upsertCustomerBagasi(string $nama, string $noHp, ?string $alamat, string $tipe, int $poolId = 0): int
    {
        if ($noHp === '') {
            return 0;
        }

        $existingQuery = DB::table('customer_bagasi')
            ->where('no_hp', $noHp);
        PoolScope::applyCustomerBagasiScope($existingQuery, 'customer_bagasi');
        $this->applyWriteTenantScopeIfExists($existingQuery, 'customer_bagasi');
        $existing = $existingQuery->first(['id', 'tipe']);

        if ($existing) {
            $existingTipe = strtolower((string) ($existing->tipe ?? ''));
            $nextTipe = $existingTipe;
            if ($existingTipe !== $tipe && $existingTipe !== 'keduanya') {
                $nextTipe = 'keduanya';
            }

            $customerUpdate = DB::table('customer_bagasi')->where('id', (int) $existing->id);
            PoolScope::applyCustomerBagasiScope($customerUpdate, 'customer_bagasi');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_bagasi');
            $customerUpdate->update([
                'nama' => $nama,
                'alamat' => $alamat,
                'tipe' => $nextTipe === '' ? $tipe : $nextTipe,
            ]);
            $this->assignCustomerPoolIfMissing((int) $existing->id, $poolId, 'customer_bagasi');

            return (int) $existing->id;
        }

        return (int) DB::table('customer_bagasi')->insertGetId(array_merge([
            'nama' => $nama,
            'no_hp' => $noHp,
            'alamat' => $alamat,
            'tipe' => $tipe,
            'created_at' => now(),
        ], $poolId > 0 && Schema::hasColumn('customer_bagasi', 'pool_id') ? ['pool_id' => $poolId] : [], $this->tenantPayload('customer_bagasi')));
    }

    private function resolveMappedLuggagePrice(int $routeId, int $serviceId): float
    {
        return 0;
    }

    private function ensureLuggageResi(int $luggageId): string
    {
        $existing = (string) (DB::table('luggages')->where('id', $luggageId)->value('kode_resi') ?? '');
        if ($existing !== '') {
            return $existing;
        }

        $resi = $this->nextLuggageResi();
        DB::table('luggages')->where('id', $luggageId)->update(['kode_resi' => $resi]);

        return $resi;
    }

    private function nextLuggageResi(): string
    {
        $date = now()->format('Ymd');
        $prefix = "BGS-{$date}-";
        $like = "{$prefix}%";

        $query = DB::table('luggages')
            ->where('kode_resi', 'like', $like)
            ->orderByDesc('id');
        $this->applyWriteTenantScopeIfExists($query, 'luggages');
        $last = (string) ($query->value('kode_resi') ?? '');

        $seq = 1;
        if ($last !== '') {
            $parts = explode('-', $last);
            $tail = (int) end($parts);
            if ($tail > 0) {
                $seq = $tail + 1;
            }
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function appendLuggageLogByResi(string $resi, string $status, ?string $notes = null): void
    {
        $user = auth()->user();
        $actor = $user?->email ?? $user?->name ?? 'system';
        $normalizedStatus = $this->normalizeLuggageStatus($status);

        DB::table('bagasi_logs')->insert(array_merge([
            'kode_resi' => $resi,
            'status' => $normalizedStatus,
            'notes' => $this->nullable($notes),
            'created_by_username' => $actor,
            'created_at' => now(),
        ], $this->tenantPayload('bagasi_logs')));

        ActivityLog::write(
            'BAGASI',
            'Bagasi '.$resi.' - '.$normalizedStatus,
            $this->nullable($notes) ?? 'Update bagasi',
            (string) $actor,
            ['kode_resi' => $resi, 'status' => $normalizedStatus],
        );
    }

    private function luggageReceivedStatus(): string
    {
        return 'Diterima';
    }

    private function luggagePickedUpStatus(): string
    {
        return 'Dalam Perjalanan';
    }

    private function luggageArrivedStatus(): string
    {
        return 'Tiba di Tujuan';
    }

    private function normalizeLuggageStatus(mixed $status): string
    {
        $raw = trim((string) ($status ?? ''));
        $normalized = strtolower($raw);

        return match ($normalized) {
            '', 'pending', 'done', 'diterima', 'barang sudah diterima' => $this->luggageReceivedStatus(),
            'active', 'sent', 'barang sudah dipickup', 'dalam perjalanan' => $this->luggagePickedUpStatus(),
            'barang sudah tiba', 'tiba di tujuan' => $this->luggageArrivedStatus(),
            'canceled' => 'canceled',
            'payment' => 'payment',
            default => $raw !== '' ? $raw : $this->luggageReceivedStatus(),
        };
    }

    private function luggageStatusAliases(string $status): array
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            '', 'pending', 'done', 'diterima', 'barang sudah diterima', strtolower($this->luggageReceivedStatus()) => [
                'pending',
                'done',
                'diterima',
                'barang sudah diterima',
                strtolower($this->luggageReceivedStatus()),
            ],
            'active', 'sent', 'barang sudah dipickup', strtolower($this->luggagePickedUpStatus()) => [
                'active',
                'sent',
                'barang sudah dipickup',
                strtolower($this->luggagePickedUpStatus()),
            ],
            'barang sudah tiba', strtolower($this->luggageArrivedStatus()) => [
                'barang sudah tiba',
                strtolower($this->luggageArrivedStatus()),
            ],
            'canceled' => ['canceled'],
            'payment' => ['payment'],
            default => [$normalized],
        };
    }

    private function luggageRevenueStatuses(): array
    {
        return array_values(array_unique(array_merge(
            $this->luggageStatusAliases($this->luggageReceivedStatus()),
            $this->luggageStatusAliases($this->luggagePickedUpStatus()),
            $this->luggageStatusAliases($this->luggageArrivedStatus()),
        )));
    }

    private function applyLuggageStatusFilter(Builder $query, string $column, array $statuses): void
    {
        $normalizedStatuses = array_values(array_unique(array_filter(array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            $statuses,
        ))));

        $query->where(function (Builder $statusQuery) use ($column, $normalizedStatuses) {
            foreach ($normalizedStatuses as $index => $status) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $statusQuery->{$method}('LOWER('.$column.') = ?', [$status]);
            }
        });
    }

    private function activityActor(): string
    {
        $user = auth()->user();

        return (string) ($user?->email ?? $user?->name ?? 'system');
    }

    private function charterIdentityLabel(array $charter): string
    {
        $name = strtoupper(trim((string) ($charter['name'] ?? '')));
        $company = trim((string) ($charter['company_name'] ?? ''));

        if ($name !== '' && $company !== '') {
            return $name.' - '.$company;
        }

        if ($name !== '') {
            return $name;
        }

        if ($company !== '') {
            return $company;
        }

        return 'pelanggan';
    }

    private function charterTripSummary(array $charter): string
    {
        return $this->buildSummaryParts([
            $this->displayText((string) ($charter['pickup_point'] ?? '')) !== '' ? 'Pickup '.$this->displayText((string) ($charter['pickup_point'] ?? '')) : '',
            $this->displayText((string) ($charter['drop_point'] ?? '')) !== '' ? 'Drop '.$this->displayText((string) ($charter['drop_point'] ?? '')) : '',
            $this->displayDate($charter['start_date'] ?? null),
            $this->displayTime($charter['departure_time'] ?? null),
        ]);
    }

    private function charterCreateSummary(array $payload): string
    {
        return $this->buildSummaryParts([
            $this->charterTripSummary($payload),
            $this->displayText((string) ($payload['driver_name'] ?? '')) !== '' ? 'Driver: '.$this->displayText((string) ($payload['driver_name'] ?? '')) : '',
            $this->displayText((string) ($payload['armada_nopol'] ?? '')) !== '' ? 'Armada: '.$this->displayText((string) ($payload['armada_nopol'] ?? '')) : '',
            isset($payload['payment_status']) ? 'Pembayaran: '.$this->displayText((string) $payload['payment_status']) : '',
        ]);
    }

    private function charterChangeSummary(array $before, array $after): string
    {
        $changes = [];
        $fieldMap = [
            'name' => 'Nama',
            'company_name' => 'Perusahaan',
            'phone' => 'Telepon',
            'start_date' => 'Tgl Mulai',
            'end_date' => 'Tgl Selesai',
            'departure_time' => 'Jam',
            'pickup_point' => 'Pickup',
            'drop_point' => 'Drop',
            'unit_id' => 'Unit',
            'armada_nopol' => 'Armada',
            'driver_name' => 'Driver',
            'price' => 'Harga',
            'layanan' => 'Layanan',
            'bop_price' => 'BOP',
            'bop_status' => 'Status BOP',
            'down_payment' => 'DP',
            'payment_status' => 'Pembayaran',
            'status' => 'Status',
        ];

        foreach ($fieldMap as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $beforeValue = $this->adminFieldDisplayValue($field, $before[$field] ?? null);
            $afterValue = $this->adminFieldDisplayValue($field, $after[$field] ?? null);

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[] = $this->formatChangeLine($label, $beforeValue, $afterValue);
        }

        return ! empty($changes)
            ? implode(' | ', $changes)
            : $this->buildSummaryParts([$this->charterTripSummary($after), 'Tidak ada perubahan data utama']);
    }

    private function luggageCreateSummary(array $payload): string
    {
        return $this->buildSummaryParts([
            'Pengirim: '.$this->displayText((string) ($payload['sender_name'] ?? '')),
            'Penerima: '.$this->displayText((string) ($payload['receiver_name'] ?? '')),
            isset($payload['quantity']) ? 'Qty: '.(int) $payload['quantity'] : '',
            isset($payload['payment_status']) ? 'Pembayaran: '.$this->displayText((string) $payload['payment_status']) : '',
            $this->displayText((string) ($payload['rute'] ?? '')) !== '' ? 'Rute: '.$this->displayText((string) ($payload['rute'] ?? '')) : '',
        ]);
    }

    private function luggageChangeSummary(array $before, array $after): string
    {
        $changes = [];
        $fieldMap = [
            'sender_name' => 'Pengirim',
            'sender_phone' => 'HP Pengirim',
            'sender_address' => 'Alamat Pengirim',
            'receiver_name' => 'Penerima',
            'receiver_phone' => 'HP Penerima',
            'receiver_address' => 'Alamat Penerima',
            'quantity' => 'Qty',
            'notes' => 'Catatan',
            'price' => 'Harga',
            'status' => 'Status',
            'payment_status' => 'Pembayaran',
            'rute' => 'Rute',
            'tanggal' => 'Tanggal',
            'unit_id' => 'Unit',
        ];

        foreach ($fieldMap as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $beforeValue = $this->adminFieldDisplayValue($field, $before[$field] ?? null);
            $afterValue = $this->adminFieldDisplayValue($field, $after[$field] ?? null);

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[] = $this->formatChangeLine($label, $beforeValue, $afterValue);
        }

        return ! empty($changes)
            ? implode(' | ', $changes)
            : $this->luggageCreateSummary($after);
    }

    private function adminFieldDisplayValue(string $field, mixed $value): string
    {
        return match ($field) {
            'departure_time' => $this->displayTime($value),
            'start_date', 'end_date', 'tanggal' => $this->displayDate($value),
            'price', 'bop_price', 'down_payment' => number_format((float) $value, 0, ',', '.'),
            'quantity', 'unit_id' => $value === null || $value === '' ? '' : (string) ((int) $value),
            default => $this->displayText((string) ($value ?? '')),
        };
    }

    private function displayDate(mixed $value): string
    {
        return $this->displayText((string) ($value ?? ''));
    }

    private function displayTime(mixed $value): string
    {
        return substr($this->displayText((string) ($value ?? '')), 0, 5);
    }

    private function displayText(string $value): string
    {
        return trim($value);
    }

    private function formatChangeLine(string $label, string $before, string $after): string
    {
        $before = $before !== '' ? $before : '-';
        $after = $after !== '' ? $after : '-';

        return $label.': '.$before.' -> '.$after;
    }

    private function buildSummaryParts(array $parts, string $separator = ' | '): string
    {
        $parts = array_values(array_filter(array_map(
            static fn ($part) => trim((string) $part),
            $parts,
        )));

        return implode($separator, $parts);
    }

    private function normalizeDriverName(string $value): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
    }

    private function normalizeNopol(string $value): string
    {
        $normalized = strtoupper(trim($value));

        return preg_replace('/[^A-Z0-9]/', '', $normalized) ?? '';
    }

    private function normalizeRouteName(string $value): string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
        $normalized = str_replace([' => ', ' -> ', ' - '], ' TO ', $normalized);
        $normalized = str_replace(['=>', '->'], ' TO ', $normalized);
        $normalized = preg_replace('/\s*-\s*/', ' TO ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function findRouteByNormalizedName(string $routeName): ?object
    {
        $normalizedTarget = $this->normalizeRouteName($routeName);
        if ($normalizedTarget === '') {
            return null;
        }

        $query = DB::table('routes');
        $this->applyWriteTenantScopeIfExists($query, 'routes');
        $rows = $query->get(['id', 'name']);
        foreach ($rows as $row) {
            if ($this->normalizeRouteName((string) ($row->name ?? '')) === $normalizedTarget) {
                return $row;
            }
        }

        return null;
    }

    private function driverScheduleBopKey(string $rute, int $dow, string $jam): string
    {
        $route = $this->normalizeRouteName($rute);
        $time = substr(trim($jam), 0, 5);

        if ($route === '' || $time === '') {
            return '';
        }

        return implode('|', [$route, (string) $dow, $time]);
    }

    private function driverTripRevenueKey(string $rute, string $tanggal, string $jam, int $unit): string
    {
        $route = $this->normalizeRouteName($rute);
        $date = trim($tanggal);
        $time = substr(trim($jam), 0, 5);

        if ($route === '' || $date === '' || $time === '' || $unit <= 0) {
            return '';
        }

        return implode('|', [$route, $date, $time, (string) $unit]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function driverRowsForMonth(string $monthStart, string $monthEnd): array
    {
        $hasDriverArmadaId = $this->driversHasArmadaId();
        $hasDriverArmadaNopol = $this->driversHasArmadaNopol();
        $canJoinArmadas = $hasDriverArmadaId && Schema::hasTable('armadas');

        $select = [
            'd.id',
            'd.nama',
            'd.phone',
            'd.unit_id',
            Schema::hasColumn('drivers', 'pool_id') ? 'd.pool_id' : DB::raw('NULL as pool_id'),
            $this->hasDriversTargetRevenueBulananColumn() ? 'd.target_revenue_bulanan' : DB::raw('0 as target_revenue_bulanan'),
            $this->hasDriversTargetRevenueTahunanColumn() ? 'd.target_revenue_tahunan' : DB::raw('0 as target_revenue_tahunan'),
            $this->hasDriversRevenueColumn() ? 'd.revenue' : DB::raw('0 as revenue'),
            $this->hasDriversBopColumn() ? 'd.bop' : DB::raw('0 as bop'),
            $this->hasDriversFixedCostColumn() ? 'd.fixed_cost' : DB::raw('0 as fixed_cost'),
        ];

        $select[] = $hasDriverArmadaId ? 'd.armada_id' : DB::raw('NULL as armada_id');
        if ($hasDriverArmadaNopol && $canJoinArmadas) {
            $select[] = DB::raw('COALESCE(d.armada_nopol, a.nopol) as nopol');
        } elseif ($hasDriverArmadaNopol) {
            $select[] = DB::raw('d.armada_nopol as nopol');
        } elseif ($canJoinArmadas) {
            $select[] = DB::raw('a.nopol as nopol');
        } else {
            $select[] = DB::raw('NULL as nopol');
        }

        $rows = DB::table('drivers as d')
            ->when($canJoinArmadas, static function (Builder $query) {
                $query->leftJoin('armadas as a', 'd.armada_id', '=', 'a.id');
            })
            ->when(Schema::hasColumn('drivers', 'tenant_id'), function (Builder $query) {
                $tenantId = PoolScope::tenantId();
                if ($tenantId > 0) {
                    $query->where('d.tenant_id', $tenantId);
                }
            })
            ->when(Schema::hasColumn('drivers', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'd.tenant_id');
            })
            ->when(Schema::hasColumn('drivers', 'pool_id'), function (Builder $q): void {
                $this->applyPoolScopeIfExists($q, 'drivers', 'd');
            })
            ->orderBy('d.nama')
            ->get($select);
        $poolNames = $this->poolNameMap($rows->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());

        $scheduleBopMap = $this->scheduleBopMap();
        $bookingRevenueMap = $this->bookingRevenueByTripForDateRange($monthStart, $monthEnd);

        $departureRevenueByDriver = [];
        $departureBopByDriver = [];
        $assignmentRows = DB::table('trip_assignments')
            ->whereNotNull('driver_id')
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $query): void {
                PoolScope::applyTenantScope($query, 'trip_assignments.tenant_id');
            })
            ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                $query->where(function (Builder $statusQuery) {
                    $statusQuery
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'canceled');
                });
            });
        $this->applyPoolOrRouteScopeToQuery(
            $assignmentRows,
            Schema::hasColumn('trip_assignments', 'pool_id') ? 'trip_assignments.pool_id' : '',
            Schema::hasColumn('trip_assignments', 'route_id') ? 'trip_assignments.route_id' : '',
            'trip_assignments.rute',
        );
        $assignmentRows = $assignmentRows->get(['driver_id', 'rute', 'tanggal', 'jam', 'unit']);

        foreach ($assignmentRows as $assignment) {
            $driverId = (int) ($assignment->driver_id ?? 0);
            $tanggal = trim((string) ($assignment->tanggal ?? ''));

            if ($driverId <= 0 || $tanggal === '') {
                continue;
            }

            try {
                $dow = Carbon::parse($tanggal)->dayOfWeek;
            } catch (\Throwable) {
                continue;
            }

            $scheduleKey = $this->driverScheduleBopKey(
                (string) ($assignment->rute ?? ''),
                $dow,
                (string) ($assignment->jam ?? ''),
            );
            $revenueKey = $this->driverTripRevenueKey(
                (string) ($assignment->rute ?? ''),
                $tanggal,
                (string) ($assignment->jam ?? ''),
                (int) ($assignment->unit ?? 0),
            );

            $departureRevenueByDriver[$driverId] = (float) ($departureRevenueByDriver[$driverId] ?? 0)
                + (float) ($bookingRevenueMap[$revenueKey] ?? 0);
            $departureBopByDriver[$driverId] = (float) ($departureBopByDriver[$driverId] ?? 0)
                + (float) ($scheduleBopMap[$scheduleKey] ?? 0);
        }

        $luggageRevenueByDriver = [];
        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'trip_assignment_id')) {
            $luggageRows = DB::table('luggages as l')
                ->join('trip_assignments as t', 'l.trip_assignment_id', '=', 't.id')
                ->whereBetween(DB::raw('COALESCE(l.tanggal, DATE(l.created_at), t.tanggal)'), [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('luggages', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'l.tenant_id');
                })
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 't.tenant_id');
                })
                ->where(function (Builder $query) {
                    $this->applyLuggageStatusFilter($query, 'l.status', $this->luggageRevenueStatuses());
                })
                ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery
                            ->whereNull('t.status')
                            ->orWhere('t.status', '!=', 'canceled');
                    });
                })
                ->select([
                    't.driver_id',
                    DB::raw('SUM(COALESCE(l.price, 0)) as total_price'),
                ]);
            $this->applyPoolOrRouteScopeToQuery(
                $luggageRows,
                $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
                Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
                'l.rute',
            );
            $luggageRows = $luggageRows
                ->groupBy('t.driver_id')
                ->get();

            foreach ($luggageRows as $luggage) {
                $driverId = (int) ($luggage->driver_id ?? 0);
                if ($driverId <= 0) {
                    continue;
                }

                $luggageRevenueByDriver[$driverId] = (float) ($luggage->total_price ?? 0);
            }
        }

        $charterRevenueByDriverName = [];
        $charterBopByDriverName = [];
        $hasCharterStatus = $this->chartersHasStatusColumn();
        $charterQuery = DB::table('charters as c')
            ->whereBetween('c.start_date', [$monthStart, $monthEnd])
            ->when(Schema::hasColumn('charters', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'c.tenant_id');
            });
        $this->applyCharterPoolScope($charterQuery);
        $charterRows = $charterQuery->get([
            'c.driver_name',
            'c.price',
            'c.bop_price',
            'c.payment_status',
            'c.bop_status',
            $hasCharterStatus ? DB::raw('c.status as status') : DB::raw('NULL as status'),
        ]);

        foreach ($charterRows as $charter) {
            $driverKey = $this->normalizeDriverName((string) ($charter->driver_name ?? ''));
            if ($driverKey === '') {
                continue;
            }

            $status = strtolower(trim((string) ($charter->status ?? '')));
            $paymentStatus = strtolower(trim((string) ($charter->payment_status ?? '')));
            $bopStatus = strtolower(trim((string) ($charter->bop_status ?? '')));

            if ($status === 'canceled' || $paymentStatus === 'canceled' || $bopStatus === 'canceled') {
                continue;
            }

            $charterRevenueByDriverName[$driverKey] = (float) ($charterRevenueByDriverName[$driverKey] ?? 0)
                + (float) ($charter->price ?? 0);
            $charterBopByDriverName[$driverKey] = (float) ($charterBopByDriverName[$driverKey] ?? 0)
                + (float) ($charter->bop_price ?? 0);
        }

        $items = [];
        foreach ($rows as $row) {
            $driverId = (int) ($row->id ?? 0);
            $driverNameKey = $this->normalizeDriverName((string) ($row->nama ?? ''));
            $luggageRevenue = (float) ($luggageRevenueByDriver[$driverId] ?? 0);
            $departureRevenue = (float) ($departureRevenueByDriver[$driverId] ?? 0);
            $departureBop = (float) ($departureBopByDriver[$driverId] ?? 0);
            $charterRevenue = (float) ($charterRevenueByDriverName[$driverNameKey] ?? 0);
            $charterBop = (float) ($charterBopByDriverName[$driverNameKey] ?? 0);

            $payload = (array) $row;
            $payload['id'] = $driverId;
            $payload['unit_id'] = $payload['unit_id'] !== null ? (int) $payload['unit_id'] : null;
            $payload['armada_id'] = $payload['armada_id'] !== null ? (int) $payload['armada_id'] : null;
            $payload['pool_id'] = (int) ($payload['pool_id'] ?? 0) ?: null;
            $payload['pool_name'] = $payload['pool_id'] ? ($poolNames[$payload['pool_id']] ?? null) : null;
            $payload['target_revenue_bulanan'] = (float) ($payload['target_revenue_bulanan'] ?? 0);
            $payload['fixed_cost'] = (float) ($payload['fixed_cost'] ?? 0);
            $payload['nopol'] = (string) ($payload['nopol'] ?? '');
            $payload['luggage_revenue'] = $luggageRevenue;
            $payload['departure_revenue'] = $departureRevenue;
            $payload['charter_revenue'] = $charterRevenue;
            $payload['revenue'] = $departureRevenue + $luggageRevenue + $charterRevenue;
            $payload['departure_bop'] = $departureBop;
            $payload['charter_bop'] = $charterBop;
            $payload['bop'] = $departureBop + $charterBop;
            $items[] = $payload;
        }

        return $items;
    }

    /**
     * @return array<string, float>
     */
    private function scheduleBopMap(): array
    {
        if (! Schema::hasTable('schedules') || ! $this->hasSchedulesBopColumn()) {
            return [];
        }

        $cacheKey = implode(':', [
            'adminops',
            'schedule-bop-map',
            PoolScope::cacheKey(),
            $this->buildTableMutationSignature('schedules'),
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(5), function (): array {
            $map = [];
            $rows = DB::table('schedules')
                ->when(Schema::hasColumn('schedules', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'schedules.tenant_id');
                });
            $this->applyRouteScopeToQuery($rows, Schema::hasColumn('schedules', 'route_id') ? 'schedules.route_id' : '', 'schedules.rute');
            $rows = $rows->get(['rute', 'dow', 'jam', 'bop']);

            foreach ($rows as $row) {
                $key = $this->driverScheduleBopKey(
                    (string) ($row->rute ?? ''),
                    (int) ($row->dow ?? 0),
                    (string) ($row->jam ?? ''),
                );

                if ($key === '') {
                    continue;
                }

                $map[$key] = max((float) ($map[$key] ?? 0), (float) ($row->bop ?? 0));
            }

            return $map;
        });
    }

    /**
     * @return array<string, float>
     */
    private function bookingRevenueByTripForDateRange(string $monthStart, string $monthEnd): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $cacheKey = implode(':', [
            'adminops',
            'booking-trip-revenue',
            PoolScope::cacheKey(),
            $monthStart,
            $monthEnd,
            $this->buildTableMutationSignature('bookings'),
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($monthStart, $monthEnd): array {
            $map = [];
            $rows = DB::table('bookings')
                ->select(['rute', 'tanggal', 'jam', 'unit'])
                ->selectRaw('SUM(CASE WHEN COALESCE(price, 0) - COALESCE(discount, 0) > 0 THEN COALESCE(price, 0) - COALESCE(discount, 0) ELSE 0 END) as net_revenue')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('bookings', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'bookings.tenant_id');
                });
            $this->applyRouteScopeToQuery($rows, Schema::hasColumn('bookings', 'route_id') ? 'bookings.route_id' : '', 'bookings.rute');
            $rows = $rows
                ->groupBy('rute', 'tanggal', 'jam', 'unit')
                ->get();

            foreach ($rows as $row) {
                $key = $this->driverTripRevenueKey(
                    (string) ($row->rute ?? ''),
                    (string) ($row->tanggal ?? ''),
                    (string) ($row->jam ?? ''),
                    (int) ($row->unit ?? 0),
                );

                if ($key === '') {
                    continue;
                }

                $map[$key] = (float) ($row->net_revenue ?? 0);
            }

            return $map;
        });
    }

    /**
     * @return array{luggage_revenue: float, departure_revenue: float, charter_revenue: float, revenue: float, departure_bop: float, charter_bop: float, bop: float}
     */
    private function emptyFinancialBucket(): array
    {
        return [
            'luggage_revenue' => 0.0,
            'departure_revenue' => 0.0,
            'charter_revenue' => 0.0,
            'revenue' => 0.0,
            'departure_bop' => 0.0,
            'charter_bop' => 0.0,
            'bop' => 0.0,
        ];
    }

    /**
     * @return array<int, array{luggage_revenue: float, departure_revenue: float, charter_revenue: float, revenue: float, departure_bop: float, charter_bop: float, bop: float}>
     */
    private function routeFinancialsForMonth(string $monthStart, string $monthEnd): array
    {
        if (! Schema::hasTable('routes')) {
            return [];
        }

        $routes = DB::table('routes')
            ->when(Schema::hasColumn('routes', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'routes.tenant_id');
            })
            ->get(['id', 'name', 'origin', 'destination']);
        $financials = [];
        $nameMap = [];
        $routeProfiles = [];

        foreach ($routes as $route) {
            $routeId = (int) ($route->id ?? 0);
            if ($routeId <= 0) {
                continue;
            }

            $financials[$routeId] = $this->emptyFinancialBucket();
            $routeProfiles[$routeId] = [
                'name' => $this->normalizeRouteName((string) ($route->name ?? '')),
                'origin' => $this->normalizeRouteName((string) ($route->origin ?? '')),
                'destination' => $this->normalizeRouteName((string) ($route->destination ?? '')),
            ];

            foreach (['name', 'origin', 'destination'] as $field) {
                $key = $routeProfiles[$routeId][$field];
                if ($key === '') {
                    continue;
                }

                $nameMap[$key] ??= [];
                $nameMap[$key][] = $routeId;
            }
        }

        $routeIdsForName = static fn (string $name) => $nameMap[$name] ?? [];

        if (Schema::hasTable('bookings')) {
            $bookingRows = DB::table('bookings')
                ->select(['rute'])
                ->selectRaw('SUM(CASE WHEN COALESCE(price, 0) - COALESCE(discount, 0) > 0 THEN COALESCE(price, 0) - COALESCE(discount, 0) ELSE 0 END) as net_revenue')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('bookings', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'bookings.tenant_id');
                })
                ->groupBy('rute')
                ->get();

            foreach ($bookingRows as $booking) {
                foreach ($routeIdsForName($this->normalizeRouteName((string) ($booking->rute ?? ''))) as $routeId) {
                    $financials[$routeId]['departure_revenue'] += (float) ($booking->net_revenue ?? 0);
                }
            }
        }

        if (Schema::hasTable('trip_assignments')) {
            $scheduleBopMap = $this->scheduleBopMap();
            $assignmentRows = DB::table('trip_assignments')
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'trip_assignments.tenant_id');
                })
                ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery
                            ->whereNull('status')
                            ->orWhere('status', '!=', 'canceled');
                    });
                })
                ->get(['rute', 'tanggal', 'jam']);

            foreach ($assignmentRows as $assignment) {
                $tanggal = trim((string) ($assignment->tanggal ?? ''));
                if ($tanggal === '') {
                    continue;
                }

                try {
                    $dow = Carbon::parse($tanggal)->dayOfWeek;
                } catch (\Throwable) {
                    continue;
                }

                $scheduleKey = $this->driverScheduleBopKey(
                    (string) ($assignment->rute ?? ''),
                    $dow,
                    (string) ($assignment->jam ?? ''),
                );
                $bop = (float) ($scheduleBopMap[$scheduleKey] ?? 0);
                foreach ($routeIdsForName($this->normalizeRouteName((string) ($assignment->rute ?? ''))) as $routeId) {
                    $financials[$routeId]['departure_bop'] += $bop;
                }
            }
        }

        if (Schema::hasTable('luggages')) {
            $luggageRows = DB::table('luggages')
                ->select([
                    Schema::hasColumn('luggages', 'rute_id') ? 'rute_id' : DB::raw('NULL as rute_id'),
                    'rute',
                    'price',
                ])
                ->when(Schema::hasColumn('luggages', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'luggages.tenant_id');
                })
                ->whereBetween(DB::raw('COALESCE(tanggal, DATE(created_at))'), [$monthStart, $monthEnd])
                ->where(function (Builder $query) {
                    $this->applyLuggageStatusFilter($query, 'status', $this->luggageRevenueStatuses());
                })
                ->get();

            foreach ($luggageRows as $luggage) {
                $mappedRouteIds = [];
                $routeId = (int) ($luggage->rute_id ?? 0);
                if ($routeId > 0 && isset($financials[$routeId])) {
                    $mappedRouteIds[] = $routeId;
                }

                if ($mappedRouteIds === []) {
                    $mappedRouteIds = $routeIdsForName($this->normalizeRouteName((string) ($luggage->rute ?? '')));
                }

                foreach (array_unique($mappedRouteIds) as $mappedRouteId) {
                    $financials[$mappedRouteId]['luggage_revenue'] += (float) ($luggage->price ?? 0);
                }
            }
        }

        if (Schema::hasTable('charters')) {
            $hasStatus = $this->chartersHasStatusColumn();
            $charterRows = DB::table('charters')
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('charters', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'charters.tenant_id');
                })
                ->get([
                    'pickup_point',
                    'drop_point',
                    'price',
                    'bop_price',
                    'payment_status',
                    'bop_status',
                    $hasStatus ? 'status' : DB::raw('NULL as status'),
                ]);

            foreach ($charterRows as $charter) {
                $status = strtolower(trim((string) ($charter->status ?? '')));
                $paymentStatus = strtolower(trim((string) ($charter->payment_status ?? '')));
                $bopStatus = strtolower(trim((string) ($charter->bop_status ?? '')));

                if ($status === 'canceled' || $paymentStatus === 'canceled' || $bopStatus === 'canceled') {
                    continue;
                }

                $matchedRouteIds = $this->matchRouteIdsForCharter(
                    (string) ($charter->pickup_point ?? ''),
                    (string) ($charter->drop_point ?? ''),
                    $routeProfiles,
                    $nameMap,
                );

                foreach ($matchedRouteIds as $routeId) {
                    $financials[$routeId]['charter_revenue'] += (float) ($charter->price ?? 0);
                    $financials[$routeId]['charter_bop'] += (float) ($charter->bop_price ?? 0);
                }
            }
        }

        foreach ($financials as $routeId => $bucket) {
            // Zero out charter revenue for routes that have zero operational data
            // Prevents phantom revenue from loose name-based matching
            $depRev = (float) ($bucket['departure_revenue'] ?? 0);
            $lugRev = (float) ($bucket['luggage_revenue'] ?? 0);
            $charRev = (float) ($bucket['charter_revenue'] ?? 0);

            if ($depRev <= 0 && $lugRev <= 0) {
                $charRev = 0;
            }

            $financials[$routeId]['revenue'] = $depRev + $lugRev + $charRev;
            $financials[$routeId]['bop'] = (float) ($bucket['departure_bop'] ?? 0)
                + ($charRev > 0 ? (float) ($bucket['charter_bop'] ?? 0) : 0);
        }

        return $financials;
    }

    /**
     * @param  array<int, array{name: string, origin: string, destination: string}>  $routeProfiles
     * @param  array<string, array<int, int>>  $nameMap
     * @return array<int, int>
     */
    private function matchRouteIdsForCharter(string $pickupPoint, string $dropPoint, array $routeProfiles, array $nameMap): array
    {
        $pickup = $this->normalizeRouteName($pickupPoint);
        $drop = $this->normalizeRouteName($dropPoint);
        $matches = [];

        foreach ($routeProfiles as $routeId => $profile) {
            $name = $profile['name'];
            $origin = $profile['origin'];
            $destination = $profile['destination'];

            // Only match if BOTH origin AND destination match (same or reverse direction)
            // This prevents phantom charter revenue on loosely-matched routes
            $sameDirection = $origin !== '' && $destination !== '' && $pickup === $origin && $drop === $destination;
            $reverseDirection = $origin !== '' && $destination !== '' && $pickup === $destination && $drop === $origin;

            if ($sameDirection || $reverseDirection) {
                $matches[] = $routeId;
            }
        }

        // Fallback: only match by single location/label if no directional match found
        // This handles charters with single pickup/drop labels
        if ($matches === []) {
            foreach ([$pickup, $drop] as $label) {
                foreach ($nameMap[$label] ?? [] as $routeId) {
                    $matches[] = $routeId;
                }
            }
        }

        return array_values(array_unique(array_map(static fn ($id) => (int) $id, $matches)));
    }

    /**
     * @param  array<int, int>  $routeIds
     * @param  array<int, array<string, float>>  $financials
     * @return array{luggage_revenue: float, departure_revenue: float, charter_revenue: float, revenue: float, departure_bop: float, charter_bop: float, bop: float}
     */
    private function sumRouteFinancials(array $routeIds, array $financials): array
    {
        $bucket = $this->emptyFinancialBucket();

        foreach (array_unique(array_map(static fn ($id) => (int) $id, $routeIds)) as $routeId) {
            $item = $financials[$routeId] ?? [];
            foreach (array_keys($bucket) as $key) {
                $bucket[$key] += (float) ($item[$key] ?? 0);
            }
        }

        return $bucket;
    }

    /**
     * @param  array<int, array<string, float>>  $financials
     */
    private function withRouteFinancials(object $row, array $financials): object
    {
        $bucket = $financials[(int) ($row->id ?? 0)] ?? [];

        $row->target_revenue = (float) ($row->target_revenue ?? 0);
        $row->fixed_cost = (float) ($row->fixed_cost ?? 0);
        $row->luggage_revenue = (float) ($bucket['luggage_revenue'] ?? 0);
        $row->departure_revenue = (float) ($bucket['departure_revenue'] ?? 0);
        $row->charter_revenue = (float) ($bucket['charter_revenue'] ?? 0);
        $row->revenue = (float) ($bucket['revenue'] ?? 0);
        $row->departure_bop = (float) ($bucket['departure_bop'] ?? 0);
        $row->charter_bop = (float) ($bucket['charter_bop'] ?? 0);
        $row->bop = (float) ($bucket['bop'] ?? 0);

        return $row;
    }

    /**
     * @return array<string, array{luggage_revenue: float, departure_revenue: float, charter_revenue: float, revenue: float, departure_bop: float, charter_bop: float, bop: float}>
     */
    private function armadaFinancialsForMonth(string $monthStart, string $monthEnd): array
    {
        $emptyBucket = static fn (): array => [
            'luggage_revenue' => 0.0,
            'departure_revenue' => 0.0,
            'charter_revenue' => 0.0,
            'revenue' => 0.0,
            'departure_bop' => 0.0,
            'charter_bop' => 0.0,
            'bop' => 0.0,
        ];
        $ensureBucket = static function (array &$items, string $key) use ($emptyBucket): void {
            if (! isset($items[$key])) {
                $items[$key] = $emptyBucket();
            }
        };

        $scheduleBopMap = $this->scheduleBopMap();
        $bookingRevenueMap = $this->bookingRevenueByTripForDateRange($monthStart, $monthEnd);
        $financials = [];

        if (Schema::hasTable('trip_assignments')) {
            $hasArmadaId = $this->tripAssignmentsHasArmadaId();
            $hasArmadaNopol = $this->tripAssignmentsHasArmadaNopol();
            $canJoinArmadas = $hasArmadaId && Schema::hasTable('armadas');

            $assignmentQuery = DB::table('trip_assignments as t');
            if ($canJoinArmadas) {
                $assignmentQuery->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
            }

            $assignmentSelect = [
                't.rute',
                't.tanggal',
                't.jam',
                't.unit',
            ];

            if ($hasArmadaNopol && $canJoinArmadas) {
                $assignmentSelect[] = DB::raw('COALESCE(t.armada_nopol, a.nopol) as armada_nopol');
            } elseif ($hasArmadaNopol) {
                $assignmentSelect[] = DB::raw('t.armada_nopol as armada_nopol');
            } elseif ($canJoinArmadas) {
                $assignmentSelect[] = DB::raw('a.nopol as armada_nopol');
            } else {
                $assignmentSelect[] = DB::raw('NULL as armada_nopol');
            }

            $assignmentRows = $assignmentQuery
                ->whereBetween('t.tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 't.tenant_id');
                })
                ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery
                            ->whereNull('t.status')
                            ->orWhere('t.status', '!=', 'canceled');
                    });
                });
            $this->applyPoolOrRouteScopeToQuery(
                $assignmentRows,
                Schema::hasColumn('trip_assignments', 'pool_id') ? 't.pool_id' : '',
                Schema::hasColumn('trip_assignments', 'route_id') ? 't.route_id' : '',
                't.rute',
            );
            $assignmentRows = $assignmentRows->get($assignmentSelect);

            foreach ($assignmentRows as $assignment) {
                $nopolKey = $this->normalizeNopol((string) ($assignment->armada_nopol ?? ''));
                $tanggal = trim((string) ($assignment->tanggal ?? ''));

                if ($nopolKey === '' || $tanggal === '') {
                    continue;
                }

                try {
                    $dow = Carbon::parse($tanggal)->dayOfWeek;
                } catch (\Throwable) {
                    continue;
                }

                $scheduleKey = $this->driverScheduleBopKey(
                    (string) ($assignment->rute ?? ''),
                    $dow,
                    (string) ($assignment->jam ?? ''),
                );
                $revenueKey = $this->driverTripRevenueKey(
                    (string) ($assignment->rute ?? ''),
                    $tanggal,
                    (string) ($assignment->jam ?? ''),
                    (int) ($assignment->unit ?? 0),
                );

                $ensureBucket($financials, $nopolKey);
                $financials[$nopolKey]['departure_revenue'] += (float) ($bookingRevenueMap[$revenueKey] ?? 0);
                $financials[$nopolKey]['departure_bop'] += (float) ($scheduleBopMap[$scheduleKey] ?? 0);
            }
        }

        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'trip_assignment_id') && Schema::hasTable('trip_assignments')) {
            $hasArmadaId = $this->tripAssignmentsHasArmadaId();
            $hasArmadaNopol = $this->tripAssignmentsHasArmadaNopol();
            $canJoinArmadas = $hasArmadaId && Schema::hasTable('armadas');

            $luggageQuery = DB::table('luggages as l')
                ->join('trip_assignments as t', 'l.trip_assignment_id', '=', 't.id');

            if ($canJoinArmadas) {
                $luggageQuery->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
            }

            $luggageSelect = [
                'l.price',
            ];

            if ($hasArmadaNopol && $canJoinArmadas) {
                $luggageSelect[] = DB::raw('COALESCE(t.armada_nopol, a.nopol) as armada_nopol');
            } elseif ($hasArmadaNopol) {
                $luggageSelect[] = DB::raw('t.armada_nopol as armada_nopol');
            } elseif ($canJoinArmadas) {
                $luggageSelect[] = DB::raw('a.nopol as armada_nopol');
            } else {
                $luggageSelect[] = DB::raw('NULL as armada_nopol');
            }

            $luggageRows = $luggageQuery
                ->whereBetween(DB::raw('COALESCE(l.tanggal, DATE(l.created_at), t.tanggal)'), [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('luggages', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'l.tenant_id');
                })
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 't.tenant_id');
                })
                ->where(function (Builder $query) {
                    $this->applyLuggageStatusFilter($query, 'l.status', $this->luggageRevenueStatuses());
                })
                ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery
                            ->whereNull('t.status')
                            ->orWhere('t.status', '!=', 'canceled');
                    });
                });
            $this->applyPoolOrRouteScopeToQuery(
                $luggageRows,
                $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
                Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
                'l.rute',
            );
            $luggageRows = $luggageRows->get($luggageSelect);

            foreach ($luggageRows as $luggage) {
                $nopolKey = $this->normalizeNopol((string) ($luggage->armada_nopol ?? ''));

                if ($nopolKey === '') {
                    continue;
                }

                $ensureBucket($financials, $nopolKey);
                $financials[$nopolKey]['luggage_revenue'] += (float) ($luggage->price ?? 0);
            }
        }

        if (Schema::hasTable('charters')) {
            $hasArmadaId = $this->chartersHasArmadaIdColumn();
            $hasArmadaNopol = $this->chartersHasArmadaNopolColumn();
            $canJoinArmadas = $hasArmadaId && Schema::hasTable('armadas');
            $hasStatus = $this->chartersHasStatusColumn();

            $charterQuery = DB::table('charters as c');
            if ($canJoinArmadas) {
                $charterQuery->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
            }

            $charterSelect = [
                'c.price',
                'c.bop_price',
                'c.payment_status',
                'c.bop_status',
                DB::raw($hasStatus ? 'c.status as status' : 'NULL as status'),
            ];

            if ($hasArmadaNopol && $canJoinArmadas) {
                $charterSelect[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
            } elseif ($hasArmadaNopol) {
                $charterSelect[] = DB::raw('c.armada_nopol as armada_nopol');
            } elseif ($canJoinArmadas) {
                $charterSelect[] = DB::raw('a.nopol as armada_nopol');
            } else {
                $charterSelect[] = DB::raw('NULL as armada_nopol');
            }

            $charterRows = $charterQuery
                ->whereBetween('c.start_date', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('charters', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'c.tenant_id');
                });
            $this->applyCharterPoolScope($charterRows);
            $charterRows = $charterRows->get($charterSelect);

            foreach ($charterRows as $charter) {
                $nopolKey = $this->normalizeNopol((string) ($charter->armada_nopol ?? ''));

                if ($nopolKey === '') {
                    continue;
                }

                $status = strtolower(trim((string) ($charter->status ?? '')));
                $paymentStatus = strtolower(trim((string) ($charter->payment_status ?? '')));
                $bopStatus = strtolower(trim((string) ($charter->bop_status ?? '')));

                if ($status === 'canceled' || $paymentStatus === 'canceled' || $bopStatus === 'canceled') {
                    continue;
                }

                $ensureBucket($financials, $nopolKey);
                $financials[$nopolKey]['charter_revenue'] += (float) ($charter->price ?? 0);
                $financials[$nopolKey]['charter_bop'] += (float) ($charter->bop_price ?? 0);
            }
        }

        foreach ($financials as $key => $bucket) {
            $financials[$key]['revenue'] = (float) ($bucket['departure_revenue'] ?? 0) + (float) ($bucket['luggage_revenue'] ?? 0) + (float) ($bucket['charter_revenue'] ?? 0);
            $financials[$key]['bop'] = (float) ($bucket['departure_bop'] ?? 0) + (float) ($bucket['charter_bop'] ?? 0);
        }

        return $financials;
    }

    /**
     * @param  array<string, array<string, float>>  $financials
     */
    private function withArmadaFinancials(object $row, array $financials): object
    {
        $bucket = $financials[$this->normalizeNopol((string) ($row->nopol ?? ''))] ?? [];

        $row->luggage_revenue = (float) ($bucket['luggage_revenue'] ?? 0);
        $row->departure_revenue = (float) ($bucket['departure_revenue'] ?? 0);
        $row->charter_revenue = (float) ($bucket['charter_revenue'] ?? 0);
        $row->revenue = (float) ($bucket['revenue'] ?? 0);
        $row->departure_bop = (float) ($bucket['departure_bop'] ?? 0);
        $row->charter_bop = (float) ($bucket['charter_bop'] ?? 0);
        $row->bop = (float) ($bucket['bop'] ?? 0);

        return $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assignmentConflicts(string $tanggal, string $jamWithSec, int $unit, int $driverId, int $ignoreId = 0): array
    {
        $query = DB::table('trip_assignments as t')
            ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id')
            ->select([
                't.id',
                't.rute',
                't.tanggal',
                't.jam',
                't.unit',
                't.driver_id',
                DB::raw('d.nama as driver_name'),
            ])
            ->where('t.tanggal', $tanggal)
            ->where('t.jam', $jamWithSec)
            ->when($ignoreId > 0, fn ($q) => $q->where('t.id', '!=', $ignoreId))
            ->where(function ($builder) use ($unit, $driverId) {
                $builder->where('t.unit', $unit);
                if ($driverId > 0) {
                    $builder->orWhere('t.driver_id', $driverId);
                }
            });

        $rows = $query->get();
        $conflicts = [];
        foreach ($rows as $row) {
            if ((int) $row->unit === $unit) {
                $conflicts[] = [
                    'type' => 'unit_busy',
                    'message' => 'Unit already assigned at same date/time.',
                    'assignment_id' => (int) $row->id,
                    'rute' => $row->rute,
                    'tanggal' => $row->tanggal,
                    'jam' => substr((string) $row->jam, 0, 5),
                    'unit' => (int) $row->unit,
                    'driver_id' => (int) ($row->driver_id ?? 0),
                    'driver_name' => $row->driver_name,
                ];
            }
            if ($driverId > 0 && (int) ($row->driver_id ?? 0) === $driverId) {
                $conflicts[] = [
                    'type' => 'driver_busy',
                    'message' => 'Driver already assigned at same date/time.',
                    'assignment_id' => (int) $row->id,
                    'rute' => $row->rute,
                    'tanggal' => $row->tanggal,
                    'jam' => substr((string) $row->jam, 0, 5),
                    'unit' => (int) $row->unit,
                    'driver_id' => (int) ($row->driver_id ?? 0),
                    'driver_name' => $row->driver_name,
                ];
            }
        }

        return $conflicts;
    }

    /**
     * @return array{data: Collection<int, object>, meta: array<string, int>}
     */
    private function paginateQuery(Builder $query, int $page, int $perPage): array
    {
        $total = (clone $query)->count();
        $meta = $this->paginationMeta($total, $page, $perPage);

        $data = $query
            ->forPage($meta['page'], $meta['per_page'])
            ->get();

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }

    /**
     * @return array{page: int, per_page: int, total: int, last_page: int}
     */
    private function paginationMeta(int $total, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function dateTimeRange(string $from, string $to): array
    {
        return [
            Carbon::createFromFormat('Y-m-d', $from)->startOfDay()->toDateTimeString(),
            Carbon::createFromFormat('Y-m-d', $to)->endOfDay()->toDateTimeString(),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeDateRange(string $from, string $to): array
    {
        $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d', $to)->startOfDay();

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate->toDateString(), $toDate->toDateString()];
    }

    private function applyNotCanceledFilter(Builder $query, string $column): void
    {
        $query->where(function (Builder $builder) use ($column) {
            $builder
                ->whereNull($column)
                ->orWhereRaw('LOWER('.$column.') <> ?', ['canceled']);
        });
    }

    private function applyActiveCharterReportFilter(Builder $query, string $alias = 'c'): void
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        if ($this->chartersHasStatusColumn()) {
            $this->applyNotCanceledFilter($query, $prefix.'status');

            return;
        }

        $query->where(function (Builder $builder) use ($prefix) {
            $builder
                ->whereNull($prefix.'payment_status')
                ->orWhereRaw('LOWER('.$prefix.'payment_status) <> ?', ['canceled']);
        });
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function buildTablesMutationSignature(array $tables): string
    {
        $signatures = [];

        foreach ($tables as $table) {
            $signatures[$table] = $this->buildTableMutationSignature($table);
        }

        return md5(json_encode($signatures, JSON_THROW_ON_ERROR));
    }

    private function buildTableMutationSignature(string $table): string
    {
        if (! Schema::hasTable($table)) {
            return 'na';
        }

        $selects = ['COUNT(*) as total_rows'];
        if (Schema::hasColumn($table, 'id')) {
            $selects[] = 'COALESCE(MAX(id), 0) as max_id';
        }

        foreach (['updated_at', 'created_at'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $selects[] = "MAX({$column}) as max_{$column}";
            }
        }

        $signature = (array) (DB::table($table)->selectRaw(implode(', ', $selects))->first() ?? []);

        return md5(json_encode($signature, JSON_THROW_ON_ERROR));
    }

    private function hasSchedulesRouteId(): bool
    {
        if ($this->schedulesHasRouteId === null) {
            $this->schedulesHasRouteId = Schema::hasColumn('schedules', 'route_id');
        }

        return $this->schedulesHasRouteId;
    }

    private function hasSchedulesSeatsColumn(): bool
    {
        if ($this->schedulesHasSeatsColumn === null) {
            $this->schedulesHasSeatsColumn = Schema::hasColumn('schedules', 'seats');
        }

        return $this->schedulesHasSeatsColumn;
    }

    private function hasSchedulesBopColumn(): bool
    {
        if ($this->schedulesHasBopColumn === null) {
            $this->schedulesHasBopColumn = Schema::hasColumn('schedules', 'bop');
        }

        return $this->schedulesHasBopColumn;
    }

    private function hasScheduleUnitsTable(): bool
    {
        if ($this->scheduleUnitsTableExists === null) {
            $this->scheduleUnitsTableExists = Schema::hasTable('schedule_units');
        }

        return $this->scheduleUnitsTableExists;
    }

    private function chartersHasStatusColumn(): bool
    {
        if ($this->chartersHasStatusColumn === null) {
            $this->chartersHasStatusColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'status');
        }

        return $this->chartersHasStatusColumn;
    }

    private function chartersHasArmadaIdColumn(): bool
    {
        if ($this->chartersHasArmadaIdColumn === null) {
            $this->chartersHasArmadaIdColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'armada_id');
        }

        return $this->chartersHasArmadaIdColumn;
    }

    private function chartersHasArmadaNopolColumn(): bool
    {
        if ($this->chartersHasArmadaNopolColumn === null) {
            $this->chartersHasArmadaNopolColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'armada_nopol');
        }

        return $this->chartersHasArmadaNopolColumn;
    }

    private function chartersHasPoolIdColumn(): bool
    {
        if ($this->chartersHasPoolIdColumn === null) {
            $this->chartersHasPoolIdColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'pool_id');
        }

        return $this->chartersHasPoolIdColumn;
    }

    private function chartersHasMasterCarterIdColumn(): bool
    {
        if ($this->chartersHasMasterCarterIdColumn === null) {
            $this->chartersHasMasterCarterIdColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'master_carter_id');
        }

        return $this->chartersHasMasterCarterIdColumn;
    }

    private function luggagesHasPoolIdColumn(): bool
    {
        if ($this->luggagesHasPoolIdColumn === null) {
            $this->luggagesHasPoolIdColumn = Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'pool_id');
        }

        return $this->luggagesHasPoolIdColumn;
    }

    private function currentPoolContextId(): int
    {
        return PoolScope::currentPoolId(auth()->id());
    }

    private function writablePoolContextId(): int
    {
        return PoolScope::defaultWritablePoolId(auth()->id());
    }

    private function applyTenantScopeIfExists(Builder $query, string $table, string $alias = ''): void
    {
        [$baseTable, $tableAlias] = $this->parseTableAlias($table);
        $effectiveAlias = $alias !== '' ? $alias : $tableAlias;
        $prefix = $effectiveAlias !== '' ? $effectiveAlias.'.' : '';
        if (Schema::hasColumn($baseTable, 'tenant_id')) {
            PoolScope::applyTenantScope($query, $prefix.'tenant_id');
        }
    }

    private function applyPoolScopeIfExists(Builder $query, string $table, string $alias = '', ?int $poolId = null): void
    {
        [$baseTable, $tableAlias] = $this->parseTableAlias($table);
        $effectiveAlias = $alias !== '' ? $alias : $tableAlias;
        $prefix = $effectiveAlias !== '' ? $effectiveAlias.'.' : '';
        if (! Schema::hasColumn($baseTable, 'pool_id')) {
            return;
        }

        $resolvedPoolId = $poolId ?? $this->currentPoolContextId();
        if ($resolvedPoolId > 0) {
            $query->where($prefix.'pool_id', $resolvedPoolId);

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

        $query->whereIn($prefix.'pool_id', $poolIds);
    }

    private function applyWriteTenantScopeIfExists(Builder $query, string $table, string $alias = ''): void
    {
        [$baseTable, $tableAlias] = $this->parseTableAlias($table);
        $tenantId = $this->writeTenantId($baseTable);
        if ($tenantId <= 0) {
            return;
        }

        $effectiveAlias = $alias !== '' ? $alias : $tableAlias;
        $prefix = $effectiveAlias !== '' ? $effectiveAlias.'.' : '';
        $query->where($prefix.'tenant_id', $tenantId);
    }

    /**
     * @return array<string, int>
     */
    private function tenantPayload(string $table): array
    {
        $tenantId = $this->writeTenantId($table);

        return $tenantId > 0 ? ['tenant_id' => $tenantId] : [];
    }

    private function writeTenantId(string $table): int
    {
        if (! Schema::hasColumn($table, 'tenant_id')) {
            return 0;
        }

        $tenantId = PoolScope::tenantId();
        if ($tenantId <= 0) {
            $tenantId = $this->defaultTenantId();
        }

        return $tenantId;
    }

    private function poolPayload(string $table, ?int $poolId = null): array
    {
        if (! Schema::hasColumn($table, 'pool_id')) {
            return [];
        }

        $resolvedPoolId = $poolId ?? $this->writablePoolContextId();

        return ['pool_id' => $resolvedPoolId > 0 ? $resolvedPoolId : null];
    }

    private function resolveWritablePoolIdFromRequest(Request $request, string $table, int $existingPoolId = 0, bool $isCreate = false): int
    {
        if (! Schema::hasColumn($table, 'pool_id')) {
            return 0;
        }

        $activePoolId = (int) session('active_pool_id', 0);
        $requestedPoolId = (int) $request->input('pool_id', 0);

        if ($activePoolId > 0) {
            return PoolScope::canAccessPool($activePoolId, auth()->id()) ? $activePoolId : -1;
        }

        if ($requestedPoolId > 0) {
            return PoolScope::canAccessPool($requestedPoolId, auth()->id()) ? $requestedPoolId : -1;
        }

        if (! $isCreate && $existingPoolId > 0) {
            return PoolScope::canAccessPool($existingPoolId, auth()->id()) ? $existingPoolId : -1;
        }

        return 0;
    }

    /**
     * @param  array<int, int>  $poolIds
     * @return array<int, string>
     */
    private function poolNameMap(array $poolIds): array
    {
        $poolIds = array_values(array_unique(array_filter(array_map('intval', $poolIds), static fn (int $id): bool => $id > 0)));
        if ($poolIds === [] || ! Schema::hasTable('pools')) {
            return [];
        }

        return DB::table('pools')
            ->whereIn('id', $poolIds)
            ->pluck('name', 'id')
            ->mapWithKeys(static fn ($name, $id): array => [(int) $id => (string) $name])
            ->all();
    }

    private function poolResolveErrorMessage(int $code): string
    {
        return match ($code) {
            -1 => 'Pool tidak ditemukan atau Anda tidak memiliki akses.',
            default => 'Pilih pool terlebih dahulu saat mode Semua Pool.',
        };
    }

    private function defaultTenantId(): int
    {
        if (! Schema::hasTable('tenants')) {
            return 0;
        }

        $tenantId = (int) (DB::table('tenants')->where('id', 1)->value('id') ?? 0);
        if ($tenantId > 0) {
            return $tenantId;
        }

        return (int) (DB::table('tenants')->where('slug', 'qbus-default')->value('id') ?? 0);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseTableAlias(string $table): array
    {
        $table = trim($table);
        if (preg_match('/^([a-z0-9_]+)(?:\s+as\s+([a-z0-9_]+))?$/i', $table, $matches) === 1) {
            return [$matches[1], $matches[2] ?? ''];
        }

        return [$table, ''];
    }

    private function tripAssignmentsHasArmadaId(): bool
    {
        if ($this->tripAssignmentsHasArmadaId === null) {
            $this->tripAssignmentsHasArmadaId = Schema::hasTable('trip_assignments') && Schema::hasColumn('trip_assignments', 'armada_id');
        }

        return $this->tripAssignmentsHasArmadaId;
    }

    private function tripAssignmentsHasArmadaNopol(): bool
    {
        if ($this->tripAssignmentsHasArmadaNopol === null) {
            $this->tripAssignmentsHasArmadaNopol = Schema::hasTable('trip_assignments') && Schema::hasColumn('trip_assignments', 'armada_nopol');
        }

        return $this->tripAssignmentsHasArmadaNopol;
    }

    private function tripAssignmentsHasStatus(): bool
    {
        if ($this->tripAssignmentsHasStatus === null) {
            $this->tripAssignmentsHasStatus = Schema::hasTable('trip_assignments') && Schema::hasColumn('trip_assignments', 'status');
        }

        return $this->tripAssignmentsHasStatus;
    }

    private function driversHasArmadaId(): bool
    {
        if ($this->driversHasArmadaId === null) {
            $this->driversHasArmadaId = Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'armada_id');
        }

        return $this->driversHasArmadaId;
    }

    private function driversHasArmadaNopol(): bool
    {
        if ($this->driversHasArmadaNopol === null) {
            $this->driversHasArmadaNopol = Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'armada_nopol');
        }

        return $this->driversHasArmadaNopol;
    }

    /**
     * Keep the charter reservation flow connected to the Customer Carter master data.
     *
     * @param  array<string, mixed>  $payload
     */
    private function syncCustomerCharterFromCharterPayload(array $payload): void
    {
        if (! Schema::hasTable('customer_charter')) {
            return;
        }

        $name = strtoupper(trim((string) ($payload['name'] ?? '')));
        $phone = trim((string) ($payload['phone'] ?? ''));

        if ($name === '' || $phone === '') {
            return;
        }

        $customerPayload = [
            'nama' => $name,
            'no_hp' => $phone,
            'alamat' => $this->nullable($payload['pickup_point'] ?? null),
            'company' => $this->nullable($payload['company_name'] ?? null),
        ];
        $poolId = Schema::hasColumn('customer_charter', 'pool_id')
            ? (int) ($payload['pool_id'] ?? PoolScope::customerPoolId())
            : 0;

        $existingQuery = DB::table('customer_charter')->where('no_hp', $phone);
        PoolScope::applyCustomerCharterScope($existingQuery, 'customer_charter');
        $this->applyWriteTenantScopeIfExists($existingQuery, 'customer_charter');
        $existingId = (int) ($existingQuery->value('id') ?? 0);

        if ($existingId > 0) {
            $updatePayload = [
                'nama' => $customerPayload['nama'],
            ];

            if ($customerPayload['alamat'] !== null) {
                $updatePayload['alamat'] = $customerPayload['alamat'];
            }

            if ($customerPayload['company'] !== null) {
                $updatePayload['company'] = $customerPayload['company'];
            }

            $customerUpdate = DB::table('customer_charter')->where('id', $existingId);
            PoolScope::applyCustomerCharterScope($customerUpdate, 'customer_charter');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_charter');
            $customerUpdate->update($updatePayload);
            $this->assignCustomerPoolIfMissing($existingId, $poolId, 'customer_charter');

            return;
        }

        try {
            DB::table('customer_charter')->insert(array_merge(
                $customerPayload,
                $poolId > 0 ? ['pool_id' => $poolId] : [],
                $this->tenantPayload('customer_charter'),
                ['created_at' => now()],
            ));
        } catch (QueryException) {
            $customerUpdate = DB::table('customer_charter')->where('no_hp', $phone);
            PoolScope::applyCustomerCharterScope($customerUpdate, 'customer_charter');
            $this->applyWriteTenantScopeIfExists($customerUpdate, 'customer_charter');
            $customerUpdate->update([
                'nama' => $customerPayload['nama'],
                'alamat' => $customerPayload['alamat'],
                'company' => $customerPayload['company'],
            ]);
            $existingAfterError = DB::table('customer_charter')->where('no_hp', $phone);
            PoolScope::applyCustomerCharterScope($existingAfterError, 'customer_charter');
            $this->applyWriteTenantScopeIfExists($existingAfterError, 'customer_charter');
            $this->assignCustomerPoolIfMissing((int) ($existingAfterError->value('id') ?? 0), $poolId, 'customer_charter');
        }
    }

    /**
     * Reuse Carter reservations as route presets for the Master Carter menu.
     *
     * @param  array<string, mixed>  $payload
     */
    private function syncMasterCarterFromCharterPayload(array $payload): void
    {
        if (! Schema::hasTable('master_carter')) {
            return;
        }

        $origin = trim((string) ($payload['pickup_point'] ?? ''));
        $destination = trim((string) ($payload['drop_point'] ?? ''));

        if ($origin === '' || $destination === '') {
            return;
        }

        $duration = trim((string) ($payload['layanan'] ?? '')) ?: 'Regular';
        $routePayload = [
            'name' => strtoupper($origin.' - '.$destination),
            'origin' => $origin,
            'destination' => $destination,
            'duration' => $duration,
            'rental_price' => (float) ($payload['price'] ?? 0),
            'bop_price' => (float) ($payload['bop_price'] ?? 0),
        ];
        $poolId = (int) ($payload['pool_id'] ?? 0);
        $routePayload = array_merge($routePayload, $this->tenantPayload('master_carter'), $this->poolPayload('master_carter', $poolId > 0 ? $poolId : null));

        try {
            $existingQuery = DB::table('master_carter')
                ->whereRaw("UPPER(COALESCE(origin, '')) = ?", [strtoupper($origin)])
                ->whereRaw("UPPER(COALESCE(destination, '')) = ?", [strtoupper($destination)])
                ->whereRaw("UPPER(COALESCE(duration, '')) = ?", [strtoupper($duration)]);
            $this->applyWriteTenantScopeIfExists($existingQuery, 'master_carter');
            $this->applyPoolScopeIfExists($existingQuery, 'master_carter', '', $poolId > 0 ? $poolId : null);
            $existingId = (int) ($existingQuery->value('id') ?? 0);

            if ($existingId > 0) {
                DB::table('master_carter')->where('id', $existingId)->update($routePayload);

                return;
            }

            if (Schema::hasColumn('master_carter', 'created_at')) {
                $routePayload['created_at'] = now();
            }

            DB::table('master_carter')->insert($routePayload);
        } catch (QueryException) {
            // Master Carter is a reusable preset; do not block the reservation if syncing fails.
        }
    }

    // ──────────────────────────────────────────────
    // SaaS: Tenants CRUD
    // ──────────────────────────────────────────────

    public function tenantsIndex(Request $request): JsonResponse
    {
        if (! Schema::hasTable('tenants')) {
            return $this->ok(['tenants' => [], 'plans' => [], 'pagination' => $this->paginationMeta(0, 1, 20)]);
        }

        $q = trim((string) $request->query('q', ''));
        $query = DB::table('tenants')
            ->leftJoin('subscriptions', function ($join) {
                $join->on('tenants.id', '=', 'subscriptions.tenant_id')
                    ->whereRaw('subscriptions.id = (SELECT id FROM subscriptions s2 WHERE s2.tenant_id = tenants.id ORDER BY s2.created_at DESC LIMIT 1)');
            })
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->orderBy('tenants.created_at', 'desc');

        $hasUsersTenant = Schema::hasColumn('users', 'tenant_id');
        $hasPoolsTenant = Schema::hasColumn('pools', 'tenant_id');

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

        $query->select(
            'tenants.*',
            'subscriptions.id as subscription_id',
            'subscriptions.status as subscription_status',
            'subscriptions.trial_ends_at',
            'subscriptions.starts_at',
            'subscriptions.ends_at',
            'subscriptions.billing_interval',
            'plans.name as plan_name',
            'plans.slug as plan_slug',
            $hasUsersTenant ? DB::raw('COALESCE(tenant_user_counts.user_count, 0) as user_count') : DB::raw('0 as user_count'),
            $hasPoolsTenant ? DB::raw('COALESCE(tenant_pool_counts.pool_count, 0) as pool_count') : DB::raw('0 as pool_count'),
        );

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function (Builder $builder) use ($qLike): void {
                $builder
                    ->where('tenants.name', 'like', $qLike)
                    ->orWhere('tenants.slug', 'like', $qLike)
                    ->orWhere('tenants.email', 'like', $qLike);
            });
        }

        [$page, $perPage] = $this->paginationParams($request);
        $result = $this->paginateQuery($query, $page, $perPage);
        $tenants = collect($result['data'])->map(function ($t) {
            return [
                'id' => (int) $t->id,
                'name' => (string) ($t->name ?? ''),
                'slug' => (string) ($t->slug ?? ''),
                'email' => (string) ($t->email ?? ''),
                'phone' => (string) ($t->phone ?? ''),
                'address' => (string) ($t->address ?? ''),
                'domain' => $t->domain ?? null,
                'logo_url' => $t->logo_url ?? null,
                'status' => (string) ($t->status ?? 'active'),
                'target_revenue' => (float) ($t->target_revenue ?? 0),
                'subscription_id' => $t->subscription_id ? (int) $t->subscription_id : null,
                'subscription_status' => (string) ($t->subscription_status ?? ''),
                'trial_ends_at' => $t->trial_ends_at,
                'starts_at' => $t->starts_at,
                'ends_at' => $t->ends_at,
                'billing_interval' => (string) ($t->billing_interval ?? 'monthly'),
                'plan_name' => (string) ($t->plan_name ?? 'Belum Ada'),
                'plan_slug' => (string) ($t->plan_slug ?? ''),
                'user_count' => (int) ($t->user_count ?? 0),
                'pool_count' => (int) ($t->pool_count ?? 0),
                'created_at' => $t->created_at,
            ];
        })->all();

        $plans = Schema::hasTable('plans') ? DB::table('plans')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug', 'price_monthly']) : [];

        return $this->ok([
            'tenants' => $tenants,
            'plans' => $plans,
            'pagination' => $result['meta'],
        ]);
    }

    public function tenantsSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        if (! Schema::hasTable('tenants')) {
            return $this->error('Tabel tenants belum tersedia.', 409);
        }

        $id = (int) $request->input('id', 0);
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('tenants', 'slug')->ignore($id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'domain' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending_payment', 'active', 'suspended', 'canceled'])],
            'target_revenue' => ['nullable', 'numeric', 'min:0'],
            // For new tenants, optionally set initial plan
            'plan_id' => ['nullable', 'integer', 'min:1', 'exists:plans,id'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:90'],
        ]);

        $payload = [
            'name' => trim((string) $data['name']),
            'slug' => trim((string) $data['slug']),
            'email' => $this->nullable($data['email'] ?? null),
            'phone' => $this->nullable($data['phone'] ?? null),
            'address' => $this->nullable($data['address'] ?? null),
            'domain' => $this->nullable($data['domain'] ?? null),
            'status' => (string) ($data['status'] ?? 'active'),
            'target_revenue' => (float) ($data['target_revenue'] ?? 0),
        ];

        if ($id <= 0) {
            try {
                $result = app(TenantProvisioningService::class)->provisionStandaloneTenant([
                    ...$payload,
                    'plan_id' => $data['plan_id'] ?? null,
                    'trial_days' => $data['trial_days'] ?? config('saas.trial_days', 14),
                    'billing_interval' => 'monthly',
                ]);

                return $this->ok([
                    'message' => 'Tenant created.',
                    'id' => (int) $result['tenant_id'],
                    'subscription_status' => $result['subscription_status'] ?? null,
                    'invoice_id' => $result['invoice_id'] ?? 0,
                ], 201);
            } catch (\Throwable $e) {
                return $this->error('Gagal membuat tenant: '.$e->getMessage(), 422);
            }
        }

        $updatePayload = [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ];
        foreach (['email', 'phone', 'address', 'domain', 'status', 'target_revenue'] as $field) {
            if (array_key_exists($field, $data)) {
                $updatePayload[$field] = $payload[$field];
            }
        }

        DB::table('tenants')->where('id', $id)->update(array_merge($updatePayload, ['updated_at' => now()]));

        return $this->ok([
            'message' => 'Tenant updated.',
            'id' => $id,
        ]);
    }

    public function tenantsDelete(int $id): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        if (! Schema::hasTable('tenants')) {
            return $this->error('Tabel tenants belum tersedia.', 409);
        }

        $tenant = DB::table('tenants')->where('id', $id)->first();
        if (! $tenant) {
            return $this->error('Tenant tidak ditemukan.', 404);
        }

        return DB::transaction(function () use ($id, $tenant): JsonResponse {
            // Instead of hard-deleting, mark as canceled (data retention period)
            DB::table('tenants')->where('id', $id)->update([
                'status' => 'canceled',
                'updated_at' => now(),
            ]);

            // Cancel the subscription
            if (Schema::hasTable('subscriptions')) {
                DB::table('subscriptions')
                    ->where('tenant_id', $id)
                    ->whereIn('status', ['pending_payment', 'trial', 'active', 'past_due'])
                    ->update([
                        'status' => 'canceled',
                        'canceled_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            return $this->ok(['message' => "Tenant '{$tenant->name}' telah dicancel."]);
        });
    }

    // ──────────────────────────────────────────────
    // SaaS: Subscriptions
    // ──────────────────────────────────────────────

    public function subscriptionsIndex(Request $request): JsonResponse
    {
        if (! Schema::hasTable('subscriptions') || ! Schema::hasTable('tenants') || ! Schema::hasTable('plans')) {
            return $this->ok(['subscriptions' => [], 'tenants' => [], 'plans' => [], 'pagination' => $this->paginationMeta(0, 1, 20)]);
        }

        $status = trim((string) $request->query('status', ''));
        $tenantId = (int) $request->query('tenant_id', 0);
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select(
                'subscriptions.*',
                'tenants.name as tenant_name',
                'tenants.slug as tenant_slug',
                'plans.name as plan_name',
                'plans.slug as plan_slug',
                'plans.price_monthly',
            )
            ->orderBy('subscriptions.created_at', 'desc');

        if ($status !== '') {
            $query->where('subscriptions.status', $status);
        }
        if ($tenantId > 0) {
            $query->where('subscriptions.tenant_id', $tenantId);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where('tenants.name', 'like', $qLike);
        }

        [$page, $perPage] = $this->paginationParams($request);
        $result = $this->paginateQuery($query, $page, $perPage);

        $subscriptions = collect($result['data'])->map(function ($s) {
            return [
                'id' => (int) $s->id,
                'tenant_id' => (int) $s->tenant_id,
                'tenant_name' => (string) $s->tenant_name,
                'tenant_slug' => (string) $s->tenant_slug,
                'plan_id' => (int) $s->plan_id,
                'plan_name' => (string) $s->plan_name,
                'plan_slug' => (string) $s->plan_slug,
                'price_monthly' => (float) $s->price_monthly,
                'status' => (string) $s->status,
                'trial_ends_at' => $s->trial_ends_at,
                'starts_at' => $s->starts_at,
                'ends_at' => $s->ends_at,
                'billing_interval' => (string) ($s->billing_interval ?? 'monthly'),
                'canceled_at' => $s->canceled_at,
                'grace_period_days' => (int) ($s->grace_period_days ?? 7),
                'notes' => $s->notes ?? null,
                'created_at' => $s->created_at,
            ];
        })->all();

        $tenants = DB::table('tenants')->orderBy('name')->get(['id', 'name', 'slug']);
        $plans = DB::table('plans')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug', 'price_monthly']);

        return $this->ok([
            'subscriptions' => $subscriptions,
            'tenants' => $tenants,
            'plans' => $plans,
            'status_options' => ['pending_payment', 'trial', 'active', 'past_due', 'suspended', 'canceled', 'expired'],
            'pagination' => $result['meta'],
        ]);
    }

    public function subscriptionsSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        if (! Schema::hasTable('subscriptions')) {
            return $this->error('Tabel subscriptions belum tersedia.', 409);
        }

        $id = (int) $request->input('id', 0);
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['required_without:id', 'integer', 'min:1', 'exists:tenants,id'],
            'plan_id' => ['nullable', 'integer', 'min:1', 'exists:plans,id'],
            'status' => ['nullable', Rule::in(['pending_payment', 'trial', 'active', 'past_due', 'suspended', 'canceled', 'expired'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'billing_interval' => ['nullable', Rule::in(['monthly', 'yearly'])],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($id, $data): JsonResponse {
            if ($id > 0) {
                $payload = [];
                if (isset($data['plan_id'])) {
                    $payload['plan_id'] = (int) $data['plan_id'];
                }
                if (isset($data['status'])) {
                    $payload['status'] = (string) $data['status'];
                    if ($data['status'] === 'canceled') {
                        $payload['canceled_at'] = now();
                    }
                }
                if (isset($data['starts_at'])) {
                    $payload['starts_at'] = $data['starts_at'];
                }
                if (isset($data['ends_at'])) {
                    $payload['ends_at'] = $data['ends_at'];
                }
                if (isset($data['billing_interval'])) {
                    $payload['billing_interval'] = $data['billing_interval'];
                }
                if (isset($data['grace_period_days'])) {
                    $payload['grace_period_days'] = (int) $data['grace_period_days'];
                }
                if (isset($data['notes'])) {
                    $payload['notes'] = $this->nullable($data['notes']);
                }

                if ($payload !== []) {
                    $payload['updated_at'] = now();
                    DB::table('subscriptions')->where('id', $id)->update($payload);
                }

                $sub = DB::table('subscriptions')->where('id', $id)->first();
                $message = 'Subscription updated.';
            } else {
                $tenantId = (int) $data['tenant_id'];
                $planId = (int) ($data['plan_id'] ?? DB::table('plans')->where('slug', 'starter')->value('id') ?? 1);
                $status = (string) ($data['status'] ?? 'active');
                $startsAt = isset($data['starts_at']) ? $data['starts_at'] : now()->toDateString();
                $endsAt = $data['ends_at'] ?? now()->addMonth()->toDateString();

                $subId = (int) DB::table('subscriptions')->insertGetId([
                    'tenant_id' => $tenantId,
                    'plan_id' => $planId,
                    'status' => $status,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'billing_interval' => (string) ($data['billing_interval'] ?? 'monthly'),
                    'grace_period_days' => (int) ($data['grace_period_days'] ?? 7),
                    'notes' => $this->nullable($data['notes'] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $id = $subId;
                $sub = DB::table('subscriptions')->where('id', $id)->first();
                $message = 'Subscription created.';
            }

            return $this->ok([
                'message' => $message,
                'id' => $id,
                'subscription' => $sub,
            ]);
        });
    }

    // ──────────────────────────────────────────────
    // SaaS: Plans
    // ──────────────────────────────────────────────

    public function plansIndex(Request $request): JsonResponse
    {
        if (! Schema::hasTable('plans')) {
            return $this->ok(['plans' => []]);
        }

        $plans = DB::table('plans')
            ->orderBy('sort_order')
            ->get();

        $featuresByPlan = collect();
        if ($plans->isNotEmpty() && Schema::hasTable('plan_feature') && Schema::hasTable('feature_gates')) {
            $featuresByPlan = DB::table('plan_feature')
                ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
                ->whereIn('plan_feature.plan_id', $plans->pluck('id')->all())
                ->select(
                    'plan_feature.plan_id',
                    'feature_gates.feature_key',
                    'feature_gates.feature_name',
                    'feature_gates.feature_group',
                    'feature_gates.is_core',
                    'plan_feature.max_value',
                )
                ->orderBy('feature_gates.feature_group')
                ->orderBy('feature_gates.feature_name')
                ->get()
                ->groupBy('plan_id')
                ->map(function (Collection $rows): array {
                    return $rows
                        ->map(function ($f) {
                            return [
                                'feature_key' => (string) $f->feature_key,
                                'feature_name' => (string) $f->feature_name,
                                'feature_group' => (string) $f->feature_group,
                                'is_core' => (bool) $f->is_core,
                                'max_value' => $f->max_value,
                            ];
                        })
                        ->all();
                });
        }

        $plans = $plans
            ->map(function ($p) use ($featuresByPlan) {
                return [
                    'id' => (int) $p->id,
                    'name' => (string) $p->name,
                    'slug' => (string) $p->slug,
                    'description' => (string) ($p->description ?? ''),
                    'price_monthly' => (float) $p->price_monthly,
                    'price_yearly' => (float) $p->price_yearly,
                    'max_pools' => (int) $p->max_pools,
                    'max_users' => (int) $p->max_users,
                    'max_armadas' => (int) $p->max_armadas,
                    'max_routes' => (int) $p->max_routes,
                    'max_drivers' => (int) $p->max_drivers,
                    'max_charters_per_month' => (int) $p->max_charters_per_month,
                    'has_seat_map' => (bool) $p->has_seat_map,
                    'has_pdf_export' => (bool) $p->has_pdf_export,
                    'has_csv_export' => (bool) $p->has_csv_export,
                    'has_online_booking' => (bool) $p->has_online_booking,
                    'has_analytics' => (bool) $p->has_analytics,
                    'has_whatsapp_api' => (bool) $p->has_whatsapp_api,
                    'has_custom_domain' => (bool) $p->has_custom_domain,
                    'has_custom_roles' => (bool) $p->has_custom_roles,
                    'support_priority' => (string) $p->support_priority,
                    'sort_order' => (int) $p->sort_order,
                    'is_active' => (bool) $p->is_active,
                    'features' => $featuresByPlan->get($p->id, []),
                ];
            })
            ->all();

        return $this->ok(['plans' => $plans]);
    }

    public function plansSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        if (! Schema::hasTable('plans')) {
            return $this->error('Tabel plans belum tersedia.', 409);
        }

        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return $this->error('Plan ID diperlukan untuk update. Plan baru hanya bisa dibuat via migration.', 422);
        }

        $data = $request->validate([
            'id' => ['required', 'integer', 'min:1', 'exists:plans,id'],
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'max_pools' => ['nullable', 'integer', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
            'max_armadas' => ['nullable', 'integer', 'min:0'],
            'max_routes' => ['nullable', 'integer', 'min:0'],
            'max_drivers' => ['nullable', 'integer', 'min:0'],
            'max_charters_per_month' => ['nullable', 'integer', 'min:0'],
            'has_seat_map' => ['nullable', 'boolean'],
            'has_pdf_export' => ['nullable', 'boolean'],
            'has_csv_export' => ['nullable', 'boolean'],
            'has_online_booking' => ['nullable', 'boolean'],
            'has_analytics' => ['nullable', 'boolean'],
            'has_whatsapp_api' => ['nullable', 'boolean'],
            'has_custom_domain' => ['nullable', 'boolean'],
            'has_custom_roles' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            // Feature overrides: { feature_key: max_value }
            'features' => ['nullable', 'array'],
            'features.*.feature_key' => ['required', 'string', 'exists:feature_gates,feature_key'],
            'features.*.max_value' => ['nullable', 'integer', 'min:0'],
        ]);

        $booleanFields = ['has_seat_map', 'has_pdf_export', 'has_csv_export', 'has_online_booking', 'has_analytics', 'has_whatsapp_api', 'has_custom_domain', 'has_custom_roles', 'is_active'];

        return DB::transaction(function () use ($id, $data, $booleanFields): JsonResponse {
            $payload = [];
            $planFields = ['name', 'description', 'price_monthly', 'price_yearly', 'max_pools', 'max_users', 'max_armadas', 'max_routes', 'max_drivers', 'max_charters_per_month'];

            foreach ($planFields as $field) {
                if (isset($data[$field])) {
                    $payload[$field] = $data[$field];
                }
            }
            foreach ($booleanFields as $field) {
                if (isset($data[$field])) {
                    $payload[$field] = (bool) $data[$field];
                }
            }

            if ($payload !== []) {
                $payload['updated_at'] = now();
                DB::table('plans')->where('id', $id)->update($payload);
            }

            // Update feature mappings
            if (isset($data['features']) && Schema::hasTable('plan_feature') && Schema::hasTable('feature_gates')) {
                foreach ($data['features'] as $featureOverride) {
                    $gateId = DB::table('feature_gates')->where('feature_key', $featureOverride['feature_key'])->value('id');
                    if ($gateId) {
                        DB::table('plan_feature')->updateOrInsert(
                            ['plan_id' => $id, 'feature_gate_id' => $gateId],
                            ['max_value' => $featureOverride['max_value'] ?? null, 'updated_at' => now()],
                        );
                    }
                }
            }

            return $this->ok(['message' => 'Plan updated.', 'id' => $id]);
        });
    }

    // ──────────────────────────────────────────────
    // SaaS: Invoices
    // ──────────────────────────────────────────────

    public function invoicesIndex(Request $request): JsonResponse
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return $this->ok([
                'invoices' => [],
                'summary' => $this->emptyInvoiceSummary(),
                'pagination' => $this->paginationMeta(0, 1, 20),
            ]);
        }

        $status = trim((string) $request->query('status', ''));
        $tenantId = (int) $request->query('tenant_id', 0);
        $q = trim((string) $request->query('q', ''));
        $hasDueDateColumn = Schema::hasColumn('invoice_subscriptions', 'due_date');
        $hasPaidAtColumn = Schema::hasColumn('invoice_subscriptions', 'paid_at');

        $query = DB::table('invoice_subscriptions')
            ->join('tenants', 'invoice_subscriptions.tenant_id', '=', 'tenants.id')
            ->leftJoin('subscriptions', 'invoice_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select(
                'invoice_subscriptions.*',
                'tenants.name as tenant_name',
                'tenants.slug as tenant_slug',
                'plans.name as plan_name',
            )
            ->orderBy('invoice_subscriptions.created_at', 'desc');

        if ($status !== '') {
            if ($status === 'overdue' && $hasDueDateColumn) {
                $query->where(function (Builder $overdue) {
                    $overdue
                        ->where('invoice_subscriptions.status', 'overdue')
                        ->orWhere(function (Builder $pendingOverdue) {
                            $pendingOverdue
                                ->where('invoice_subscriptions.status', 'pending')
                                ->whereDate('invoice_subscriptions.due_date', '<', now()->toDateString());
                        });
                });
            } else {
                $query->where('invoice_subscriptions.status', $status);
            }
        }
        if ($tenantId > 0) {
            $query->where('invoice_subscriptions.tenant_id', $tenantId);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function (Builder $search) use ($qLike): void {
                $search
                    ->where('invoice_subscriptions.invoice_number', 'like', $qLike)
                    ->orWhere('tenants.name', 'like', $qLike)
                    ->orWhere('tenants.slug', 'like', $qLike)
                    ->orWhere('plans.name', 'like', $qLike);
            });
        }

        [$page, $perPage] = $this->paginationParams($request);
        $result = $this->paginateQuery($query, $page, $perPage);

        $invoices = collect($result['data'])->map(function ($inv) {
            return [
                'id' => (int) $inv->id,
                'tenant_id' => (int) $inv->tenant_id,
                'tenant_name' => (string) $inv->tenant_name,
                'tenant_slug' => (string) $inv->tenant_slug,
                'subscription_id' => (int) $inv->subscription_id,
                'invoice_number' => (string) $inv->invoice_number,
                'amount' => (float) $inv->amount,
                'status' => (string) $inv->status,
                'due_date' => $inv->due_date ?? null,
                'paid_at' => $inv->paid_at ?? null,
                'payment_method' => $inv->payment_method ?? null,
                'payment_gateway' => $inv->payment_gateway ?? 'Mayar',
                'gateway_reference' => $inv->gateway_reference ?? null,
                'gateway_checkout_url' => $inv->gateway_checkout_url ?? null,
                'gateway_status' => $inv->gateway_status ?? null,
                'gateway_paid_at' => $inv->gateway_paid_at ?? null,
                'plan_name' => (string) ($inv->plan_name ?? ''),
                'notes' => $inv->notes ?? null,
                'created_at' => $inv->created_at ?? null,
            ];
        })->all();

        return $this->ok([
            'invoices' => $invoices,
            'summary' => $this->invoiceSummary($hasDueDateColumn, $hasPaidAtColumn),
            'status_options' => ['pending', 'paid', 'overdue', 'failed', 'refunded'],
            'pagination' => $result['meta'],
        ]);
    }

    /**
     * @return array{pending: int, verification: int, paid_month: int, overdue: int, total_amount_pending: float}
     */
    private function emptyInvoiceSummary(): array
    {
        return [
            'pending' => 0,
            'verification' => 0,
            'paid_month' => 0,
            'overdue' => 0,
            'total_amount_pending' => 0.0,
        ];
    }

    /**
     * @return array{pending: int, verification: int, paid_month: int, overdue: int, total_amount_pending: float}
     */
    private function invoiceSummary(bool $hasDueDateColumn, bool $hasPaidAtColumn): array
    {
        $base = DB::table('invoice_subscriptions');

        $pending = (clone $base)->where('status', 'pending');

        $paidMonth = 0;
        if ($hasPaidAtColumn) {
            $paidMonth = (int) (clone $base)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        }

        $overdue = (clone $base)->where('status', 'overdue');
        if ($hasDueDateColumn) {
            $overdue->orWhere(function (Builder $query): void {
                $query
                    ->where('status', 'pending')
                    ->whereDate('due_date', '<', now()->toDateString());
            });
        }

        return [
            'pending' => (int) $pending->count(),
            'verification' => 0,
            'paid_month' => $paidMonth,
            'overdue' => (int) $overdue->count(),
            'total_amount_pending' => (float) (clone $base)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount'),
        ];
    }

    public function invoicesMarkPaid(int $id, Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        if (! Schema::hasTable('invoice_subscriptions')) {
            return $this->error('Tabel invoice belum tersedia.', 409);
        }

        $invoice = DB::table('invoice_subscriptions')->where('id', $id)->first();
        if (! $invoice) {
            return $this->error('Invoice tidak ditemukan.', 404);
        }

        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        PaymentGateway::markInvoicePaid($id, (string) ($this->nullable($data['payment_method'] ?? 'Admin Correction') ?? 'Admin Correction'));

        return $this->ok(['message' => 'Invoice marked as paid.']);
    }

    // ──────────────────────────────────────────────
    // SaaS: Payment Settings
    // ──────────────────────────────────────────────

    public function paymentSettingsGet(): JsonResponse
    {
        $settings = $this->loadPaymentSettings();

        return $this->ok(['settings' => $settings]);
    }

    public function paymentSettingsSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('platform.manage')) {
            return $response;
        }

        return $this->ok([
            'message' => 'Mayar dikonfigurasi melalui environment server.',
            'settings' => $this->loadPaymentSettings(),
        ]);
    }

    private function loadPaymentSettings(): array
    {
        $apiKey = trim((string) config('mayar.api_key'));
        $apiUrl = rtrim((string) config('mayar.api_url', 'https://api.mayar.id'), '/');

        return [
            'gateway' => 'Mayar',
            'enabled' => (bool) config('mayar.enabled', false),
            'configured' => $apiKey !== '',
            'api_url' => $apiUrl,
            'payment_create_path' => (string) config('mayar.payment_create_path', '/hl/v1/payment/create'),
            'webhook_url' => route('api.webhooks.mayar', absolute: true),
            'webhook_secret_configured' => trim((string) config('mayar.webhook_secret')) !== '',
        ];
    }

    private function ok(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $data), $status);
    }

    private function error(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge(['success' => false, 'error' => $message], $extra), $status);
    }
}
