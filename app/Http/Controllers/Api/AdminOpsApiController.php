<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentGateway;
use App\Services\TenantProvisioningService;
use App\Support\AccessControl;
use App\Support\ActivityLog;
use App\Support\FeatureGate;
use App\Support\ManifestLifecycle;
use App\Support\PoolScope;
use App\Support\RoleAccessData;
use App\Support\SegmentName;
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

    private ?bool $driversHasKategoriColumn = null;

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

        $scheduleSegmentsPivot = [];
        if (Schema::hasTable('schedule_segment')) {
            $scheduleIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
            if (! empty($scheduleIds)) {
                $pivots = DB::table('schedule_segment')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->get(['schedule_id', 'segment_id', 'jam_pickup']);
                foreach ($pivots as $pivot) {
                    $sId = (int) $pivot->schedule_id;
                    if (! isset($scheduleSegmentsPivot[$sId])) {
                        $scheduleSegmentsPivot[$sId] = [];
                    }
                    $scheduleSegmentsPivot[$sId][(int) $pivot->segment_id] = substr((string) $pivot->jam_pickup, 0, 5);
                }
            }
        }

        $segmentCache = [];
        $rows = $rows->map(function ($row) use (&$segmentCache, $scheduleSegmentsPivot) {
            $routeId = (int) ($row->route_id ?? 0);
            $routeName = trim((string) ($row->route_name ?? $row->rute ?? ''));
            $cacheKey = $routeId.'|'.$routeName;

            if (! array_key_exists($cacheKey, $segmentCache)) {
                $segmentCache[$cacheKey] = $this->scheduleSegmentsForRoute($routeId, $routeName);
            }

            $scheduleJam = substr((string) ($row->jam ?? ''), 0, 5);
            $scheduleId = (int) ($row->id ?? 0);
            $explicitMappings = $scheduleSegmentsPivot[$scheduleId] ?? [];

            $segmentMatches = array_values(array_filter(
                $segmentCache[$cacheKey],
                static function (array $segment) use ($scheduleJam, $explicitMappings): bool {
                    if (! empty($explicitMappings)) {
                        return isset($explicitMappings[$segment['id']]);
                    }

                    $jamPickups = $segment['jam_pickups'] ?? [];

                    return in_array($scheduleJam, $jamPickups, true)
                        || (string) ($segment['jam'] ?? '') === $scheduleJam;
                },
            ));

            // Map explicitly selected pickup
            $segmentMatches = array_map(function ($segment) use ($explicitMappings) {
                if (! empty($explicitMappings) && isset($explicitMappings[$segment['id']])) {
                    $segment['jam_pickups'] = [$explicitMappings[$segment['id']]];
                }

                return $segment;
            }, $segmentMatches);

            $row->segment_matches = $segmentMatches;
            $segmentJamPickups = [];
            foreach ($segmentMatches as $segment) {
                foreach ($segment['jam_pickups'] ?? [] as $jam) {
                    $normalizedJam = SegmentName::jam($jam);
                    if ($normalizedJam !== '') {
                        $segmentJamPickups[] = $normalizedJam;
                    }
                }
            }
            $row->segment_jam_pickups = array_values(array_unique($segmentJamPickups));

            return $row;
        });

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
            'segment_configs' => ['nullable', 'array'],
            'segment_configs.*.segment_id' => ['required_with:segment_configs', 'integer', 'min:1'],
            'segment_configs.*.jam_pickup' => ['required_with:segment_configs', 'string', 'max:5'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $routeId = (int) ($data['route_id'] ?? 0);
        $routeName = trim((string) ($data['rute'] ?? ''));
        // Normalize jam: ensure it's stored as HH:MM (strip seconds if already present)
        $jamRaw = trim((string) ($data['jam'] ?? ''));
        $jam = strlen($jamRaw) === 5 ? $jamRaw : substr($jamRaw, 0, 5);

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

        $scheduleJam = substr($jam, 0, 5);
        $routeSegmentJamPickups = $this->scheduleSegmentJamOptions($routeId, $routeName);
        $segmentConfigs = is_array($data['segment_configs'] ?? null) ? $data['segment_configs'] : [];
        if (empty($segmentConfigs) && $routeSegmentJamPickups !== [] && ! in_array($scheduleJam, $routeSegmentJamPickups, true)) {
            return $this->error('Jam jadwal harus cocok dengan jam segment pada rute ini.', 422);
        }

        // Validasi duplikat dihapus agar satu jam jadwal bisa dibuat berulang kali untuk segment berbeda
        // $duplicateQuery = DB::table('schedules')->...
        // if ($duplicate) { return this->error(...); }

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
            return DB::transaction(function () use ($id, $payload, $normalizedLabels, $normalizedUnitIds, $segmentConfigs) {
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

                if (Schema::hasTable('schedule_segment')) {
                    DB::table('schedule_segment')->where('schedule_id', $scheduleId)->delete();
                    $pivotRows = [];
                    $savedSegments = [];
                    foreach ($segmentConfigs as $sc) {
                        $sId = (int) $sc['segment_id'];
                        if (isset($savedSegments[$sId])) {
                            continue;
                        }
                        $savedSegments[$sId] = true;

                        $pivotRows[] = [
                            'schedule_id' => $scheduleId,
                            'segment_id' => $sId,
                            'jam_pickup' => substr(trim((string) $sc['jam_pickup']), 0, 5),
                            'created_at' => now(),
                        ];
                    }
                    if (! empty($pivotRows)) {
                        DB::table('schedule_segment')->insert($pivotRows);
                    }
                }

                if ($id > 0) {
                    return $this->ok(['message' => 'Schedule updated.', 'id' => $scheduleId]);
                }

                return $this->ok(['message' => 'Schedule created.', 'id' => $scheduleId], 201);
            });
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            // Extract shorter postgres error message if possible
            if (preg_match('/ERROR:\s*(.+?)(?:\s+DETAIL|\s+HINT|$)/s', $msg, $m)) {
                $msg = trim($m[1]);
            }

            return $this->error('Failed saving schedule: '.$msg, 500);
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

        if (Schema::hasTable('schedule_segment')) {
            DB::table('schedule_segment')->where('schedule_id', $id)->delete();
        }
        $scheduleDelete = DB::table('schedules')->where('id', $id);
        $this->applyWriteTenantScopeIfExists($scheduleDelete, 'schedules');
        $scheduleDelete->delete();

        return $this->ok(['message' => 'Schedule deleted.']);
    }

    public function driversIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $poolId = (int) ($validated['pool_id'] ?? 0);
        [$monthStart, $monthEnd] = $this->driverPeriodBounds($validated['period'] ?? null);
        $rows = collect($this->driverRowsForMonth($monthStart, $monthEnd, $poolId));

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows
                ->filter(static fn (array $row): bool => str_contains(mb_strtolower(implode(' ', [
                    (string) ($row['nama'] ?? ''),
                    (string) ($row['phone'] ?? ''),
                    (string) ($row['category'] ?? ''),
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

    public function driversExport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $poolId = (int) ($validated['pool_id'] ?? 0);
        [$monthStart, $monthEnd, $period] = $this->driverPeriodBounds($validated['period'] ?? null, true);

        $rows = collect($this->driverRowsForMonth($monthStart, $monthEnd, $poolId));
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows
                ->filter(static fn (array $row): bool => str_contains(mb_strtolower(implode(' ', [
                    (string) ($row['nama'] ?? ''),
                    (string) ($row['phone'] ?? ''),
                    (string) ($row['category'] ?? ''),
                    (string) ($row['pool_name'] ?? ''),
                ])), $needle))
                ->values();
        }

        $exportRows = $rows->map(function (array $row): array {
            $revenue = (float) ($row['revenue'] ?? 0);
            $bop = (float) ($row['bop'] ?? 0);
            $fixedCost = (float) ($row['fixed_cost'] ?? 0);
            $gross = $revenue - $bop;
            $net = $gross - $fixedCost;
            $target = (float) ($row['target_revenue_bulanan'] ?? 0);
            $achievement = $target > 0 ? ($revenue / $target) * 100 : 0;

            return [
                'Nama Driver' => (string) ($row['nama'] ?? ''),
                'Kontak' => (string) ($row['phone'] ?? ''),
                'Kategori Driver' => (string) ($row['category'] ?? ''),
                'Pool/Wilayah' => (string) ($row['pool_name'] ?? ''),
                'Keberangkatan Rit' => (int) ($row['departure_count'] ?? 0),
                'Charter Revenue' => (float) ($row['charter_revenue'] ?? 0),
                'Keberangkatan Revenue' => (float) ($row['departure_revenue'] ?? 0),
                'Bagasi Revenue' => (float) ($row['luggage_revenue'] ?? 0),
                'Total Revenue' => $revenue,
                'Charter BOP' => (float) ($row['charter_bop'] ?? 0),
                'Keberangkatan BOP' => (float) ($row['departure_bop'] ?? 0),
                'Total BOP' => $bop,
                'Gross' => $gross,
                'Fixed Cost' => $fixedCost,
                'Net Margin' => $net,
                'Target Revenue' => $target,
                'Achievement' => $achievement,
            ];
        })->all();

        $filename = 'driver-report-'.$period.'.xlsx';
        $xlsx = $this->buildDriversXlsx($exportRows, $period);

        return response()->streamDownload(function () use ($xlsx): void {
            echo $xlsx;
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
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
            'kategori' => ['nullable', 'string', Rule::in(['Minibus', 'Mediumbus', 'Bigbus'])],
            'category' => ['nullable', 'string', Rule::in(['Minibus', 'Mediumbus', 'Bigbus'])],
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
        $driverCategory = $this->normalizeUnitCategory($data['kategori'] ?? $data['category'] ?? null);

        $payload = [
            'nama' => strtoupper(trim((string) $data['nama'])),
            'phone' => $this->nullable($data['phone'] ?? null),
        ];

        if ($this->driversHasKategoriColumn()) {
            $payload['kategori'] = $driverCategory;
        }

        if ($this->hasDriversTargetRevenueBulananColumn()) {
            $payload['target_revenue_bulanan'] = (float) ($data['target_revenue_bulanan'] ?? 0);
        }

        if ($this->hasDriversTargetRevenueTahunanColumn() && array_key_exists('target_revenue_tahunan', $data)) {
            $payload['target_revenue_tahunan'] = (float) ($data['target_revenue_tahunan'] ?? 0);
        }

        if (Schema::hasColumn('drivers', 'unit_id')) {
            $payload['unit_id'] = null;
        }

        if ($this->driversHasArmadaId()) {
            $payload['armada_id'] = null;
        }

        if ($this->driversHasArmadaNopol()) {
            $payload['armada_nopol'] = null;
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

        $poolId = $this->resolveWritablePoolIdFromRequest($request, 'luggage_services', 0, true);
        if ($poolId < 0) {
            return $this->error($this->poolResolveErrorMessage($poolId), $poolId === -1 ? 403 : 422);
        }

        $newId = DB::table('luggage_services')->insertGetId(array_merge($payload, $this->tenantPayload('luggage_services'), $this->poolPayload('luggage_services', $poolId > 0 ? $poolId : null), [
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
                's.jam',
                Schema::hasColumn('segments', 'jam_pickups')
                    ? 's.jam_pickups'
                    : DB::raw('NULL as jam_pickups'),
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
            $segments = $query->get()->map(function ($row) {
                $row->rute = SegmentName::display(
                    $row->origin ?? null,
                    $row->destination ?? null,
                    $row->rute ?? '',
                );
                $row->jam = SegmentName::jam($row->jam ?? null);
                $row->jam_pickups = SegmentName::jamList(
                    $row->jam_pickups ?? null,
                    $row->jam ?? null,
                );

                return $row;
            })->values();

            return $this->ok(['segments' => $segments]);
        }

        [$page, $perPage] = $this->paginationParams($request);
        $result = $this->paginateQuery($query, $page, $perPage);
        $result['data'] = collect($result['data'])->map(function ($row) {
            $row->rute = SegmentName::display(
                $row->origin ?? null,
                $row->destination ?? null,
                $row->rute ?? '',
            );
            $row->jam = SegmentName::jam($row->jam ?? null);
            $row->jam_pickups = SegmentName::jamList(
                $row->jam_pickups ?? null,
                $row->jam ?? null,
            );

            return $row;
        })->values();

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
            'rute' => ['nullable', 'string', 'max:120'],
            'origin' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:120'],
            'jam' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'jam_pickups' => ['nullable', 'array', 'min:1'],
            'jam_pickups.*' => ['required', 'regex:/^\d{2}:\d{2}$/'],
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

        $segmentName = SegmentName::display(
            $data['origin'] ?? null,
            $data['destination'] ?? null,
            $data['rute'] ?? '',
        );

        $jamPickups = SegmentName::jamList(
            $data['jam_pickups'] ?? null,
            $data['jam'] ?? null,
        );

        if ($jamPickups === []) {
            return $this->error('Minimal 1 jam pickup wajib diisi.', 422);
        }

        $payload = [
            'route_id' => (int) $data['route_id'],
            'rute' => $segmentName !== '' ? $segmentName : trim((string) ($data['rute'] ?? '')),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
            'jam' => SegmentName::jam($jamPickups[0]).':00',
            'harga' => (float) $data['harga'],
        ];

        if (Schema::hasColumn('segments', 'jam_pickups')) {
            $payload['jam_pickups'] = json_encode($jamPickups);
        }

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

    public function activityLogsIndex(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = (int) $request->query('limit', 0);
        if ($limit > 0) {
            $perPage = max(10, min(100, $limit));
        } else {
            [, $perPage] = $this->paginationParams($request);
        }
        $offset = max(0, ($page - 1) * $perPage);
        $items = ActivityLog::recentForTenant($perPage, $offset);
        $total = ActivityLog::countForTenant();
        $lastPage = max(1, (int) ceil($total / $perPage));

        return $this->ok([
            'logs' => $items,
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
        $tenantId = (int) PoolScope::tenantId(auth()->id());
        $activePoolId = (int) PoolScope::currentPoolId(auth()->id());
        $rangeKey = implode(':', [
            $type,
            $from,
            $to,
            $poolId,
            $routeId,
            $tenantId,
            $activePoolId,
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
        if (! Schema::hasTable('bookings')) {
            return 0.0;
        }

        $departures = $this->reportBookingDeparturesForBop($from, $to, $poolId, $routeFilter);
        if ($departures->isEmpty()) {
            return 0.0;
        }

        $lookup = $this->reportBookingBopLookup($departures, $poolId);

        return (float) $departures->sum(fn ($departure): float => $this->reportBookingDepartureBop($departure, $lookup));
    }

    /**
     * @param  array{requested?: bool, id?: int, name?: string}  $routeFilter
     */
    private function reportBookingDeparturesForBop(string $from, string $to, int $poolId = 0, array $routeFilter = []): Collection
    {
        $query = DB::table('bookings as b')
            ->whereBetween('b.tanggal', [$from, $to]);
        $this->applyNotCanceledFilter($query, 'b.status');
        $this->applyRouteScopeToQuery($query, '', 'b.rute', $poolId);
        $this->applyTenantScopeIfExists($query, 'bookings', 'b');
        $this->applyResolvedRouteFilter(
            $query,
            $routeFilter,
            Schema::hasColumn('bookings', 'route_id') ? 'b.route_id' : '',
            'b.rute',
        );

        return $query
            ->distinct()
            ->get([
                Schema::hasColumn('bookings', 'route_id') ? 'b.route_id' : DB::raw('NULL as route_id'),
                'b.rute',
                'b.tanggal',
                'b.jam',
                'b.unit',
            ]);
    }

    /**
     * @param  Collection<int, object>  $departures
     * @return array{schedule_route: array<string, float>, schedule_name: array<string, float>, route_id: array<int, float>, route_name: array<string, float>}
     */
    private function reportBookingBopLookup(Collection $departures, int $poolId = 0): array
    {
        $lookup = [
            'schedule_route' => [],
            'schedule_name' => [],
            'route_id' => [],
            'route_name' => [],
        ];

        if ($departures->isEmpty()) {
            return $lookup;
        }

        $routeIds = $departures
            ->pluck('route_id')
            ->map(static fn ($value): int => (int) $value)
            ->filter(static fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();

        if (Schema::hasTable('routes') && Schema::hasColumn('routes', 'bop')) {
            $routeQuery = DB::table('routes');
            $this->applyTenantScopeIfExists($routeQuery, 'routes');
            $this->applyRouteScopeToQuery($routeQuery, 'routes.id', 'routes.name', $poolId);
            if ($routeIds !== []) {
                $routeQuery->whereIn('id', $routeIds);
            }

            $routeSelect = [
                'id',
                'name',
                Schema::hasColumn('routes', 'origin') ? 'origin' : DB::raw('NULL as origin'),
                Schema::hasColumn('routes', 'destination') ? 'destination' : DB::raw('NULL as destination'),
                'bop',
            ];

            foreach ($routeQuery->get($routeSelect) as $route) {
                $routeId = (int) ($route->id ?? 0);
                $bop = (float) ($route->bop ?? 0);
                if ($routeId > 0) {
                    $lookup['route_id'][$routeId] = $bop;
                }

                foreach ($this->reportRouteNameCandidates($route) as $candidate) {
                    $key = $this->normalizeRouteName($candidate);
                    if ($key !== '') {
                        $lookup['route_name'][$key] = $bop;
                    }
                }
            }
        }

        if (Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'bop')) {
            $scheduleQuery = DB::table('schedules')->where('bop', '>', 0);
            $this->applyTenantScopeIfExists($scheduleQuery, 'schedules');
            $this->applyRouteScopeToQuery(
                $scheduleQuery,
                Schema::hasColumn('schedules', 'route_id') ? 'schedules.route_id' : '',
                'schedules.rute',
                $poolId,
            );

            $scheduleSelect = [
                Schema::hasColumn('schedules', 'route_id') ? 'route_id' : DB::raw('NULL as route_id'),
                'rute',
                'dow',
                'jam',
                'bop',
            ];

            foreach ($scheduleQuery->get($scheduleSelect) as $schedule) {
                $dow = (int) ($schedule->dow ?? -1);
                $time = substr(trim((string) ($schedule->jam ?? '')), 0, 5);
                $bop = (float) ($schedule->bop ?? 0);

                if ($dow < 0 || $time === '' || $bop <= 0) {
                    continue;
                }

                $routeId = (int) ($schedule->route_id ?? 0);
                if ($routeId > 0) {
                    $lookup['schedule_route'][$routeId.'|'.$dow.'|'.$time] = $bop;
                }

                $routeKey = $this->normalizeRouteName((string) ($schedule->rute ?? ''));
                if ($routeKey !== '') {
                    $lookup['schedule_name'][$routeKey.'|'.$dow.'|'.$time] = $bop;
                }
            }
        }

        return $lookup;
    }

    /**
     * @param  array{schedule_route: array<string, float>, schedule_name: array<string, float>, route_id: array<int, float>, route_name: array<string, float>}  $lookup
     */
    private function reportBookingDepartureBop(object $departure, array $lookup): float
    {
        $routeId = (int) ($departure->route_id ?? 0);
        $routeKey = $this->normalizeRouteName((string) ($departure->rute ?? ''));
        $time = substr(trim((string) ($departure->jam ?? '')), 0, 5);

        try {
            $dow = Carbon::parse((string) ($departure->tanggal ?? ''))->dayOfWeek;
        } catch (\Throwable) {
            $dow = -1;
        }

        if ($dow >= 0 && $time !== '') {
            if ($routeId > 0) {
                $scheduleKey = $routeId.'|'.$dow.'|'.$time;
                if (isset($lookup['schedule_route'][$scheduleKey])) {
                    return (float) $lookup['schedule_route'][$scheduleKey];
                }
            }

            if ($routeKey !== '') {
                $scheduleKey = $routeKey.'|'.$dow.'|'.$time;
                if (isset($lookup['schedule_name'][$scheduleKey])) {
                    return (float) $lookup['schedule_name'][$scheduleKey];
                }
            }
        }

        if ($routeId > 0 && isset($lookup['route_id'][$routeId])) {
            return (float) $lookup['route_id'][$routeId];
        }

        return $routeKey !== '' ? (float) ($lookup['route_name'][$routeKey] ?? 0) : 0.0;
    }

    /**
     * @return array<int, string>
     */
    private function reportRouteNameCandidates(object $route): array
    {
        $candidates = [(string) ($route->name ?? '')];
        $origin = trim((string) ($route->origin ?? ''));
        $destination = trim((string) ($route->destination ?? ''));

        if ($origin !== '' && $destination !== '') {
            $candidates[] = $origin.' - '.$destination;
        }

        return $candidates;
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
            default => $this->buildTablesMutationSignature(array_merge(['bookings', 'routes', 'schedules'], $poolTables)),
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

        if (! Schema::hasTable('charters')) {
            return $this->ok([
                'charters' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

        $charterColumns = array_flip(Schema::getColumnListing('charters'));
        if (! isset($charterColumns['id'])) {
            return $this->ok([
                'charters' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
        $hasMasterCarterIdColumn = $this->chartersHasMasterCarterIdColumn();
        $hasUnitIdColumn = isset($charterColumns['unit_id']);
        $hasStartDateColumn = isset($charterColumns['start_date']);
        $hasDepartureTimeColumn = isset($charterColumns['departure_time']);
        $hasPaymentStatusColumn = isset($charterColumns['payment_status']);
        $hasBopStatusColumn = isset($charterColumns['bop_status']);
        $canJoinUnits = $hasUnitIdColumn && Schema::hasTable('units');
        $unitColumns = $canJoinUnits ? array_flip(Schema::getColumnListing('units')) : [];
        $canJoinArmadas = $hasArmadaIdColumn && Schema::hasTable('armadas');
        $armadaColumns = $canJoinArmadas ? array_flip(Schema::getColumnListing('armadas')) : [];
        $selectColumn = static fn (array $columns, string $column, string $alias, string $prefix = 'c') => isset($columns[$column])
            ? "{$prefix}.{$column}"
            : DB::raw("NULL as {$alias}");

        $query = DB::table('charters as c');

        if ($canJoinUnits) {
            $query->leftJoin('units as u', 'c.unit_id', '=', 'u.id');
        }

        if ($canJoinArmadas) {
            $query->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
        }

        $select = [
            'c.id',
            $selectColumn($charterColumns, 'name', 'name'),
            $selectColumn($charterColumns, 'company_name', 'company_name'),
            $selectColumn($charterColumns, 'phone', 'phone'),
            $selectColumn($charterColumns, 'start_date', 'start_date'),
            $selectColumn($charterColumns, 'end_date', 'end_date'),
            $selectColumn($charterColumns, 'departure_time', 'departure_time'),
            $selectColumn($charterColumns, 'pickup_point', 'pickup_point'),
            $selectColumn($charterColumns, 'drop_point', 'drop_point'),
            $selectColumn($charterColumns, 'unit_id', 'unit_id'),
            $selectColumn($charterColumns, 'driver_name', 'driver_name'),
            $selectColumn($charterColumns, 'price', 'price'),
            $selectColumn($charterColumns, 'layanan', 'layanan'),
            $selectColumn($charterColumns, 'bop_price', 'bop_price'),
            $selectColumn($charterColumns, 'bop_status', 'bop_status'),
            $selectColumn($charterColumns, 'down_payment', 'down_payment'),
            $selectColumn($charterColumns, 'payment_status', 'payment_status'),
            $selectColumn($charterColumns, 'created_at', 'created_at'),
            DB::raw($hasStatusColumn
                ? 'c.status as status'
                : (
                    $hasPaymentStatusColumn && $hasBopStatusColumn
                        ? "CASE WHEN c.payment_status = 'Canceled' THEN 'canceled' WHEN c.bop_status = 'done' THEN 'done' ELSE 'active' END as status"
                        : "'active' as status"
                )),
            isset($unitColumns['nopol']) ? DB::raw('u.nopol as unit_nopol') : DB::raw('NULL as unit_nopol'),
            isset($unitColumns['category']) ? DB::raw('u.category as unit_category') : DB::raw('NULL as unit_category'),
        ];

        $select[] = $hasPoolIdColumn ? 'c.pool_id' : DB::raw('NULL as pool_id');
        $select[] = $hasMasterCarterIdColumn ? 'c.master_carter_id' : DB::raw('NULL as master_carter_id');

        if ($hasArmadaIdColumn) {
            $select[] = 'c.armada_id';
        } else {
            $select[] = DB::raw('NULL as armada_id');
        }

        if ($hasArmadaNopolColumn && $canJoinArmadas && isset($armadaColumns['nopol'])) {
            $select[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
        } elseif ($hasArmadaNopolColumn) {
            $select[] = 'c.armada_nopol';
        } elseif ($canJoinArmadas && isset($armadaColumns['nopol'])) {
            $select[] = DB::raw('a.nopol as armada_nopol');
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $query->select($select);

        if ($from !== '' && $to !== '' && $hasStartDateColumn) {
            $query->whereBetween('c.start_date', [$from, $to]);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike, $charterColumns, $unitColumns, $armadaColumns, $hasArmadaNopolColumn, $canJoinArmadas) {
                $hasClause = false;

                foreach (['name', 'phone', 'driver_name', 'pickup_point', 'drop_point'] as $column) {
                    if (! isset($charterColumns[$column])) {
                        continue;
                    }

                    $hasClause
                        ? $builder->orWhere("c.{$column}", 'like', $qLike)
                        : $builder->where("c.{$column}", 'like', $qLike);
                    $hasClause = true;
                }

                foreach (['nopol', 'category'] as $column) {
                    if (! isset($unitColumns[$column])) {
                        continue;
                    }

                    $hasClause
                        ? $builder->orWhere("u.{$column}", 'like', $qLike)
                        : $builder->where("u.{$column}", 'like', $qLike);
                    $hasClause = true;
                }

                if ($hasArmadaNopolColumn) {
                    $hasClause
                        ? $builder->orWhere('c.armada_nopol', 'like', $qLike)
                        : $builder->where('c.armada_nopol', 'like', $qLike);
                    $hasClause = true;
                }

                if ($canJoinArmadas) {
                    foreach (['nopol', 'kategori', 'merk'] as $column) {
                        if (! isset($armadaColumns[$column])) {
                            continue;
                        }

                        $hasClause
                            ? $builder->orWhere("a.{$column}", 'like', $qLike)
                            : $builder->where("a.{$column}", 'like', $qLike);
                        $hasClause = true;
                    }
                }

                if (! $hasClause) {
                    $builder->whereRaw('1 = 0');
                }
            });
        }
        if ($paymentStatus !== '' && $hasPaymentStatusColumn) {
            $query->where('c.payment_status', $paymentStatus);
        }
        if ($bopStatus !== '' && $hasBopStatusColumn) {
            $query->where('c.bop_status', $bopStatus);
        }
        if ($unitId > 0 && $hasUnitIdColumn) {
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
            } elseif ($hasBopStatusColumn) {
                $query->where('c.bop_status', 'done');
            }
        } elseif ($scope === 'active') {
            if ($hasStatusColumn) {
                $query->where('c.status', '!=', 'done');
            } elseif ($hasBopStatusColumn) {
                $query->where(function (Builder $builder) {
                    $builder->whereNull('c.bop_status')->orWhere('c.bop_status', '!=', 'done');
                });
            }
        }
        $this->applyCharterPoolScope($query);
        $this->applyTenantScopeIfExists($query, 'charters', 'c');
        $this->orderChartersByNearestDeparture($query, $scope, $hasStartDateColumn, $hasDepartureTimeColumn);

        $result = $this->paginateQuery($query, $page, $perPage);

        return $this->ok([
            'charters' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    private function orderChartersByNearestDeparture(Builder $query, string $scope, bool $hasStartDateColumn = true, bool $hasDepartureTimeColumn = true): void
    {
        if (! $hasStartDateColumn) {
            $query->orderByDesc('c.id');

            return;
        }

        $departureTimeOrdering = $hasDepartureTimeColumn
            ? 'c.departure_time'
            : 'c.id';
        $departureTimeNullOrdering = $hasDepartureTimeColumn
            ? 'c.departure_time IS NULL'
            : '0';

        if ($scope === 'history') {
            $query
                ->orderByDesc('c.start_date')
                ->orderByRaw($departureTimeNullOrdering)
                ->orderByDesc($departureTimeOrdering)
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
                ->orderByRaw($departureTimeNullOrdering)
                ->orderBy($departureTimeOrdering)
                ->orderBy('c.id');

            return;
        }

        $query
            ->orderByRaw('CASE WHEN c.start_date >= ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN c.start_date >= ? THEN c.start_date ELSE NULL END ASC', [$today])
            ->orderByRaw('CASE WHEN c.start_date < ? THEN c.start_date ELSE NULL END DESC', [$today])
            ->orderByRaw($departureTimeNullOrdering)
            ->orderBy($departureTimeOrdering)
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
        if (! Schema::hasTable('luggages')) {
            return $this->ok([
                'luggages' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $status = trim((string) $request->query('status', ''));
        $paymentStatus = trim((string) $request->query('payment_status', ''));
        $q = trim((string) $request->query('q', ''));
        $luggageColumns = array_flip(Schema::getColumnListing('luggages'));
        if (! isset($luggageColumns['id'])) {
            return $this->ok([
                'luggages' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

        $serviceForeignKey = isset($luggageColumns['service_id'])
            ? 'service_id'
            : (isset($luggageColumns['layanan_id']) ? 'layanan_id' : null);
        $hasLuggageServicesTable = Schema::hasTable('luggage_services');
        $serviceColumns = $hasLuggageServicesTable ? array_flip(Schema::getColumnListing('luggage_services')) : [];
        $hasRoutesTable = Schema::hasTable('routes');
        $routeColumns = $hasRoutesTable ? array_flip(Schema::getColumnListing('routes')) : [];
        $hasRouteIdColumn = isset($luggageColumns['rute_id']);
        $hasRouteLabelColumn = isset($luggageColumns['rute']);
        $hasCreatedAtColumn = isset($luggageColumns['created_at']);
        $hasStatusColumn = isset($luggageColumns['status']);
        $hasPaymentStatusColumn = isset($luggageColumns['payment_status']);
        $hasTripAssignmentLink = isset($luggageColumns['trip_assignment_id']) && Schema::hasTable('trip_assignments');
        $tripAssignmentColumns = $hasTripAssignmentLink ? array_flip(Schema::getColumnListing('trip_assignments')) : [];
        $canJoinDrivers = $hasTripAssignmentLink
            && isset($tripAssignmentColumns['driver_id'])
            && Schema::hasTable('drivers');
        $driverColumns = $canJoinDrivers ? array_flip(Schema::getColumnListing('drivers')) : [];
        $canJoinArmadas = $hasTripAssignmentLink && $this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas');
        $armadaColumns = $canJoinArmadas ? array_flip(Schema::getColumnListing('armadas')) : [];
        $hasTripAssignmentArmadaNopol = $hasTripAssignmentLink && $this->tripAssignmentsHasArmadaNopol();
        $selectColumn = static fn (array $columns, string $column, string $alias, string $prefix = 'l') => isset($columns[$column])
            ? "{$prefix}.{$column}"
            : DB::raw("NULL as {$alias}");

        $query = DB::table('luggages as l');

        if ($hasLuggageServicesTable && $serviceForeignKey !== null) {
            $query->leftJoin('luggage_services as s', 'l.'.$serviceForeignKey, '=', 's.id');
        }

        if ($hasRoutesTable && $hasRouteIdColumn) {
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
                $selectColumn($luggageColumns, 'sender_name', 'sender_name'),
                $selectColumn($luggageColumns, 'sender_phone', 'sender_phone'),
                $selectColumn($luggageColumns, 'sender_address', 'sender_address'),
                $selectColumn($luggageColumns, 'receiver_name', 'receiver_name'),
                $selectColumn($luggageColumns, 'receiver_phone', 'receiver_phone'),
                $selectColumn($luggageColumns, 'receiver_address', 'receiver_address'),
                $serviceForeignKey !== null ? DB::raw('l.'.$serviceForeignKey.' as service_id') : DB::raw('NULL as service_id'),
                $hasRouteIdColumn ? 'l.rute_id' : DB::raw('NULL as rute_id'),
                $selectColumn($luggageColumns, 'rute', 'rute'),
                $selectColumn($luggageColumns, 'tanggal', 'tanggal'),
                $selectColumn($luggageColumns, 'unit_id', 'unit_id'),
                $hasTripAssignmentLink ? 'l.trip_assignment_id' : DB::raw('NULL as trip_assignment_id'),
                $selectColumn($luggageColumns, 'pengirim_id', 'pengirim_id'),
                $selectColumn($luggageColumns, 'penerima_id', 'penerima_id'),
                $selectColumn($luggageColumns, 'quantity', 'quantity'),
                $selectColumn($luggageColumns, 'notes', 'notes'),
                $selectColumn($luggageColumns, 'price', 'price'),
                $selectColumn($luggageColumns, 'status', 'status'),
                $selectColumn($luggageColumns, 'payment_status', 'payment_status'),
                $selectColumn($luggageColumns, 'kode_resi', 'kode_resi'),
                $selectColumn($luggageColumns, 'created_at', 'created_at'),
                $hasLuggageServicesTable && $serviceForeignKey !== null && isset($serviceColumns['name']) ? DB::raw('s.name as service_name') : DB::raw('NULL as service_name'),
                $hasRoutesTable && $hasRouteIdColumn && isset($routeColumns['name']) ? DB::raw('r.name as route_name') : DB::raw('NULL as route_name'),
                $hasTripAssignmentLink && isset($tripAssignmentColumns['tanggal']) ? DB::raw('t.tanggal as departure_date') : DB::raw('NULL as departure_date'),
                $hasTripAssignmentLink && isset($tripAssignmentColumns['jam']) ? DB::raw('t.jam as departure_time') : DB::raw('NULL as departure_time'),
                $hasTripAssignmentLink && isset($tripAssignmentColumns['unit']) ? DB::raw('t.unit as departure_unit') : DB::raw('NULL as departure_unit'),
                $canJoinDrivers && isset($driverColumns['nama']) ? DB::raw('d.nama as departure_driver_name') : DB::raw('NULL as departure_driver_name'),
            ])
            ->orderByDesc('l.id');

        if ($hasTripAssignmentArmadaNopol && isset($tripAssignmentColumns['armada_nopol']) && $canJoinArmadas && isset($armadaColumns['nopol'])) {
            $query->addSelect(DB::raw('COALESCE(t.armada_nopol, a.nopol) as departure_armada_nopol'));
        } elseif ($hasTripAssignmentArmadaNopol && isset($tripAssignmentColumns['armada_nopol'])) {
            $query->addSelect(DB::raw('t.armada_nopol as departure_armada_nopol'));
        } elseif ($canJoinArmadas && isset($armadaColumns['nopol'])) {
            $query->addSelect(DB::raw('a.nopol as departure_armada_nopol'));
        } else {
            $query->addSelect(DB::raw('NULL as departure_armada_nopol'));
        }

        if ($from !== '' && $to !== '' && $hasCreatedAtColumn) {
            [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);
            $query->whereBetween('l.created_at', [$createdFrom, $createdTo]);
        }
        if ($status !== '' && $hasStatusColumn) {
            $this->applyLuggageStatusFilter($query, 'l.status', $this->luggageStatusAliases($status));
        }
        if ($paymentStatus !== '' && $hasPaymentStatusColumn) {
            $query->where('l.payment_status', $paymentStatus);
        }
        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike, $luggageColumns, $hasRoutesTable, $hasRouteIdColumn, $routeColumns, $hasLuggageServicesTable, $serviceForeignKey, $serviceColumns, $canJoinDrivers, $driverColumns, $hasTripAssignmentArmadaNopol, $hasTripAssignmentLink, $tripAssignmentColumns, $canJoinArmadas, $armadaColumns) {
                $hasClause = false;

                foreach (['sender_name', 'sender_phone', 'receiver_name', 'receiver_phone', 'kode_resi', 'notes', 'rute', 'tanggal'] as $column) {
                    if (! isset($luggageColumns[$column])) {
                        continue;
                    }

                    $hasClause
                        ? $builder->orWhere("l.{$column}", 'like', $qLike)
                        : $builder->where("l.{$column}", 'like', $qLike);
                    $hasClause = true;
                }

                if ($hasLuggageServicesTable && $serviceForeignKey !== null && isset($serviceColumns['name'])) {
                    $hasClause
                        ? $builder->orWhere('s.name', 'like', $qLike)
                        : $builder->where('s.name', 'like', $qLike);
                    $hasClause = true;
                }

                if ($hasRoutesTable && $hasRouteIdColumn && isset($routeColumns['name'])) {
                    $hasClause
                        ? $builder->orWhere('r.name', 'like', $qLike)
                        : $builder->where('r.name', 'like', $qLike);
                    $hasClause = true;
                }

                if ($canJoinDrivers && isset($driverColumns['nama'])) {
                    $hasClause
                        ? $builder->orWhere('d.nama', 'like', $qLike)
                        : $builder->where('d.nama', 'like', $qLike);
                    $hasClause = true;
                }

                if ($hasTripAssignmentArmadaNopol && $hasTripAssignmentLink && isset($tripAssignmentColumns['armada_nopol'])) {
                    $hasClause
                        ? $builder->orWhere('t.armada_nopol', 'like', $qLike)
                        : $builder->where('t.armada_nopol', 'like', $qLike);
                    $hasClause = true;
                }

                if ($canJoinArmadas && isset($armadaColumns['nopol'])) {
                    $hasClause
                        ? $builder->orWhere('a.nopol', 'like', $qLike)
                        : $builder->where('a.nopol', 'like', $qLike);
                    $hasClause = true;
                }

                if (! $hasClause) {
                    $builder->whereRaw('1 = 0');
                }
            });
        }
        $this->applyPoolOrRouteScopeToQuery(
            $query,
            $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
            $hasRouteIdColumn ? 'l.rute_id' : '',
            $hasRouteLabelColumn ? 'l.rute' : '',
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

        if (ManifestLifecycle::isAutoCloseDue($payload['tanggal'], substr((string) $payload['jam'], 0, 5))) {
            return $this->error('Manifest sudah ditutup. Data assignment tidak bisa diubah lagi.', 409);
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

        $currentAssignment = null;
        if ($id > 0) {
            $currentAssignment = DB::table('trip_assignments')->where('id', $id)->first(['id', 'tanggal', 'jam', 'status']);
        } else {
            $existingAssignmentId = DB::table('trip_assignments')
                ->where('rute', $payload['rute'])
                ->where('tanggal', $payload['tanggal'])
                ->where('jam', $payload['jam'])
                ->where('unit', $payload['unit'])
                ->value('id');

            if ($existingAssignmentId) {
                $currentAssignment = DB::table('trip_assignments')->where('id', (int) $existingAssignmentId)->first(['id', 'tanggal', 'jam', 'status']);
            }
        }

        if ($this->tripAssignmentIsLocked($currentAssignment)) {
            return $this->error('Manifest sudah ditutup. Data assignment tidak bisa diubah lagi.', 409);
        }

        if ($id > 0) {
            DB::table('trip_assignments')->where('id', $id)->update(array_merge($payload, [
                'updated_at' => now(),
            ]));

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
            DB::table('trip_assignments')->where('id', (int) $existingId)->update(array_merge($updatePayload, [
                'updated_at' => now(),
            ]));

            return $this->ok([
                'message' => 'Assignment updated by trip.',
                'id' => (int) $existingId,
                'armada_id' => $payload['armada_id'] ?? null,
                'armada_nopol' => $payload['armada_nopol'] ?? null,
            ]);
        }

        $newId = DB::table('trip_assignments')->insertGetId(array_merge($payload, $this->tenantPayload('trip_assignments'), [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return $this->ok([
            'message' => 'Assignment created.',
            'id' => $newId,
            'armada_id' => $payload['armada_id'] ?? null,
            'armada_nopol' => $payload['armada_nopol'] ?? null,
        ], 201);
    }

    public function assignmentsDelete(int $id): JsonResponse
    {
        $assignment = DB::table('trip_assignments')->where('id', $id)->first(['id', 'tanggal', 'jam', 'status']);
        if ($this->tripAssignmentIsLocked($assignment)) {
            return $this->error('Manifest sudah ditutup. Assignment tidak bisa dihapus.', 409);
        }

        DB::table('trip_assignments')->where('id', $id)->delete();

        return $this->ok(['message' => 'Assignment deleted.']);
    }

    public function assignmentsBulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $locked = DB::table('trip_assignments')
            ->whereIn('id', $data['ids'])
            ->get(['id', 'tanggal', 'jam', 'status'])
            ->first(function ($assignment): bool {
                return $this->tripAssignmentIsLocked($assignment);
            });
        if ($locked) {
            return $this->error('Manifest sudah ditutup. Ada assignment yang tidak bisa dihapus.', 409);
        }

        $deleted = DB::table('trip_assignments')->whereIn('id', $data['ids'])->delete();

        return $this->ok(['message' => 'Bulk delete assignments done.', 'deleted' => $deleted]);
    }

    public function customerBagasiIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        if (! Schema::hasTable('customer_bagasi')) {
            return $this->ok([
                'customers' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

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

        if (! Schema::hasTable('customer_charter')) {
            return $this->ok([
                'customers' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

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

        if (! Schema::hasTable('master_carter')) {
            return $this->ok([
                'routes' => [],
                'pagination' => $this->paginationMeta(0, $page, $perPage),
            ]);
        }

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

            $poolId = $this->resolveWritablePoolIdFromRequest($request, 'master_carter', 0, true);
            if ($poolId < 0) {
                return $this->error($this->poolResolveErrorMessage($poolId), $poolId === -1 ? 403 : 422);
            }

            $newId = DB::table('master_carter')->insertGetId(array_merge($payload, $this->tenantPayload('master_carter'), $this->poolPayload('master_carter', $poolId > 0 ? $poolId : null), ['created_at' => now()]));

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

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'kategori' => ['nullable', 'string', 'max:120'],
            'ac_type' => ['nullable', 'string', 'max:20'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $kategori = trim((string) ($validated['kategori'] ?? ''));
        $acType = trim((string) ($validated['ac_type'] ?? ''));
        $poolId = (int) ($validated['pool_id'] ?? 0);
        [$monthStart, $monthEnd] = $this->armadaPeriodBounds($validated['period'] ?? null);

        $query = DB::table('armadas')
            ->when(Schema::hasColumn('armadas', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'armadas.tenant_id');
            })
            ->select($this->armadaSelectColumns())
            ->orderBy('nopol');
        $this->applyPoolScopeIfExists($query, 'armadas', '', $poolId > 0 ? $poolId : 0);

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

        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);
        $driverNames = $this->armadaDriverNameMap($poolId > 0 ? $poolId : 0);

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
            ->map(function ($row) use ($financials, $poolNames, $driverNames) {
                $row = $this->withArmadaFinancials($row, $financials);
                $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
                $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;
                $row->driver_name = $driverNames['by_id'][(int) ($row->id ?? 0)] ?? $driverNames['by_nopol'][$this->normalizeNopol((string) ($row->nopol ?? ''))] ?? null;

                return $row;
            })
            ->values();

        return $this->ok([
            'armadas' => $rows,
            ...($pagination !== null ? ['pagination' => $pagination] : []),
        ]);
    }

    public function armadasExport(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('armadas')) {
            return response()->streamDownload(static function (): void {}, 'armadas.csv', ['Content-Type' => 'text/csv']);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'kategori' => ['nullable', 'string', 'max:120'],
            'ac_type' => ['nullable', 'string', 'max:20'],
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $kategori = trim((string) ($validated['kategori'] ?? ''));
        $acType = trim((string) ($validated['ac_type'] ?? ''));
        $poolId = (int) ($validated['pool_id'] ?? 0);
        [$monthStart, $monthEnd, $period] = $this->armadaPeriodBounds($validated['period'] ?? null);
        $filename = 'armadas-'.str_replace('-', '', $period).'.csv';

        $query = DB::table('armadas')
            ->when(Schema::hasColumn('armadas', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'armadas.tenant_id');
            })
            ->select($this->armadaSelectColumns())
            ->orderBy('nopol');
        $this->applyPoolScopeIfExists($query, 'armadas', '', $poolId > 0 ? $poolId : 0);

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

        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);
        $driverNames = $this->armadaDriverNameMap($poolId > 0 ? $poolId : 0);

        $rows = $query->get();
        $poolNames = $this->poolNameMap($rows->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $rows = $rows->map(function ($row) use ($financials, $poolNames, $driverNames) {
            $row = $this->withArmadaFinancials($row, $financials);
            $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
            $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;
            $row->driver_name = $driverNames['by_id'][(int) ($row->id ?? 0)] ?? $driverNames['by_nopol'][$this->normalizeNopol((string) ($row->nopol ?? ''))] ?? null;

            return $row;
        });

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'nopol',
                'driver_name',
                'pool_name',
                'kategori',
                'ac_type',
                'gps_status',
                'revenue',
                'bop',
                'gross_margin',
                'net_margin',
                'target_bulanan',
                'achievement_pct',
                'platform_gps',
                'api_gps',
                'warna',
                'tahun',
            ]);
            foreach ($rows as $row) {
                $revenue = (float) ($row->revenue ?? 0);
                $bop = (float) ($row->bop ?? 0);
                $fixedCost = (float) ($row->fixed_cost ?? 0);
                $gross = $revenue - $bop;
                $net = $gross - $fixedCost;
                $target = (float) ($row->target_bulanan ?? 0);
                $achievement = $target > 0 ? ($revenue / $target) * 100 : 0;
                $gpsStatus = trim((string) ($row->platform_gps ?? '')) !== '' || trim((string) ($row->api_gps ?? '')) !== '' ? 'Aktif' : 'Offline';

                fputcsv($out, [
                    $row->nopol ?? '',
                    $row->driver_name ?? '',
                    $row->pool_name ?? '',
                    $row->kategori ?? '',
                    $row->ac_type ?? '',
                    $gpsStatus,
                    $revenue,
                    $bop,
                    $gross,
                    $net,
                    $target,
                    round($achievement, 1),
                    $row->platform_gps ?? '',
                    $row->api_gps ?? '',
                    $row->warna ?? '',
                    $row->tahun ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function armadaPeriodBounds(?string $period): array
    {
        $normalizedPeriod = trim((string) $period);
        if ($normalizedPeriod === '') {
            $normalizedPeriod = now()->format('Y-m');
        }

        try {
            $month = Carbon::createFromFormat('Y-m', $normalizedPeriod)->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
            $normalizedPeriod = $month->format('Y-m');
        }

        return [
            $month->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
            $normalizedPeriod,
        ];
    }

    /**
     * @return array{by_id: array<int, string>, by_nopol: array<string, string>}
     */
    private function armadaDriverNameMap(int $poolId = 0): array
    {
        $result = [
            'by_id' => [],
            'by_nopol' => [],
        ];

        if (! Schema::hasTable('drivers')) {
            return $result;
        }

        $select = ['id', 'nama'];
        if (Schema::hasColumn('drivers', 'armada_id')) {
            $select[] = 'armada_id';
        } else {
            $select[] = DB::raw('NULL as armada_id');
        }
        if (Schema::hasColumn('drivers', 'armada_nopol')) {
            $select[] = 'armada_nopol';
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $query = DB::table('drivers')
            ->select($select)
            ->when(Schema::hasColumn('drivers', 'tenant_id'), function (Builder $q): void {
                PoolScope::applyTenantScope($q, 'drivers.tenant_id');
            });
        $this->applyPoolScopeIfExists($query, 'drivers', '', $poolId > 0 ? $poolId : 0);

        foreach ($query->orderBy('nama')->get() as $row) {
            $name = trim((string) ($row->nama ?? ''));
            if ($name === '') {
                continue;
            }

            $driverId = (int) ($row->id ?? 0);
            if ($driverId > 0 && ! isset($result['by_id'][$driverId])) {
                $result['by_id'][$driverId] = $name;
            }

            $nopolKey = $this->normalizeNopol((string) ($row->armada_nopol ?? ''));
            if ($nopolKey !== '' && ! isset($result['by_nopol'][$nopolKey])) {
                $result['by_nopol'][$nopolKey] = $name;
            }
        }

        return $result;
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

    public function armadasShow(Request $request, int $id): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->error('Data armada tidak tersedia.', 404);
        }

        $validated = $request->validate([
            'pool_id' => ['nullable', 'integer', 'min:0'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);

        $poolId = (int) ($validated['pool_id'] ?? 0);
        [$monthStart, $monthEnd] = $this->armadaPeriodBounds($validated['period'] ?? null);

        $query = DB::table('armadas')->where('id', $id);
        if (Schema::hasColumn('armadas', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        if ($poolId > 0) {
            $this->applyPoolScopeIfExists($query, 'armadas', '', $poolId);
        }

        $row = $query->first($this->armadaSelectColumns());

        if (! $row) {
            return $this->error('Armada tidak ditemukan.', 404);
        }

        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);
        $driverNames = $this->armadaDriverNameMap($poolId > 0 ? $poolId : 0);

        $row = $this->withArmadaFinancials($row, $financials);
        $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
        $poolNames = $this->poolNameMap([$row->pool_id ?? 0]);
        $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;
        $row->driver_name = $driverNames['by_id'][(int) ($row->id ?? 0)] ?? $driverNames['by_nopol'][$this->normalizeNopol((string) ($row->nopol ?? ''))] ?? null;
        $row->monthly = $this->armadaMonthlyDetailForPeriod($row, $monthStart, $monthEnd, $poolId);

        return $this->ok(['armada' => $row]);
    }

    public function poolSwitch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pool_id' => ['required', 'integer', 'min:0'],
        ]);

        $poolId = (int) ($data['pool_id'] ?? 0);
        $userId = (int) ($request->user()?->id ?? 0);

        if ($poolId > 0 && $this->poolTablesReady()) {
            $tenantId = PoolScope::tenantId($userId);
            if ($tenantId <= 0) {
                return $this->error('Pilih tenant dulu.', 422);
            }

            $poolQuery = DB::table('pools')
                ->where('id', $poolId)
                ->where('status', 'active');

            if (Schema::hasColumn('pools', 'tenant_id')) {
                $poolQuery->where('tenant_id', $tenantId);
            }

            $pool = $poolQuery->first(['id', 'name']);

            if (! $pool) {
                return $this->error('Pool tidak ditemukan atau tidak aktif.', 422);
            }

            $scope = PoolScope::forCurrentUser(0, $userId, false);
            if (! ($scope['all'] ?? true) && ! in_array($poolId, $scope['pool_ids'] ?? [], true)) {
                return $this->error('Anda tidak memiliki akses ke pool ini.', 403);
            }
        }

        session(['active_pool_id' => $poolId]);
        PoolScope::flushRequestCache();
        $poolName = $poolId > 0
            ? (string) (DB::table('pools')->where('id', $poolId)->value('name') ?? 'Pool')
            : 'Semua Pool';

        return $this->ok([
            'message' => $poolId > 0 ? "Pool aktif: {$poolName}" : 'Menampilkan semua pool.',
            'pool_id' => $poolId,
            'pool_name' => $poolName,
        ]);
    }

    public function tenantSwitch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:0'],
        ]);

        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $userId = (int) ($request->user()?->id ?? 0);
        $previousTenantId = (int) session('active_tenant_id', 0);
        $previousPoolId = (int) session('active_pool_id', 0);

        if ($tenantId > 0) {
            if (! Schema::hasTable('tenants')) {
                return $this->error('Tabel tenants belum tersedia.', 409);
            }

            $tenant = DB::table('tenants')
                ->where('id', $tenantId)
                ->where('status', '!=', 'canceled')
                ->first(['id', 'name', 'slug', 'status']);

            if (! $tenant) {
                return $this->error('Tenant tidak ditemukan atau sudah tidak aktif.', 422);
            }
        }

        if ($tenantId > 0) {
            session(['active_tenant_id' => $tenantId]);
        } else {
            session()->forget('active_tenant_id');
        }

        session()->forget('active_pool_id');
        PoolScope::flushRequestCache();

        $tenantName = $tenantId > 0
            ? (string) (DB::table('tenants')->where('id', $tenantId)->value('name') ?? 'Tenant')
            : 'Semua Tenant';

        ActivityLog::write(
            'SWITCH',
            'Tenant switched',
            $previousTenantId > 0 || $tenantId > 0
                ? "Tenant context changed to {$tenantName}"
                : 'Tenant context cleared',
            (string) ($request->user()?->email ?? ''),
            [
                'user_id' => $userId,
                'previous_tenant_id' => $previousTenantId,
                'new_tenant_id' => $tenantId,
                'previous_pool_id' => $previousPoolId,
                'new_pool_id' => 0,
                'tenant_id' => $tenantId > 0 ? $tenantId : $previousTenantId,
                'action' => 'tenant_switch',
            ],
        );

        return $this->ok([
            'message' => $tenantId > 0 ? "Tenant aktif: {$tenantName}" : 'Menampilkan semua tenant.',
            'tenant_id' => $tenantId,
            'tenant_name' => $tenantName,
        ]);
    }

    public function poolsIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['desc', 'asc'])],
            'performance' => ['nullable', Rule::in(['all', 'tercapai', 'kurang'])],
        ]);

        return $this->ok($this->poolListingPayload(
            trim((string) ($validated['q'] ?? '')),
            trim((string) ($validated['region'] ?? '')),
            (string) ($validated['sort'] ?? 'desc'),
            (string) ($validated['performance'] ?? 'all'),
        ));
    }

    public function poolOptionsIndex(Request $request): JsonResponse
    {
        return $this->ok($this->poolOptionsPayload());
    }

    public function poolsExport(Request $request): StreamedResponse
    {
        if ($response = $this->requirePermission('report.export')) {
            abort($response);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['desc', 'asc'])],
            'performance' => ['nullable', Rule::in(['all', 'tercapai', 'kurang'])],
        ]);

        $payload = $this->poolListingPayload(
            trim((string) ($validated['q'] ?? '')),
            trim((string) ($validated['region'] ?? '')),
            (string) ($validated['sort'] ?? 'desc'),
            (string) ($validated['performance'] ?? 'all'),
        );

        $period = now()->format('Y-m');
        $xlsx = $this->buildPoolsXlsx(array_values($payload['pools'] ?? []), $period);

        return response()->streamDownload(function () use ($xlsx): void {
            echo $xlsx;
        }, 'performa-cabang-'.$period.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{pools: array<int, array<string, mixed>>, routes: array<int, mixed>, can_manage: bool}
     */
    private function poolListingPayload(string $q = '', string $region = '', string $sort = 'desc', string $performance = 'all'): array
    {
        if (! $this->poolTablesReady()) {
            $routes = [];
            if (Schema::hasTable('routes')) {
                $q = DB::table('routes')->orderBy('name');
                if (Schema::hasColumn('routes', 'tenant_id')) {
                    PoolScope::applyTenantScope($q, 'routes.tenant_id');
                }
                $routes = $q->get(['id', 'name', 'origin', 'destination'])->all();
            }

            return [
                'pools' => [],
                'routes' => $routes,
                'regions' => [],
                'can_manage' => true,
            ];
        }

        $canManage = $this->currentUserIsSuperAdmin();
        $allowedPoolIds = $canManage ? [] : $this->currentUserPoolIds();

        $poolQuery = DB::table('pools')
            ->when(Schema::hasColumn('pools', 'tenant_id'), function (Builder $query): void {
                PoolScope::applyTenantScope($query, 'pools.tenant_id');
            })
            ->select([
                'id',
                'name',
                'code',
                'target_revenue',
                $this->hasPoolsFixedCostColumn() ? 'fixed_cost' : DB::raw('0 as fixed_cost'),
                'status',
                'notes',
                Schema::hasColumn('pools', 'phone') ? 'phone' : DB::raw('NULL as phone'),
                Schema::hasColumn('pools', 'address') ? 'address' : DB::raw('NULL as address'),
                'created_at',
            ])
            ->orderBy('name');

        if (! $canManage) {
            if ($allowedPoolIds === []) {
                return [
                    'pools' => [],
                    'routes' => [],
                    'regions' => [],
                    'can_manage' => false,
                ];
            }

            $poolQuery->whereIn('id', $allowedPoolIds)->where('status', 'active');
        }

        $pools = $poolQuery->get();
        $poolIds = $pools->pluck('id')->map(static fn ($id): int => (int) $id)->values()->all();
        $routesByPool = [];

        if ($poolIds !== [] && Schema::hasTable('pool_route') && Schema::hasTable('routes')) {
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
                $routesByPool[$poolId]['names'][] = $routeName !== ''
                    ? $routeName
                    : trim($origin !== '' && $destination !== '' ? $origin.' - '.$destination : '');
            }
        }

        $armadaCounts = $this->poolArmadaCounts();
        $driverCounts = $this->poolDriverCounts();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $routeFinancials = $this->routeFinancialsForMonth($monthStart, $monthEnd);
        $monthlyTargetsByPool = $this->poolMonthlyTargetsByPoolIds($poolIds);
        $currentMonth = now()->startOfMonth()->toDateString();

        $mappedRows = $pools
            ->map(function ($pool) use ($routesByPool, $routeFinancials, $armadaCounts, $driverCounts, $monthlyTargetsByPool, $currentMonth): array {
                $poolId = (int) ($pool->id ?? 0);
                $routes = $routesByPool[$poolId] ?? ['ids' => [], 'names' => []];
                $financials = $this->sumRouteFinancials($routes['ids'], $routeFinancials);
                $revenue = (float) ($financials['revenue'] ?? 0);
                $bop = (float) ($financials['bop'] ?? 0);
                $fixedCost = (float) ($pool->fixed_cost ?? 0);
                $gross = $revenue - $bop;
                $net = $gross - $fixedCost;
                $legacyTarget = (float) ($pool->target_revenue ?? 0);
                $monthlyTargets = $monthlyTargetsByPool[$poolId] ?? [];
                $currentMonthlyTarget = null;
                foreach ($monthlyTargets as $monthlyTarget) {
                    if ((string) ($monthlyTarget['target_month'] ?? '') === $currentMonth) {
                        $currentMonthlyTarget = $monthlyTarget;

                        break;
                    }
                }
                $monthlyTargetTotal = $currentMonthlyTarget ? (float) (
                    ($currentMonthlyTarget['booking_target'] ?? 0)
                    + ($currentMonthlyTarget['bagasi_target'] ?? 0)
                    + ($currentMonthlyTarget['carter_target'] ?? 0)
                ) : null;
                $target = $monthlyTargetTotal ?? $legacyTarget;
                $achievement = $target > 0 ? ($revenue / $target) * 100 : 0;

                return [
                    'id' => $poolId,
                    'name' => (string) ($pool->name ?? ''),
                    'code' => (string) ($pool->code ?? ''),
                    'region' => $this->poolRegionLabel(
                        (string) ($pool->name ?? ''),
                        (string) ($pool->code ?? ''),
                        (string) ($pool->notes ?? ''),
                        collect($routes['names'])->map(static fn ($name): string => trim((string) $name))->all(),
                    ),
                    'phone' => trim((string) ($pool->phone ?? '')),
                    'address' => trim((string) ($pool->address ?? '')),
                    'target_revenue' => $legacyTarget,
                    'monthly_target_month' => $currentMonth,
                    'monthly_target_total' => $monthlyTargetTotal,
                    'monthly_target_booking' => $currentMonthlyTarget ? (float) ($currentMonthlyTarget['booking_target'] ?? 0) : null,
                    'monthly_target_bagasi' => $currentMonthlyTarget ? (float) ($currentMonthlyTarget['bagasi_target'] ?? 0) : null,
                    'monthly_target_carter' => $currentMonthlyTarget ? (float) ($currentMonthlyTarget['carter_target'] ?? 0) : null,
                    'monthly_target_source' => $currentMonthlyTarget ? 'monthly' : ($legacyTarget > 0 ? 'legacy' : 'none'),
                    'monthly_targets' => $monthlyTargets,
                    'fixed_cost' => $fixedCost,
                    'charter_revenue' => (float) ($financials['charter_revenue'] ?? 0),
                    'departure_revenue' => (float) ($financials['departure_revenue'] ?? 0),
                    'luggage_revenue' => (float) ($financials['luggage_revenue'] ?? 0),
                    'revenue' => $revenue,
                    'charter_bop' => (float) ($financials['charter_bop'] ?? 0),
                    'departure_bop' => (float) ($financials['departure_bop'] ?? 0),
                    'bop' => $bop,
                    'gross_margin' => $gross,
                    'net_margin' => $net,
                    'achievement' => $achievement,
                    'armada_ready_count' => (int) ($armadaCounts[$poolId]['ready'] ?? 0),
                    'armada_count' => (int) ($armadaCounts[$poolId]['total'] ?? 0),
                    'driver_count' => (int) ($driverCounts[$poolId] ?? 0),
                    'status' => (string) ($pool->status ?? 'active'),
                    'notes' => (string) ($pool->notes ?? ''),
                    'created_at' => (string) ($pool->created_at ?? ''),
                    'route_ids' => array_values(array_filter($routes['ids'], static fn ($id): bool => (int) $id > 0)),
                    'route_names' => collect($routes['names'])
                        ->map(static fn ($name): string => trim((string) $name))
                        ->filter(static fn ($name): bool => $name !== '')
                        ->unique()
                        ->sortBy(static fn (string $name): string => mb_strtolower($name))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $regionOptions = collect($mappedRows)
            ->pluck('region')
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn ($value): bool => $value !== '')
            ->unique()
            ->sortBy(static fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();

        $rows = collect($mappedRows)
            ->filter(function (array $row) use ($q, $region, $performance): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($row['name'] ?? ''),
                    (string) ($row['code'] ?? ''),
                    (string) ($row['region'] ?? ''),
                    (string) ($row['notes'] ?? ''),
                    (string) ($row['phone'] ?? ''),
                    (string) ($row['address'] ?? ''),
                    implode(' ', array_map(static fn ($item): string => (string) $item, $row['route_names'] ?? [])),
                ]));

                if ($q !== '' && ! str_contains($haystack, mb_strtolower($q))) {
                    return false;
                }

                if ($region !== '' && mb_strtolower((string) ($row['region'] ?? '')) !== mb_strtolower($region)) {
                    return false;
                }

                if ($performance === 'tercapai' && (float) ($row['achievement'] ?? 0) < 100) {
                    return false;
                }

                if ($performance === 'kurang' && (float) ($row['achievement'] ?? 0) >= 100) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();

        usort($rows, function (array $left, array $right) use ($sort): int {
            $leftScore = (float) ($left['achievement'] ?? 0);
            $rightScore = (float) ($right['achievement'] ?? 0);
            $comparison = $leftScore <=> $rightScore;

            if ($sort === 'desc') {
                $comparison *= -1;
            }

            if ($comparison !== 0) {
                return $comparison;
            }

            return mb_strtolower((string) ($left['name'] ?? '')) <=> mb_strtolower((string) ($right['name'] ?? ''));
        });

        $routeQuery = DB::table('routes')->orderBy('name');
        if (Schema::hasColumn('routes', 'tenant_id')) {
            PoolScope::applyTenantScope($routeQuery, 'routes.tenant_id');
        }

        if (! $canManage) {
            $routeIds = [];
            foreach ($rows as $pool) {
                $routeIds = array_merge($routeIds, $pool['route_ids'] ?? []);
            }
            $routeIds = array_values(array_unique(array_map(static fn ($id): int => (int) $id, $routeIds)));

            if ($routeIds === []) {
                $routeQuery->whereRaw('1 = 0');
            } else {
                $routeQuery->whereIn('id', $routeIds);
            }
        }

        return [
            'pools' => $rows,
            'routes' => $routeQuery->get(['id', 'name', 'origin', 'destination'])->all(),
            'regions' => $regionOptions,
            'can_manage' => $canManage,
        ];
    }

    /**
     * Lightweight pool + route lookup payload for deferred form masters.
     *
     * @return array{pools: array<int, array<string, mixed>>, routes: array<int, mixed>, can_manage: bool}
     */
    private function poolOptionsPayload(): array
    {
        $canManage = $this->currentUserIsSuperAdmin();

        if (! $this->poolTablesReady()) {
            $routes = [];
            if (Schema::hasTable('routes')) {
                $query = DB::table('routes')->orderBy('name');
                if (Schema::hasColumn('routes', 'tenant_id')) {
                    PoolScope::applyTenantScope($query, 'routes.tenant_id');
                }
                $routes = $query->get(['id', 'name', 'origin', 'destination'])->all();
            }

            return [
                'pools' => [],
                'routes' => $routes,
                'can_manage' => $canManage,
            ];
        }

        $allowedPoolIds = $canManage ? [] : $this->currentUserPoolIds();
        $poolQuery = DB::table('pools')
            ->when(Schema::hasColumn('pools', 'tenant_id'), function (Builder $query): void {
                PoolScope::applyTenantScope($query, 'pools.tenant_id');
            })
            ->select([
                'id',
                'name',
                Schema::hasColumn('pools', 'code') ? 'code' : DB::raw('NULL as code'),
                Schema::hasColumn('pools', 'target_revenue') ? 'target_revenue' : DB::raw('0 as target_revenue'),
                Schema::hasColumn('pools', 'status') ? 'status' : DB::raw("'active' as status"),
                Schema::hasColumn('pools', 'notes') ? 'notes' : DB::raw('NULL as notes'),
                Schema::hasColumn('pools', 'phone') ? 'phone' : DB::raw('NULL as phone'),
                Schema::hasColumn('pools', 'address') ? 'address' : DB::raw('NULL as address'),
            ])
            ->orderBy('name');

        if (! $canManage) {
            if ($allowedPoolIds === []) {
                return [
                    'pools' => [],
                    'routes' => [],
                    'can_manage' => false,
                ];
            }

            $poolQuery->whereIn('id', $allowedPoolIds)->where('status', 'active');
        }

        $pools = $poolQuery->get();
        $poolIds = $pools->pluck('id')->map(static fn ($id): int => (int) $id)->values()->all();
        $routesByPool = [];

        if ($poolIds !== [] && Schema::hasTable('pool_route') && Schema::hasTable('routes')) {
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
                $routesByPool[$poolId]['names'][] = $routeName !== ''
                    ? $routeName
                    : trim($origin !== '' && $destination !== '' ? $origin.' - '.$destination : '');
            }
        }

        $rows = $pools
            ->map(function ($pool) use ($routesByPool): array {
                $poolId = (int) ($pool->id ?? 0);
                $routes = $routesByPool[$poolId] ?? ['ids' => [], 'names' => []];

                return [
                    'id' => $poolId,
                    'name' => (string) ($pool->name ?? ''),
                    'code' => (string) ($pool->code ?? ''),
                    'target_revenue' => (float) ($pool->target_revenue ?? 0),
                    'status' => (string) ($pool->status ?? 'active'),
                    'notes' => (string) ($pool->notes ?? ''),
                    'phone' => trim((string) ($pool->phone ?? '')),
                    'address' => trim((string) ($pool->address ?? '')),
                    'route_ids' => array_values(array_filter($routes['ids'], static fn ($id): bool => (int) $id > 0)),
                    'route_names' => collect($routes['names'])
                        ->map(static fn ($name): string => trim((string) $name))
                        ->filter(static fn ($name): bool => $name !== '')
                        ->unique()
                        ->sortBy(static fn (string $name): string => mb_strtolower($name))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $routeQuery = DB::table('routes')->orderBy('name');
        if (Schema::hasColumn('routes', 'tenant_id')) {
            PoolScope::applyTenantScope($routeQuery, 'routes.tenant_id');
        }

        if (! $canManage) {
            $routeIds = [];
            foreach ($rows as $pool) {
                $routeIds = array_merge($routeIds, $pool['route_ids'] ?? []);
            }
            $routeIds = array_values(array_unique(array_map(static fn ($id): int => (int) $id, $routeIds)));

            if ($routeIds === []) {
                $routeQuery->whereRaw('1 = 0');
            } else {
                $routeQuery->whereIn('id', $routeIds);
            }
        }

        return [
            'pools' => $rows,
            'routes' => $routeQuery->get(['id', 'name', 'origin', 'destination'])->all(),
            'can_manage' => $canManage,
        ];
    }

    /**
     * @return array<int, array{total: int, ready: int}>
     */
    private function poolArmadaCounts(): array
    {
        if (! Schema::hasTable('armadas') || ! Schema::hasColumn('armadas', 'pool_id')) {
            return [];
        }

        $query = DB::table('armadas')
            ->select('pool_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN COALESCE(revenue, 0) > 0 OR COALESCE(bop, 0) > 0 OR COALESCE(fixed_cost, 0) > 0 THEN 1 ELSE 0 END) as ready')
            ->groupBy('pool_id');

        if (Schema::hasColumn('armadas', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'armadas.tenant_id');
        }

        $counts = [];
        foreach ($query->get() as $row) {
            $poolId = (int) ($row->pool_id ?? 0);
            if ($poolId <= 0) {
                continue;
            }

            $counts[$poolId] = [
                'total' => (int) ($row->total ?? 0),
                'ready' => (int) ($row->ready ?? 0),
            ];
        }

        return $counts;
    }

    /**
     * @return array<int, int>
     */
    private function poolDriverCounts(): array
    {
        if (! Schema::hasTable('drivers') || ! Schema::hasColumn('drivers', 'pool_id')) {
            return [];
        }

        $query = DB::table('drivers')
            ->select('pool_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('pool_id');

        if (Schema::hasColumn('drivers', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'drivers.tenant_id');
        }

        $counts = [];
        foreach ($query->get() as $row) {
            $poolId = (int) ($row->pool_id ?? 0);
            if ($poolId <= 0) {
                continue;
            }

            $counts[$poolId] = (int) ($row->total ?? 0);
        }

        return $counts;
    }

    private function poolRegionLabel(string $name, string $code, string $notes, array $routeNames = []): string
    {
        $sources = array_filter([
            $name,
            $code,
            implode(' ', $routeNames),
            $notes,
        ], static fn (string $value): bool => trim($value) !== '');

        foreach ($sources as $source) {
            $label = $this->deriveRegionLabel($source);

            if ($label !== '') {
                return $label;
            }
        }

        return 'Lainnya';
    }

    private function deriveRegionLabel(string $value): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strtoupper($value)) ?? '');
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/^(POOL|CABANG|AREA|WILAYAH)\s+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^A-Z0-9\s\-\/]+/u', ' ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
        if ($normalized === '') {
            return '';
        }

        $segments = preg_split('/[\s\-\/]+/', $normalized) ?: [];
        $segments = array_values(array_filter($segments, static fn (string $segment): bool => trim($segment) !== ''));
        if ($segments === []) {
            return '';
        }

        $region = implode(' ', array_slice($segments, 0, min(2, count($segments))));

        return mb_convert_case($region, MB_CASE_TITLE, 'UTF-8');
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
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'target_revenue' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
            'monthly_targets' => ['nullable', 'array'],
            'monthly_targets.*.target_month' => ['required_with:monthly_targets', 'date_format:Y-m-d'],
            'monthly_targets.*.booking_target' => ['nullable', 'numeric', 'min:0'],
            'monthly_targets.*.bagasi_target' => ['nullable', 'numeric', 'min:0'],
            'monthly_targets.*.carter_target' => ['nullable', 'numeric', 'min:0'],
            'target_month' => ['nullable', 'date_format:Y-m'],
            'booking_target' => ['nullable', 'numeric', 'min:0'],
            'bagasi_target' => ['nullable', 'numeric', 'min:0'],
            'carter_target' => ['nullable', 'numeric', 'min:0'],
            'save_monthly_target' => ['nullable', 'boolean'],
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
            'phone' => $this->nullable($data['phone'] ?? null),
            'address' => $this->nullable($data['address'] ?? null),
            'target_revenue' => (float) ($data['target_revenue'] ?? 0),
            'status' => (string) ($data['status'] ?? 'active'),
            'notes' => $this->nullable($data['notes'] ?? null),
        ];

        if ($this->hasPoolsFixedCostColumn()) {
            $payload['fixed_cost'] = (float) ($data['fixed_cost'] ?? 0);
        }

        $monthlyTargetsProvided = array_key_exists('monthly_targets', $data) && is_array($data['monthly_targets']);
        $saveMonthlyTarget = $request->boolean('save_monthly_target') || $monthlyTargetsProvided;
        $normalizedMonthlyTargets = $monthlyTargetsProvided
            ? $this->normalizePoolMonthlyTargetRows($data['monthly_targets'])
            : [];
        $normalizedTargetMonth = null;
        if (! $monthlyTargetsProvided && $saveMonthlyTarget) {
            if (! Schema::hasTable('pool_monthly_targets')) {
                return $this->error('Tabel target bulanan belum tersedia. Jalankan migration terlebih dahulu.', 409);
            }

            $normalizedTargetMonth = $this->normalizeTargetMonth((string) ($data['target_month'] ?? ''));
            if ($normalizedTargetMonth === null) {
                return $this->error('Bulan target bulanan wajib diisi.', 422);
            }
        }

        return DB::transaction(function () use ($id, $payload, $routeIds, $data, $saveMonthlyTarget, $normalizedTargetMonth, $normalizedMonthlyTargets, $monthlyTargetsProvided): JsonResponse {
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

            if ($monthlyTargetsProvided) {
                $this->syncPoolMonthlyTargets($poolId, $normalizedMonthlyTargets);
            } elseif ($saveMonthlyTarget) {
                $this->syncPoolMonthlyTargets($poolId, [[
                    'target_month' => $normalizedTargetMonth,
                    'booking_target' => (float) ($data['booking_target'] ?? 0),
                    'bagasi_target' => (float) ($data['bagasi_target'] ?? 0),
                    'carter_target' => (float) ($data['carter_target'] ?? 0),
                ]]);
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
        if ($this->currentUserIsSuperAdmin() && PoolScope::tenantId(auth()->id()) <= 0) {
            return $this->error('Pilih tenant dulu.', 409);
        }

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
            ->when(Schema::hasColumn('users', 'is_super_admin'), function (Builder $q): void {
                $q->where(function (Builder $superAdminFilter): void {
                    $superAdminFilter
                        ->whereNull('is_super_admin')
                        ->orWhere('is_super_admin', false);
                });
            })
            ->select($select)
            ->orderBy('name');

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

        $roles = AccessControl::rolesForSelect();
        $roles = array_values(array_filter($roles, static fn (array $role): bool => (string) ($role['slug'] ?? '') !== 'super-admin'));

        return $this->ok([
            'users' => $users,
            'roles' => $roles,
            'pagination' => $result['meta'],
        ]);
    }

    public function usersSave(Request $request): JsonResponse
    {
        if ($response = $this->requirePermission('user.manage')) {
            return $response;
        }

        if ($this->currentUserIsSuperAdmin() && PoolScope::tenantId(auth()->id()) <= 0) {
            return $this->error('Pilih tenant dulu.', 409);
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

        if ($superAdminRoleId > 0 && $keepsSuperAdminRole) {
            return $this->error('Role Super Admin hanya dikelola dari menu role.', 403);
        }

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $wantsSuperAdmin = (bool) ($data['is_super_admin'] ?? false);
            if ($wantsSuperAdmin) {
                return $this->error('Role Super Admin hanya dikelola dari menu role.', 403);
            }
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
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $query->where(function (Builder $superAdminFilter): void {
                    $superAdminFilter
                        ->whereNull('is_super_admin')
                        ->orWhere('is_super_admin', false);
                });
            }
            if (! $query->exists()) {
                return $this->error('User tidak ditemukan.', 404);
            }
            if (! $this->currentUserIsSuperAdmin() && (bool) ($data['is_super_admin'] ?? false)) {
                return $this->error('Hanya Super Admin yang bisa menjadikan user sebagai Super Admin.', 403);
            }
            $query->update(array_merge($payload, ['updated_at' => now()]));
            $this->syncUserPools($id, $poolIds);
            $this->syncUserRoles($id, $roleIds);

            ActivityLog::write('user.updated', 'User diperbarui', (string) $payload['email'], null, [
                'user_id' => $id,
                'tenant_id' => (int) PoolScope::tenantId(),
                'pool_id' => (int) PoolScope::currentPoolId(auth()->id()),
                'actor_user_id' => (int) (auth()->id() ?? 0),
            ]);

            return $this->ok(['message' => 'User updated.', 'id' => $id]);
        }

        if (! isset($payload['password'])) {
            return $this->error('Password wajib untuk user baru.', 422);
        }

        $newId = DB::table('users')->insertGetId(array_merge($payload, $this->tenantPayload('users'), [
            'email_verified_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $this->syncUserPools((int) $newId, $poolIds);
        $this->syncUserRoles((int) $newId, $roleIds);

        $createdUser = User::query()->whereKey($newId)->first();
        if ($createdUser && ! $createdUser->hasVerifiedEmail()) {
            $createdUser->sendEmailVerificationNotification();
        }

        ActivityLog::write('user.created', 'User dibuat', (string) $payload['email'], null, [
            'user_id' => (int) $newId,
            'tenant_id' => (int) PoolScope::tenantId(),
            'pool_id' => (int) PoolScope::currentPoolId(auth()->id()),
            'actor_user_id' => (int) (auth()->id() ?? 0),
        ]);

        return $this->ok(['message' => 'User created.', 'id' => $newId], 201);
    }

    public function usersDelete(int $id): JsonResponse
    {
        $authUserId = (int) (auth()->id() ?? 0);

        if ($this->currentUserIsSuperAdmin() && PoolScope::tenantId(auth()->id()) <= 0) {
            return $this->error('Pilih tenant dulu.', 409);
        }

        if ($id === $authUserId) {
            return $this->error('Tidak bisa menghapus akun yang sedang login.', 409);
        }

        $query = DB::table('users')->where('id', $id);
        if (Schema::hasColumn('users', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $query->where(function (Builder $superAdminFilter): void {
                $superAdminFilter
                    ->whereNull('is_super_admin')
                    ->orWhere('is_super_admin', false);
            });
        }
        if (! $query->exists()) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $tenantUserQuery = DB::table('users');
        if (Schema::hasColumn('users', 'tenant_id')) {
            PoolScope::applyTenantScope($tenantUserQuery, 'tenant_id');
        }
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $tenantUserQuery->where(function (Builder $superAdminFilter): void {
                $superAdminFilter
                    ->whereNull('is_super_admin')
                    ->orWhere('is_super_admin', false);
            });
        }
        if ((int) $tenantUserQuery->count() <= 1) {
            return $this->error('Minimal harus ada satu user admin di tenant ini.', 409);
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
        $query->delete();

        ActivityLog::write('user.deleted', 'User dihapus', '', null, [
            'user_id' => $id,
            'tenant_id' => (int) PoolScope::tenantId(),
            'pool_id' => (int) PoolScope::currentPoolId(auth()->id()),
            'actor_user_id' => (int) (auth()->id() ?? 0),
        ]);

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

        if ($this->currentUserIsSuperAdmin() && PoolScope::tenantId(auth()->id()) <= 0) {
            return null;
        }

        $query = DB::table('users')->where('id', $id);
        if (Schema::hasColumn('users', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'users.tenant_id');
        }
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $query->where(function (Builder $superAdminFilter): void {
                $superAdminFilter
                    ->whereNull('is_super_admin')
                    ->orWhere('is_super_admin', false);
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

    private function normalizeTargetMonth(?string $month): ?string
    {
        $month = trim((string) $month);
        if ($month === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        } catch (\Throwable) {
            try {
                return Carbon::parse($month)->startOfMonth()->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * @param  array<int, int>  $poolIds
     * @return array<int, array<int, array{target_month: string, booking_target: float, bagasi_target: float, carter_target: float, target_revenue: float}>>
     */
    private function poolMonthlyTargetsByPoolIds(array $poolIds): array
    {
        $poolIds = array_values(array_unique(array_filter(array_map('intval', $poolIds), static fn (int $id): bool => $id > 0)));
        if ($poolIds === [] || ! Schema::hasTable('pool_monthly_targets')) {
            return [];
        }

        $rows = DB::table('pool_monthly_targets')
            ->whereIn('pool_id', $poolIds)
            ->orderByDesc('target_month')
            ->get([
                'pool_id',
                'target_month',
                'booking_target',
                'bagasi_target',
                'carter_target',
            ]);

        $grouped = [];
        foreach ($rows as $row) {
            $poolId = (int) ($row->pool_id ?? 0);
            if ($poolId <= 0) {
                continue;
            }

            $bookingTarget = (float) ($row->booking_target ?? 0);
            $bagasiTarget = (float) ($row->bagasi_target ?? 0);
            $carterTarget = (float) ($row->carter_target ?? 0);
            $grouped[$poolId] ??= [];
            $grouped[$poolId][] = [
                'target_month' => (string) ($row->target_month ?? ''),
                'booking_target' => $bookingTarget,
                'bagasi_target' => $bagasiTarget,
                'carter_target' => $carterTarget,
                'target_revenue' => $bookingTarget + $bagasiTarget + $carterTarget,
            ];
        }

        return $grouped;
    }

    /**
     * @param  array<int, array<string, mixed>>  $monthlyTargets
     * @return array<int, array{target_month: string, booking_target: float, bagasi_target: float, carter_target: float}>
     */
    private function normalizePoolMonthlyTargetRows(array $monthlyTargets): array
    {
        $normalized = [];

        foreach ($monthlyTargets as $monthlyTarget) {
            if (! is_array($monthlyTarget)) {
                continue;
            }

            $targetMonth = $this->normalizeTargetMonth((string) ($monthlyTarget['target_month'] ?? ''));
            if ($targetMonth === null) {
                continue;
            }

            $normalized[] = [
                'target_month' => $targetMonth,
                'booking_target' => (float) ($monthlyTarget['booking_target'] ?? 0),
                'bagasi_target' => (float) ($monthlyTarget['bagasi_target'] ?? 0),
                'carter_target' => (float) ($monthlyTarget['carter_target'] ?? 0),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array{target_month: string, booking_target: float, bagasi_target: float, carter_target: float}>  $monthlyTargets
     */
    private function syncPoolMonthlyTargets(int $poolId, array $monthlyTargets): void
    {
        if ($poolId <= 0 || ! Schema::hasTable('pool_monthly_targets')) {
            return;
        }

        $now = now();
        foreach ($monthlyTargets as $monthlyTarget) {
            $targetMonth = $this->normalizeTargetMonth((string) ($monthlyTarget['target_month'] ?? ''));
            if ($targetMonth === null) {
                continue;
            }

            $bookingTarget = (float) ($monthlyTarget['booking_target'] ?? 0);
            $bagasiTarget = (float) ($monthlyTarget['bagasi_target'] ?? 0);
            $carterTarget = (float) ($monthlyTarget['carter_target'] ?? 0);

            $query = DB::table('pool_monthly_targets')
                ->where('pool_id', $poolId)
                ->where('target_month', $targetMonth);

            if ($bookingTarget <= 0 && $bagasiTarget <= 0 && $carterTarget <= 0) {
                $query->delete();

                continue;
            }

            DB::table('pool_monthly_targets')->upsert([
                [
                    'pool_id' => $poolId,
                    'target_month' => $targetMonth,
                    'booking_target' => $bookingTarget,
                    'bagasi_target' => $bagasiTarget,
                    'carter_target' => $carterTarget,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['pool_id', 'target_month'], ['booking_target', 'bagasi_target', 'carter_target', 'updated_at']);
        }
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
        $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
        $hasPickupPointColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'pickup_point');
        $hasDropPointColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'drop_point');
        $hasLegacyRouteLabels = $labels !== [] && ($hasPickupPointColumn || $hasDropPointColumn);
        if (($hasPoolIdColumn && $poolIds !== []) || $hasLegacyRouteLabels) {
            $query->where(function (Builder $builder) use ($poolIds, $labels): void {
                $hasClause = false;
                $hasPoolIdColumn = $this->chartersHasPoolIdColumn();
                $hasPickupPointColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'pickup_point');
                $hasDropPointColumn = Schema::hasTable('charters') && Schema::hasColumn('charters', 'drop_point');

                if ($hasPoolIdColumn && $poolIds !== []) {
                    $builder->whereIn('c.pool_id', $poolIds);
                    $hasClause = true;
                }

                if ($labels !== [] && ($hasPickupPointColumn || $hasDropPointColumn)) {
                    $applyLegacyLabelScope = static function (Builder $routeBuilder) use ($labels, $hasPickupPointColumn, $hasDropPointColumn): void {
                        if ($hasPickupPointColumn && $hasDropPointColumn) {
                            $routeBuilder
                                ->whereIn('c.pickup_point', $labels)
                                ->orWhereIn('c.drop_point', $labels);

                            return;
                        }

                        if ($hasPickupPointColumn) {
                            $routeBuilder->whereIn('c.pickup_point', $labels);

                            return;
                        }

                        $routeBuilder->whereIn('c.drop_point', $labels);
                    };

                    if ($hasClause && $hasPoolIdColumn) {
                        $builder->orWhere(function (Builder $legacy) use ($applyLegacyLabelScope): void {
                            $legacy
                                ->whereNull('c.pool_id')
                                ->where(fn (Builder $routeBuilder) => $applyLegacyLabelScope($routeBuilder));
                        });
                    } elseif ($hasClause) {
                        $builder->orWhere(fn (Builder $routeBuilder) => $applyLegacyLabelScope($routeBuilder));
                    } else {
                        $applyLegacyLabelScope($builder);
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
    private function driverRowsForMonth(string $monthStart, string $monthEnd, int $poolId = 0): array
    {
        $hasDriverArmadaId = $this->driversHasArmadaId();
        $hasDriverArmadaNopol = $this->driversHasArmadaNopol();
        $hasDriverKategori = $this->driversHasKategoriColumn();
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
        if ($hasDriverKategori && $canJoinArmadas) {
            $select[] = DB::raw('COALESCE(d.kategori, a.kategori) as category');
        } elseif ($hasDriverKategori) {
            $select[] = DB::raw('d.kategori as category');
        } elseif ($canJoinArmadas) {
            $select[] = DB::raw('a.kategori as category');
        } else {
            $select[] = DB::raw('NULL as category');
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
            ->when(Schema::hasColumn('drivers', 'pool_id'), function (Builder $q) use ($poolId): void {
                $this->applyPoolScopeIfExists($q, 'drivers', 'd', $poolId > 0 ? $poolId : null);
            })
            ->orderBy('d.nama')
            ->get($select);
        $poolNames = $this->poolNameMap($rows->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());

        $scheduleBopMap = $this->scheduleBopMap($poolId);
        $bookingRevenueMap = $this->bookingRevenueByTripForDateRange($monthStart, $monthEnd, $poolId);

        $departureRevenueByDriver = [];
        $departureBopByDriver = [];
        $departureCountByDriver = [];
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
            $poolId,
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
            $departureCountByDriver[$driverId] = (int) ($departureCountByDriver[$driverId] ?? 0) + 1;
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
                $poolId,
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
        $this->applyCharterPoolScope($charterQuery, $poolId);
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
            $payload['category'] = $this->normalizeUnitCategory($payload['category'] ?? null);
            $payload['departure_count'] = (int) ($departureCountByDriver[$driverId] ?? 0);
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
     * @return array{0: string, 1: string}|array{0: string, 1: string, 2: string}
     */
    private function driverPeriodBounds(?string $period, bool $includeLabel = false): array
    {
        $normalizedPeriod = trim((string) $period);
        if ($normalizedPeriod === '') {
            $normalizedPeriod = now()->format('Y-m');
        }

        try {
            $month = Carbon::createFromFormat('Y-m', $normalizedPeriod)->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
            $normalizedPeriod = $month->format('Y-m');
        }

        $start = $month->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        return $includeLabel
            ? [$start, $end, $normalizedPeriod]
            : [$start, $end];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function buildDriversXlsx(array $rows, string $period): string
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(response()->json([
                'success' => false,
                'error' => 'Ekspor Excel tidak tersedia di server ini.',
            ], 500));
        }

        $headers = [
            'Nama Driver',
            'Kontak',
            'Kategori Driver',
            'Pool/Wilayah',
            'Keberangkatan Rit',
            'Charter Revenue',
            'Keberangkatan Revenue',
            'Bagasi Revenue',
            'Total Revenue',
            'Charter BOP',
            'Keberangkatan BOP',
            'Total BOP',
            'Gross',
            'Fixed Cost',
            'Net Margin',
            'Target Revenue',
            'Achievement',
        ];

        $tmpPath = tempnam(sys_get_temp_dir(), 'driver-xlsx-');
        if ($tmpPath === false) {
            abort(response()->json([
                'success' => false,
                'error' => 'Gagal menyiapkan file Excel.',
            ], 500));
        }

        $zip = new \ZipArchive;
        $opened = $zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($opened !== true) {
            @unlink($tmpPath);
            abort(response()->json([
                'success' => false,
                'error' => 'Gagal membuat arsip Excel.',
            ], 500));
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->driverXlsxContentTypesXml());
            $zip->addFromString('_rels/.rels', $this->driverXlsxRootRelsXml());
            $zip->addFromString('xl/workbook.xml', $this->driverXlsxWorkbookXml('Driver '.$period));
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->driverXlsxWorkbookRelsXml());
            $zip->addFromString('xl/styles.xml', $this->driverXlsxStylesXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->driverXlsxSheetXml($headers, $rows, 'Driver '.$period));
            $zip->close();

            $binary = file_get_contents($tmpPath);
            if ($binary === false) {
                abort(response()->json([
                    'success' => false,
                    'error' => 'Gagal membaca file Excel.',
                ], 500));
            }

            return $binary;
        } finally {
            if ($zip->status === \ZipArchive::ER_OK) {
                // no-op
            }
            @unlink($tmpPath);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function buildPoolsXlsx(array $rows, string $period): string
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(response()->json([
                'success' => false,
                'error' => 'Ekspor Excel tidak tersedia di server ini.',
            ], 500));
        }

        $headers = [
            'Nama Cabang',
            'Kode',
            'Wilayah/Region',
            'Alamat Lengkap',
            'Kontak Operasional',
            'Armada Ready',
            'Armada Total',
            'Driver',
            'Rute',
            'Charter Revenue',
            'Keberangkatan Revenue',
            'Bagasi Revenue',
            'Total Revenue',
            'Charter BOP',
            'Keberangkatan BOP',
            'Total BOP',
            'Gross',
            'Fixed Cost',
            'Net Margin',
            'Target Revenue',
            'Achievement',
            'Status',
        ];

        $tmpPath = tempnam(sys_get_temp_dir(), 'pool-xlsx-');
        if ($tmpPath === false) {
            abort(response()->json([
                'success' => false,
                'error' => 'Gagal menyiapkan file Excel.',
            ], 500));
        }

        $zip = new \ZipArchive;
        $opened = $zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($opened !== true) {
            @unlink($tmpPath);
            abort(response()->json([
                'success' => false,
                'error' => 'Gagal membuat arsip Excel.',
            ], 500));
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->driverXlsxContentTypesXml());
            $zip->addFromString('_rels/.rels', $this->driverXlsxRootRelsXml());
            $zip->addFromString('xl/workbook.xml', $this->driverXlsxWorkbookXml('Performa Cabang '.$period));
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->driverXlsxWorkbookRelsXml());
            $zip->addFromString('xl/styles.xml', $this->driverXlsxStylesXml());
            $zip->addFromString(
                'xl/worksheets/sheet1.xml',
                $this->driverXlsxSheetXml($headers, $rows, 'Performa Cabang '.$period, [
                    'Nama Cabang',
                    'Kode',
                    'Wilayah/Region',
                    'Alamat Lengkap',
                    'Kontak Operasional',
                    'Status',
                ]),
            );
            $zip->close();

            $binary = file_get_contents($tmpPath);
            if ($binary === false) {
                abort(response()->json([
                    'success' => false,
                    'error' => 'Gagal membaca file Excel.',
                ], 500));
            }

            return $binary;
        } finally {
            @unlink($tmpPath);
        }
    }

    private function driverXlsxContentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    private function driverXlsxRootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function driverXlsxWorkbookXml(string $sheetName): string
    {
        $sheetName = $this->xmlAttribute($this->truncateSheetName($sheetName));

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="{$sheetName}" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private function driverXlsxWorkbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    private function driverXlsxStylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <numFmts count="2">
        <numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0"/>
        <numFmt numFmtId="165" formatCode="0.0"/>
    </numFmts>
    <fonts count="2">
        <font>
            <sz val="11"/>
            <color theme="1"/>
            <name val="Calibri"/>
            <family val="2"/>
        </font>
        <font>
            <sz val="11"/>
            <b/>
            <color theme="1"/>
            <name val="Calibri"/>
            <family val="2"/>
        </font>
    </fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill>
    </fills>
    <borders count="1">
        <border>
            <left/><right/><top/><bottom/><diagonal/>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="5">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
        <xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
        <xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
        <xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
</styleSheet>
XML;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function driverXlsxSheetXml(array $headers, array $rows, string $sheetName, array $textHeaders = []): string
    {
        $sheetName = $this->truncateSheetName($sheetName);
        $rowIndex = 1;
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetViews>
        <sheetView workbookViewId="0"/>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="18"/>
    <sheetData>
XML;

        $xml .= $this->driverXlsxRowXml($rowIndex++, $headers, [], true);
        foreach ($rows as $row) {
            $xml .= $this->driverXlsxRowXml($rowIndex++, $headers, $row, false, $textHeaders);
        }

        $xml .= <<<'XML'
    </sheetData>
</worksheet>
XML;

        return $xml;
    }

    /**
     * @param  array<int, mixed>  $rowValues
     */
    private function driverXlsxRowXml(int $rowNumber, array $headers, array $rowValues, bool $isHeader = false, array $textHeaders = []): string
    {
        $xml = '        <row r="'.$rowNumber.'">';
        $textHeaders = $textHeaders !== []
            ? $textHeaders
            : [
                'Nama Driver',
                'Kontak',
                'Kategori Driver',
                'Pool/Wilayah',
            ];
        foreach (array_values($headers) as $index => $header) {
            $cellRef = $this->xlsxColumnName($index).$rowNumber;
            if ($isHeader) {
                $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="1"><is><t>'.$this->xmlText((string) $header).'</t></is></c>';

                continue;
            }

            $value = $rowValues[$header] ?? '';
            if (! in_array((string) $header, $textHeaders, true) && is_numeric($value)) {
                $style = $this->xlsxNumericStyleForHeader((string) $header, $value);
                $xml .= '<c r="'.$cellRef.'" s="'.$style.'"><v>'.$this->xmlNumber($value).'</v></c>';

                continue;
            }

            $xml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$this->xmlText((string) $value).'</t></is></c>';
        }
        $xml .= '</row>'."\n";

        return $xml;
    }

    private function xlsxNumericStyleForHeader(string $header, mixed $value): int
    {
        $normalized = strtolower($header);
        if (str_contains($normalized, 'ratus') || str_contains($normalized, 'revenue') || str_contains($normalized, 'bop') || str_contains($normalized, 'gross') || str_contains($normalized, 'margin') || str_contains($normalized, 'target')) {
            return 2;
        }

        if (str_contains($normalized, 'achievement')) {
            return 4;
        }

        return is_float($value) || is_float($value) ? 4 : 3;
    }

    private function xmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xmlAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xmlNumber(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');
    }

    private function xlsxColumnName(int $index): string
    {
        $index++;
        $name = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $name = chr(65 + $mod).$name;
            $index = intdiv($index - 1, 26);
        }

        return $name;
    }

    private function truncateSheetName(string $name): string
    {
        return mb_substr(trim($name), 0, 31);
    }

    /**
     * @return array<string, float>
     */
    private function scheduleBopMap(int $poolId = 0): array
    {
        if (! Schema::hasTable('schedules') || ! $this->hasSchedulesBopColumn()) {
            return [];
        }

        $cacheKey = implode(':', [
            'adminops',
            'schedule-bop-map',
            PoolScope::cacheKey($poolId),
            $this->buildTableMutationSignature('schedules'),
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($poolId): array {
            $map = [];
            $rows = DB::table('schedules')
                ->when(Schema::hasColumn('schedules', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'schedules.tenant_id');
                });
            $this->applyRouteScopeToQuery($rows, Schema::hasColumn('schedules', 'route_id') ? 'schedules.route_id' : '', 'schedules.rute', $poolId);
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
    private function bookingRevenueByTripForDateRange(string $monthStart, string $monthEnd, int $poolId = 0): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $cacheKey = implode(':', [
            'adminops',
            'booking-trip-revenue',
            PoolScope::cacheKey($poolId),
            $monthStart,
            $monthEnd,
            $this->buildTableMutationSignature('bookings'),
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($monthStart, $monthEnd, $poolId): array {
            $map = [];
            $rows = DB::table('bookings')
                ->select(['rute', 'tanggal', 'jam', 'unit'])
                ->selectRaw('SUM(CASE WHEN COALESCE(price, 0) - COALESCE(discount, 0) > 0 THEN COALESCE(price, 0) - COALESCE(discount, 0) ELSE 0 END) as net_revenue')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('bookings', 'tenant_id'), function (Builder $q): void {
                    PoolScope::applyTenantScope($q, 'bookings.tenant_id');
                });
            $this->applyRouteScopeToQuery($rows, Schema::hasColumn('bookings', 'route_id') ? 'bookings.route_id' : '', 'bookings.rute', $poolId);
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
     * @return array{
     *     summary: array{
     *         period: string,
     *         period_label: string,
     *         charter_count: int,
     *         departure_count: int,
     *         luggage_count: int,
     *         charter_revenue: float,
     *         departure_revenue: float,
     *         luggage_revenue: float,
     *         total_revenue: float,
     *         charter_bop: float,
     *         departure_bop: float,
     *         total_bop: float,
     *         gross: float,
     *         fixed_cost: float,
     *         net_margin: float,
     *         target_revenue: float,
     *         achievement: float,
     *         status: string
     *     },
     *     bookings: array<int, array<string, mixed>>,
     *     charters: array<int, array<string, mixed>>,
     *     bagasi: array<int, array<string, mixed>>
     * }
     */
    private function armadaMonthlyDetailForPeriod(object $armada, string $monthStart, string $monthEnd, int $poolId = 0): array
    {
        $armadaId = (int) ($armada->id ?? 0);
        $armadaNopol = strtoupper(trim((string) ($armada->nopol ?? '')));
        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);
        $bucket = $financials[$this->normalizeNopol($armadaNopol)] ?? $this->emptyFinancialBucket();

        $charterRevenue = (float) ($bucket['charter_revenue'] ?? 0);
        $departureRevenue = (float) ($bucket['departure_revenue'] ?? 0);
        $luggageRevenue = (float) ($bucket['luggage_revenue'] ?? 0);
        $totalRevenue = (float) ($bucket['revenue'] ?? 0);
        $charterBop = (float) ($bucket['charter_bop'] ?? 0);
        $departureBop = (float) ($bucket['departure_bop'] ?? 0);
        $totalBop = (float) ($bucket['bop'] ?? 0);
        $gross = $totalRevenue - $totalBop;
        $fixedCost = (float) ($armada->fixed_cost ?? 0);
        $netMargin = $gross - $fixedCost;
        $targetRevenue = (float) ($armada->target_bulanan ?? 0);
        $achievement = $targetRevenue > 0 ? ($totalRevenue / $targetRevenue) * 100 : 0.0;

        $bookings = [];
        if (Schema::hasTable('bookings') && Schema::hasTable('trip_assignments')) {
            $bookingQuery = DB::table('bookings as b')
                ->join('trip_assignments as t', function ($join): void {
                    $join->on('b.rute', '=', 't.rute')
                        ->on('b.tanggal', '=', 't.tanggal')
                        ->on('b.jam', '=', 't.jam')
                        ->on('b.unit', '=', 't.unit');
                })
                ->whereBetween('b.tanggal', [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('bookings', 'tenant_id'), function (Builder $query): void {
                    PoolScope::applyTenantScope($query, 'b.tenant_id');
                })
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $query): void {
                    PoolScope::applyTenantScope($query, 't.tenant_id');
                });
            $this->applyPoolOrRouteScopeToQuery(
                $bookingQuery,
                Schema::hasColumn('trip_assignments', 'pool_id') ? 't.pool_id' : '',
                Schema::hasColumn('trip_assignments', 'route_id') ? 't.route_id' : '',
                't.rute',
                $poolId,
            );
            $this->applyArmadaMatchFilter($bookingQuery, 'trip_assignments', 't', $armadaId, $armadaNopol);
            $this->applyNotCanceledFilter($bookingQuery, 'b.status');

            $bookings = $bookingQuery
                ->orderByDesc('b.tanggal')
                ->orderByDesc('b.jam')
                ->orderByDesc('b.id')
                ->get([
                    'b.id',
                    'b.tanggal',
                    'b.jam',
                    'b.rute',
                    'b.unit',
                    'b.seat',
                    'b.name',
                    'b.phone',
                    'b.pickup_point',
                    'b.pembayaran',
                    'b.status',
                    'b.price',
                    'b.discount',
                ])
                ->map(static fn (object $row): array => [
                    'id' => (int) $row->id,
                    'tanggal' => (string) ($row->tanggal ?? ''),
                    'jam' => substr((string) ($row->jam ?? ''), 0, 5),
                    'departure_date' => (string) ($row->tanggal ?? ''),
                    'rute' => (string) ($row->rute ?? ''),
                    'unit' => (int) ($row->unit ?? 0),
                    'seat' => (string) ($row->seat ?? ''),
                    'name' => (string) ($row->name ?? ''),
                    'phone' => (string) ($row->phone ?? ''),
                    'pickup_point' => (string) ($row->pickup_point ?? ''),
                    'pembayaran' => (string) ($row->pembayaran ?? ''),
                    'status' => (string) ($row->status ?? ''),
                    'total' => max(0.0, (float) ($row->price ?? 0) - (float) ($row->discount ?? 0)),
                    'revenue' => max(0.0, (float) ($row->price ?? 0) - (float) ($row->discount ?? 0)),
                    'bop' => 0.0,
                ])
                ->values()
                ->all();
        }

        $charters = [];
        if (Schema::hasTable('charters')) {
            $charterQuery = DB::table('charters as c')
                ->when(Schema::hasColumn('charters', 'tenant_id'), function (Builder $query): void {
                    PoolScope::applyTenantScope($query, 'c.tenant_id');
                })
                ->whereBetween('c.start_date', [$monthStart, $monthEnd]);
            $this->applyCharterPoolScope($charterQuery, $poolId);
            $this->applyArmadaMatchFilter($charterQuery, 'charters', 'c', $armadaId, $armadaNopol);
            $this->applyActiveCharterReportFilter($charterQuery, 'c');

            $hasArmadaNopol = $this->chartersHasArmadaNopolColumn();
            $canJoinArmadas = $this->chartersHasArmadaIdColumn() && Schema::hasTable('armadas');

            $charterSelect = [
                'c.id',
                'c.start_date',
                'c.end_date',
                'c.departure_time',
                'c.name',
                'c.phone',
                'c.pickup_point',
                'c.drop_point',
                'c.layanan',
                'c.payment_status',
                'c.bop_status',
                DB::raw($this->chartersHasStatusColumn() ? 'c.status as status' : 'NULL as status'),
                'c.price',
                'c.bop_price',
                'c.driver_name',
            ];

            if ($hasArmadaNopol && $canJoinArmadas) {
                $charterSelect[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
                $charterQuery->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
            } elseif ($hasArmadaNopol) {
                $charterSelect[] = 'c.armada_nopol';
            } elseif ($canJoinArmadas) {
                $charterSelect[] = DB::raw('a.nopol as armada_nopol');
                $charterQuery->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
            } else {
                $charterSelect[] = DB::raw('NULL as armada_nopol');
            }

            $charters = $charterQuery
                ->orderByDesc('c.start_date')
                ->orderByDesc('c.id')
                ->get($charterSelect)
                ->map(function (object $row): array {
                    return [
                        'id' => (int) $row->id,
                        'start_date' => (string) ($row->start_date ?? ''),
                        'end_date' => (string) ($row->end_date ?? ''),
                        'departure_date' => (string) ($row->start_date ?? ''),
                        'departure_time' => (string) ($row->departure_time ?? ''),
                        'name' => (string) ($row->name ?? ''),
                        'phone' => (string) ($row->phone ?? ''),
                        'pickup_point' => (string) ($row->pickup_point ?? ''),
                        'drop_point' => (string) ($row->drop_point ?? ''),
                        'layanan' => (string) ($row->layanan ?? ''),
                        'payment_status' => (string) ($row->payment_status ?? ''),
                        'bop_status' => (string) ($row->bop_status ?? ''),
                        'status' => $this->chartersHasStatusColumn() ? (string) ($row->status ?? '') : ((string) ($row->payment_status ?? '') === 'Canceled' ? 'canceled' : 'active'),
                        'armada_nopol' => (string) ($row->armada_nopol ?? ''),
                        'driver_name' => (string) ($row->driver_name ?? ''),
                        'total' => (float) ($row->price ?? 0),
                        'revenue' => (float) ($row->price ?? 0),
                        'bop' => (float) ($row->bop_price ?? 0),
                    ];
                })
                ->values()
                ->all();
        }

        $bagasi = [];
        if (Schema::hasTable('luggages') && Schema::hasTable('trip_assignments')) {
            $luggageQuery = DB::table('luggages as l')
                ->join('trip_assignments as t', 'l.trip_assignment_id', '=', 't.id')
                ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id')
                ->whereBetween(DB::raw('COALESCE(l.tanggal, DATE(l.created_at), t.tanggal)'), [$monthStart, $monthEnd])
                ->when(Schema::hasColumn('luggages', 'tenant_id'), function (Builder $query): void {
                    PoolScope::applyTenantScope($query, 'l.tenant_id');
                })
                ->when(Schema::hasColumn('trip_assignments', 'tenant_id'), function (Builder $query): void {
                    PoolScope::applyTenantScope($query, 't.tenant_id');
                });
            $this->applyPoolOrRouteScopeToQuery(
                $luggageQuery,
                $this->luggagesHasPoolIdColumn() ? 'l.pool_id' : '',
                Schema::hasColumn('luggages', 'rute_id') ? 'l.rute_id' : '',
                'l.rute',
                $poolId,
            );
            $this->applyArmadaMatchFilter($luggageQuery, 'trip_assignments', 't', $armadaId, $armadaNopol);
            $this->applyLuggageStatusFilter($luggageQuery, 'l.status', $this->luggageRevenueStatuses());

            $bagasi = $luggageQuery
                ->orderByDesc('l.created_at')
                ->orderByDesc('l.id')
                ->get([
                    'l.id',
                    DB::raw('COALESCE(l.tanggal, DATE(l.created_at), t.tanggal) as tanggal'),
                    'l.created_at',
                    'l.kode_resi',
                    'l.sender_name',
                    'l.receiver_name',
                    'l.quantity',
                    'l.payment_status',
                    'l.status',
                    'l.price',
                    DB::raw('s.name as service_name'),
                    DB::raw('t.tanggal as departure_date'),
                    DB::raw('t.jam as departure_time'),
                    DB::raw('t.unit as departure_unit'),
                ])
                ->map(static fn (object $row): array => [
                    'id' => (int) $row->id,
                    'tanggal' => (string) ($row->tanggal ?? ''),
                    'created_at' => (string) ($row->created_at ?? ''),
                    'departure_date' => (string) ($row->departure_date ?? ($row->tanggal ?? '')),
                    'kode_resi' => (string) ($row->kode_resi ?? ''),
                    'sender_name' => (string) ($row->sender_name ?? ''),
                    'receiver_name' => (string) ($row->receiver_name ?? ''),
                    'quantity' => (int) ($row->quantity ?? 0),
                    'payment_status' => (string) ($row->payment_status ?? ''),
                    'status' => (string) ($row->status ?? ''),
                    'service_name' => (string) ($row->service_name ?? ''),
                    'total' => (float) ($row->price ?? 0),
                    'revenue' => (float) ($row->price ?? 0),
                    'bop' => 0.0,
                    'departure_time' => substr((string) ($row->departure_time ?? ''), 0, 5),
                    'departure_unit' => (int) ($row->departure_unit ?? 0),
                ])
                ->values()
                ->all();
        }

        $period = Carbon::createFromFormat('Y-m-d', $monthStart)->format('Y-m');
        $periodLabel = Carbon::createFromFormat('Y-m-d', $monthStart)
            ->locale('id')
            ->translatedFormat('F Y');

        return [
            'summary' => [
                'period' => $period,
                'period_label' => $periodLabel,
                'charter_count' => count($charters),
                'departure_count' => count($bookings),
                'luggage_count' => count($bagasi),
                'charter_revenue' => $charterRevenue,
                'departure_revenue' => $departureRevenue,
                'luggage_revenue' => $luggageRevenue,
                'total_revenue' => $totalRevenue,
                'charter_bop' => $charterBop,
                'departure_bop' => $departureBop,
                'total_bop' => $totalBop,
                'gross' => $gross,
                'fixed_cost' => $fixedCost,
                'net_margin' => $netMargin,
                'target_revenue' => $targetRevenue,
                'achievement' => $achievement,
                'status' => $achievement >= 100 ? 'Tercapai' : 'Kurang',
            ],
            'bookings' => $bookings,
            'charters' => $charters,
            'bagasi' => $bagasi,
        ];
    }

    private function applyArmadaMatchFilter(Builder $query, string $table, string $alias, int $armadaId, string $armadaNopol): void
    {
        $hasArmadaId = Schema::hasColumn($table, 'armada_id');
        $hasArmadaNopol = Schema::hasColumn($table, 'armada_nopol');

        if (! $hasArmadaId && ! $hasArmadaNopol) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $matchQuery) use ($alias, $armadaId, $armadaNopol, $hasArmadaId, $hasArmadaNopol): void {
            $applied = false;

            if ($armadaId > 0 && $hasArmadaId) {
                $matchQuery->where($alias.'.armada_id', $armadaId);
                $applied = true;
            }

            if ($armadaNopol !== '' && $hasArmadaNopol) {
                if ($applied) {
                    $matchQuery->orWhereRaw('UPPER(COALESCE('.$alias.'.armada_nopol, \'\')) = ?', [$armadaNopol]);
                } else {
                    $matchQuery->whereRaw('UPPER(COALESCE('.$alias.'.armada_nopol, \'\')) = ?', [$armadaNopol]);
                }
                $applied = true;
            }

            if (! $applied) {
                $matchQuery->whereRaw('1 = 0');
            }
        });
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
        $total = (clone $query)->getCountForPagination();
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

    /**
     * @return array<int, array{
     *     id: int,
     *     rute: string,
     *     origin: string,
     *     destination: string,
     *     jam: string,
     *     jam_pickups: array<int, string>,
     *     harga: float
     * }>
     */
    private function scheduleSegmentsForRoute(int $routeId, string $routeName): array
    {
        if (! Schema::hasTable('segments')) {
            return [];
        }

        $query = DB::table('segments as s')
            ->select([
                's.id',
                's.rute',
                's.origin',
                's.destination',
                's.jam',
                's.harga',
                Schema::hasColumn('segments', 'jam_pickups')
                    ? 's.jam_pickups'
                    : DB::raw('NULL as jam_pickups'),
            ]);
        $this->applyTenantScopeIfExists($query, 'segments', 's');

        if ($routeId > 0) {
            $query->where(function (Builder $builder) use ($routeId, $routeName): void {
                $builder->where('s.route_id', $routeId);

                if ($routeName !== '') {
                    $builder->orWhere(function (Builder $legacy) use ($routeName): void {
                        $legacy->whereNull('s.route_id')->where('s.rute', $routeName);
                    });
                }
            });
        } elseif ($routeName !== '') {
            $query->where('s.rute', $routeName);
        }

        $this->applyRouteScopeToQuery($query, 's.route_id', 's.rute');

        return $query
            ->orderBy('s.rute')
            ->orderBy('s.jam')
            ->get()
            ->map(static function ($row): array {
                return [
                    'id' => (int) ($row->id ?? 0),
                    'rute' => SegmentName::display(
                        $row->origin ?? null,
                        $row->destination ?? null,
                        $row->rute ?? '',
                    ),
                    'origin' => (string) ($row->origin ?? ''),
                    'destination' => (string) ($row->destination ?? ''),
                    'jam' => SegmentName::jam($row->jam ?? null),
                    'jam_pickups' => SegmentName::jamList(
                        $row->jam_pickups ?? null,
                        $row->jam ?? null,
                    ),
                    'harga' => (float) ($row->harga ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function scheduleSegmentJamOptions(int $routeId, string $routeName): array
    {
        $jams = [];

        foreach ($this->scheduleSegmentsForRoute($routeId, $routeName) as $segment) {
            foreach ($segment['jam_pickups'] as $jam) {
                $normalized = SegmentName::jam($jam);
                if ($normalized !== '') {
                    $jams[] = $normalized;
                }
            }
        }

        return array_values(array_unique($jams));
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
            abort(response()->json([
                'success' => false,
                'error' => 'Pilih tenant dulu.',
                'redirect_url' => route('platform.dashboard', absolute: false),
            ], 409));
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

        if ($isCreate) {
            $fallbackPoolId = $this->writablePoolContextId();

            return $fallbackPoolId > 0 ? $fallbackPoolId : -2;
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
            -2 => 'Belum ada pool yang bisa diakses untuk menyimpan data ini.',
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

    private function tripAssignmentIsLocked(?object $assignment): bool
    {
        if (! $assignment) {
            return false;
        }

        $status = ManifestLifecycle::syncTripAssignmentStatus($assignment);

        return in_array($status, ['canceled', 'closed'], true);
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

    private function driversHasKategoriColumn(): bool
    {
        if ($this->driversHasKategoriColumn === null) {
            $this->driversHasKategoriColumn = Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'kategori');
        }

        return $this->driversHasKategoriColumn;
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
                'plans.price_monthly as plan_price_monthly',
                'plans.price_yearly as plan_price_yearly',
                DB::raw('COALESCE(subscriptions.custom_price_monthly, plans.price_monthly) as price_monthly'),
                DB::raw('COALESCE(subscriptions.custom_price_yearly, plans.price_yearly) as price_yearly'),
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
                'price_monthly' => (float) ($s->price_monthly ?? $s->plan_price_monthly ?? 0),
                'price_yearly' => (float) ($s->price_yearly ?? $s->plan_price_yearly ?? 0),
                'status' => (string) $s->status,
                'trial_ends_at' => $s->trial_ends_at,
                'starts_at' => $s->starts_at,
                'ends_at' => $s->ends_at,
                'billing_interval' => (string) ($s->billing_interval ?? 'monthly'),
                'canceled_at' => $s->canceled_at,
                'grace_period_days' => (int) ($s->grace_period_days ?? 7),
                'notes' => $s->notes ?? null,
                'custom_price_monthly' => $s->custom_price_monthly ?? null,
                'custom_price_yearly' => $s->custom_price_yearly ?? null,
                'custom_max_pools' => $s->custom_max_pools ?? null,
                'custom_max_users' => $s->custom_max_users ?? null,
                'custom_max_armadas' => $s->custom_max_armadas ?? null,
                'custom_max_routes' => $s->custom_max_routes ?? null,
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
            'custom_price_monthly' => ['nullable', 'numeric', 'min:0'],
            'custom_price_yearly' => ['nullable', 'numeric', 'min:0'],
            'custom_max_pools' => ['nullable', 'integer', 'min:0'],
            'custom_max_users' => ['nullable', 'integer', 'min:0'],
            'custom_max_armadas' => ['nullable', 'integer', 'min:0'],
            'custom_max_routes' => ['nullable', 'integer', 'min:0'],
        ]);

        return DB::transaction(function () use ($id, $data): JsonResponse {
            if ($id > 0) {
                $payload = [];
                if (array_key_exists('plan_id', $data)) {
                    $payload['plan_id'] = (int) $data['plan_id'];
                }
                if (array_key_exists('status', $data)) {
                    $payload['status'] = (string) $data['status'];
                    if ($data['status'] === 'canceled') {
                        $payload['canceled_at'] = now();
                    }
                }
                if (array_key_exists('starts_at', $data)) {
                    $payload['starts_at'] = $data['starts_at'];
                }
                if (array_key_exists('ends_at', $data)) {
                    $payload['ends_at'] = $data['ends_at'];
                }
                if (array_key_exists('billing_interval', $data)) {
                    $payload['billing_interval'] = $data['billing_interval'];
                }
                if (array_key_exists('grace_period_days', $data)) {
                    $payload['grace_period_days'] = (int) $data['grace_period_days'];
                }
                if (array_key_exists('notes', $data)) {
                    $payload['notes'] = $this->nullable($data['notes']);
                }
                foreach ([
                    'custom_price_monthly',
                    'custom_price_yearly',
                    'custom_max_pools',
                    'custom_max_users',
                    'custom_max_armadas',
                    'custom_max_routes',
                ] as $field) {
                    if (array_key_exists($field, $data)) {
                        $payload[$field] = $data[$field] === null || $data[$field] === ''
                            ? null
                            : $data[$field];
                    }
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
                $customPriceMonthly = array_key_exists('custom_price_monthly', $data)
                    ? ($data['custom_price_monthly'] === null || $data['custom_price_monthly'] === '' ? null : $data['custom_price_monthly'])
                    : null;
                $customPriceYearly = array_key_exists('custom_price_yearly', $data)
                    ? ($data['custom_price_yearly'] === null || $data['custom_price_yearly'] === '' ? null : $data['custom_price_yearly'])
                    : null;

                $subId = (int) DB::table('subscriptions')->insertGetId([
                    'tenant_id' => $tenantId,
                    'plan_id' => $planId,
                    'status' => $status,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'billing_interval' => (string) ($data['billing_interval'] ?? 'monthly'),
                    'grace_period_days' => (int) ($data['grace_period_days'] ?? 7),
                    'notes' => $this->nullable($data['notes'] ?? null),
                    'custom_price_monthly' => $customPriceMonthly,
                    'custom_price_yearly' => $customPriceYearly,
                    'custom_max_pools' => array_key_exists('custom_max_pools', $data)
                        ? ($data['custom_max_pools'] === null || $data['custom_max_pools'] === '' ? null : (int) $data['custom_max_pools'])
                        : null,
                    'custom_max_users' => array_key_exists('custom_max_users', $data)
                        ? ($data['custom_max_users'] === null || $data['custom_max_users'] === '' ? null : (int) $data['custom_max_users'])
                        : null,
                    'custom_max_armadas' => array_key_exists('custom_max_armadas', $data)
                        ? ($data['custom_max_armadas'] === null || $data['custom_max_armadas'] === '' ? null : (int) $data['custom_max_armadas'])
                        : null,
                    'custom_max_routes' => array_key_exists('custom_max_routes', $data)
                        ? ($data['custom_max_routes'] === null || $data['custom_max_routes'] === '' ? null : (int) $data['custom_max_routes'])
                        : null,
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
            'payment_create_path' => (string) config('mayar.payment_create_path', '/hl/v1/invoice/create'),
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
