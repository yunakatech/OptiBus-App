<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOpsApiController extends Controller
{
    private ?bool $schedulesHasRouteId = null;
    private ?bool $schedulesHasSeatsColumn = null;
    private ?bool $schedulesHasBopColumn = null;
    private ?bool $scheduleUnitsTableExists = null;
    private ?bool $chartersHasStatusColumn = null;
    private ?bool $chartersHasArmadaIdColumn = null;
    private ?bool $chartersHasArmadaNopolColumn = null;
    private ?bool $tripAssignmentsHasArmadaId = null;
    private ?bool $tripAssignmentsHasArmadaNopol = null;
    private ?bool $tripAssignmentsHasStatus = null;
    private ?bool $driversHasArmadaId = null;
    private ?bool $driversHasArmadaNopol = null;
    private ?bool $routesHasBopColumn = null;
    private ?bool $driversHasRevenueColumn = null;
    private ?bool $driversHasBopColumn = null;
    private ?bool $driversHasFixedCostColumn = null;

    public function routesIndex(): JsonResponse
    {
        $rows = DB::table('routes')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'origin',
                'destination',
                $this->hasRoutesBopColumn() ? 'bop' : DB::raw('0 as bop'),
            ]);

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
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
        ];

        if ($this->hasRoutesBopColumn()) {
            $payload['bop'] = (float) ($data['bop'] ?? 0);
        }

        if ($id > 0) {
            DB::table('routes')->where('id', $id)->update($payload);
            if ($this->hasSchedulesRouteId()) {
                DB::table('schedules')->where('route_id', $id)->update(['rute' => $payload['name']]);
            }
            return $this->ok(['message' => 'Route updated.', 'id' => $id]);
        }

        $newId = DB::table('routes')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Route created.', 'id' => $newId], 201);
    }

    public function routesDelete(int $id): JsonResponse
    {
        if ($this->hasSchedulesRouteId()) {
            DB::table('schedules')->where('route_id', $id)->update(['route_id' => null]);
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
            $query->where('s.route_id', $routeId);
        }
        if ($rute !== '') {
            $query->where('s.rute', $rute);
        }
        if ($dow !== null && $dow !== '') {
            $query->where('s.dow', (int) $dow);
        }

        $rows = $query->get()->map(function ($row) {
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
                    ->orderBy('su.unit_no')
                    ->get([
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

        return $this->ok(['schedules' => $rows]);
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
            $route = DB::table('routes')->where('id', $routeId)->first(['id', 'name']);
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

        $duplicate = DB::table('schedules')
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
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

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
            $unitsById = DB::table('units')
                ->whereIn('id', $lookupUnitIds)
                ->get(['id', 'kapasitas', 'layout'])
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

        try {
            return DB::transaction(function () use ($id, $payload, $normalizedLabels, $normalizedUnitIds) {
                $scheduleId = $id;
                if ($scheduleId > 0) {
                    DB::table('schedules')->where('id', $scheduleId)->update($payload);
                } else {
                    $scheduleId = (int) DB::table('schedules')->insertGetId(array_merge($payload, [
                        'created_at' => now(),
                    ]));
                }

                if ($this->hasScheduleUnitsTable()) {
                    DB::table('schedule_units')->where('schedule_id', $scheduleId)->delete();
                    $rows = [];
                    foreach ($normalizedLabels as $idx => $label) {
                        $rows[] = [
                            'schedule_id' => $scheduleId,
                            'unit_no' => $idx + 1,
                            'label' => $label,
                            'unit_id' => $normalizedUnitIds[$idx] ?? null,
                            'created_at' => now(),
                        ];
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
        if ($this->hasScheduleUnitsTable()) {
            DB::table('schedule_units')->where('schedule_id', $id)->delete();
        }
        DB::table('schedules')->where('id', $id)->delete();
        return $this->ok(['message' => 'Schedule deleted.']);
    }

    public function driversIndex(): JsonResponse
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        return $this->ok(['drivers' => $this->driverRowsForMonth($monthStart, $monthEnd)]);
    }

    public function driversSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'nama' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'armada_id' => ['nullable', 'integer', 'min:1'],
            'armada_nopol' => ['nullable', 'string', 'max:50'],
            'target_revenue_bulanan' => ['nullable', 'numeric', 'min:0'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'bop' => ['nullable', 'numeric', 'min:0'],
            'fixed_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = (int) ($data['id'] ?? 0);
        $armadaId = (int) ($data['armada_id'] ?? 0);
        $requestedArmadaNopol = strtoupper(trim((string) ($data['armada_nopol'] ?? '')));
        $armadaNopol = $requestedArmadaNopol !== '' ? $requestedArmadaNopol : null;

        if (Schema::hasTable('armadas')) {
            if ($armadaId > 0) {
                $armada = DB::table('armadas')->where('id', $armadaId)->first(['id', 'nopol']);
                if (! $armada) {
                    return $this->error('Nopol armada tidak ditemukan.', 422);
                }
                $armadaNopol = strtoupper(trim((string) ($armada->nopol ?? '')));
            } elseif ($requestedArmadaNopol !== '') {
                $armada = DB::table('armadas')
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol])
                    ->first(['id', 'nopol']);

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
            'target_revenue_bulanan' => (float) ($data['target_revenue_bulanan'] ?? 0),
        ];

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

        if ($id > 0) {
            DB::table('drivers')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Driver updated.', 'id' => $id]);
        }

        $newId = DB::table('drivers')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Driver created.', 'id' => $newId], 201);
    }

    public function driversDelete(int $id): JsonResponse
    {
        DB::table('drivers')->where('id', $id)->delete();
        return $this->ok(['message' => 'Driver deleted.']);
    }

    public function luggageServicesIndex(): JsonResponse
    {
        $rows = DB::table('luggage_services')
            ->orderBy('name')
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
            DB::table('luggage_services')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Luggage service updated.', 'id' => $id]);
        }

        $newId = DB::table('luggage_services')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Luggage service created.', 'id' => $newId], 201);
    }

    public function luggageServicesDelete(int $id): JsonResponse
    {
        DB::table('luggage_services')->where('id', $id)->delete();
        return $this->ok(['message' => 'Luggage service deleted.']);
    }

    public function segmentsIndex(Request $request): JsonResponse
    {
        $routeId = (int) $request->query('route_id', 0);

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

        if ($routeId > 0) {
            $query->where('s.route_id', $routeId);
        }

        return $this->ok(['segments' => $query->get()]);
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
        $payload = [
            'route_id' => (int) $data['route_id'],
            'rute' => trim((string) $data['rute']),
            'origin' => $this->nullable($data['origin'] ?? null),
            'destination' => $this->nullable($data['destination'] ?? null),
            'harga' => (float) $data['harga'],
        ];

        if ($id > 0) {
            DB::table('segments')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Segment updated.', 'id' => $id]);
        }

        $newId = DB::table('segments')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));
        return $this->ok(['message' => 'Segment created.', 'id' => $newId], 201);
    }

    public function segmentsDelete(int $id): JsonResponse
    {
        DB::table('segments')->where('id', $id)->delete();
        return $this->ok(['message' => 'Segment deleted.']);
    }

    public function customersIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('customers')
            ->select(['id', 'name', 'phone', 'pickup_point', 'gmaps'])
            ->orderBy('name');

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
        return $this->ok([
            'customers' => $result['data'],
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
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'phone' => trim((string) $data['phone']),
            'pickup_point' => $this->nullable($data['pickup_point'] ?? null),
            'gmaps' => $this->nullable($data['gmaps'] ?? $data['address'] ?? null),
        ];

        if ($id > 0) {
            DB::table('customers')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Customer updated.', 'id' => $id]);
        }

        $existing = DB::table('customers')->where('phone', $payload['phone'])->value('id');
        if ($existing) {
            DB::table('customers')->where('id', (int) $existing)->update($payload);
            return $this->ok(['message' => 'Customer updated by phone.', 'id' => (int) $existing]);
        }

        $newId = DB::table('customers')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));
        return $this->ok(['message' => 'Customer created.', 'id' => $newId], 201);
    }

    public function customersDelete(int $id): JsonResponse
    {
        DB::table('customers')->where('id', $id)->delete();
        return $this->ok(['message' => 'Customer deleted.']);
    }

    public function customersTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'phone', 'pickup_point', 'gmaps']);
            fputcsv($out, ['Customer Contoh Qbus', '081234567890', 'Terminal Kayuringin', 'https://maps.google.com/?q=Terminal+Kayuringin']);
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

            $existingId = DB::table('customers')->where('phone', $phone)->value('id');
            if ($existingId) {
                DB::table('customers')->where('id', (int) $existingId)->update($payload);
                $updated += 1;
                continue;
            }

            DB::table('customers')->insert(array_merge($payload, [
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
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $type = $validated['type'] ?? 'booking';
        [$from, $to] = $this->normalizeDateRange($from, $to);
        [$page, $perPage] = $this->paginationParams($request);
        $rangeKey = implode(':', [
            $type,
            $from,
            $to,
            $page,
            $perPage,
            $this->reportCacheSignatureForType($type),
        ]);

        $report = Cache::remember("admin-ops:reports-summary:{$rangeKey}", now()->addSeconds(30), function () use ($type, $from, $to, $page, $perPage): array {
            return $this->buildTypedReport($type, $from, $to, $page, $perPage);
        });

        return $this->ok($report);
    }

    private function buildTypedReport(string $type, string $from, string $to, int $page, int $perPage): array
    {
        return match ($type) {
            'charter' => $this->buildCharterReport($from, $to, $page, $perPage),
            'bagasi' => $this->buildLuggageReport($from, $to, $page, $perPage),
            default => $this->buildBookingReport($from, $to, $page, $perPage),
        };
    }

    private function buildBookingReport(string $from, string $to, int $page, int $perPage): array
    {
        $baseQuery = DB::table('bookings as b')
            ->whereBetween('b.tanggal', [$from, $to]);
        $this->applyNotCanceledFilter($baseQuery, 'b.status');

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

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'booking',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => (float) ($summaryRow->revenue_total ?? 0),
            ],
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    private function buildCharterReport(string $from, string $to, int $page, int $perPage): array
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

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('COALESCE(SUM(COALESCE(c.price, 0)), 0) as revenue_total')
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

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'charter',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => (float) ($summaryRow->revenue_total ?? 0),
            ],
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    private function buildLuggageReport(string $from, string $to, int $page, int $perPage): array
    {
        [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);

        $baseQuery = DB::table('luggages as l')
            ->whereBetween('l.created_at', [$createdFrom, $createdTo]);
        $this->applyNotCanceledFilter($baseQuery, 'l.status');

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

        return [
            'summary' => [
                'from' => $from,
                'to' => $to,
                'type' => 'bagasi',
                'total_rows' => (int) ($summaryRow->total_rows ?? 0),
                'revenue_total' => (float) ($summaryRow->revenue_total ?? 0),
            ],
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    private function reportCacheSignatureForType(string $type): string
    {
        return match ($type) {
            'charter' => $this->buildTablesMutationSignature(array_values(array_filter([
                'charters',
                'units',
                $this->chartersHasArmadaNopolColumn() ? 'armadas' : null,
            ]))),
            'bagasi' => $this->buildTablesMutationSignature(['luggages', 'luggage_services']),
            default => $this->buildTablesMutationSignature(['bookings']),
        };
    }

    public function reportsBookingsCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        [$from, $to] = $this->normalizeDateRange($from, $to);
        $filename = "bookings-report-{$from}-to-{$to}.csv";

        $query = DB::table('bookings')
            ->whereBetween('tanggal', [$from, $to])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->orderBy('rute');
        $this->applyNotCanceledFilter($query, 'status');

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
        ]);

        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        [$from, $to] = $this->normalizeDateRange($from, $to);
        $type = $validated['type'] ?? 'reguler';

        $filename = "report-{$type}-{$from}-to-{$to}.csv";

        if ($type === 'bagasi') {
            [$createdFrom, $createdTo] = $this->dateTimeRange($from, $to);
            $query = DB::table('luggages as l')
                ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id')
                ->whereBetween('l.created_at', [$createdFrom, $createdTo])
                ->orderByDesc('l.created_at');
            $this->applyNotCanceledFilter($query, 'l.status');

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
            $query = DB::table('charters')
                ->whereBetween('start_date', [$from, $to])
                ->orderByDesc('start_date');
            $this->applyActiveCharterReportFilter($query, '');

            $rows = $query->get([
                    DB::raw('start_date as tanggal'),
                    'name',
                    'phone',
                    'pickup_point',
                    'drop_point',
                    DB::raw('price as final_price'),
                ]);

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Tanggal', 'Nama Penyewa', 'Nomor HP', 'Jemput - Tujuan', 'Total']);
                foreach ($rows as $row) {
                    $rute = ($row->pickup_point ?? '-') . ' - ' . ($row->drop_point ?? '-');
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

        $query
            ->select($select)
            ->orderByDesc('c.start_date')
            ->orderByDesc('c.id');

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

        $result = $this->paginateQuery($query, $page, $perPage);
        return $this->ok([
            'charters' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function chartersShow(int $id): JsonResponse
    {
        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $canJoinArmadas = $hasArmadaIdColumn && Schema::hasTable('armadas');

        $query = DB::table('charters as c')
            ->leftJoin('units as u', 'c.unit_id', '=', 'u.id')
            ->where('c.id', $id);

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
        $hasStatusColumn = $this->chartersHasStatusColumn();
        $hasArmadaIdColumn = $this->chartersHasArmadaIdColumn();
        $hasArmadaNopolColumn = $this->chartersHasArmadaNopolColumn();
        $before = [];
        if ($id > 0) {
            $existing = DB::table('charters')
                ->where('id', $id)
                ->first();
            if (! $existing) {
                return $this->error('Charter not found.', 404);
            }
            $before = (array) $existing;

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
            $selectedUnit = DB::table('units')
                ->select(['id', 'nopol', 'category'])
                ->where('id', $unitId)
                ->first();

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
                $matchedArmada = DB::table('armadas')
                    ->select(['id', 'nopol', 'kategori'])
                    ->where('id', $armadaId)
                    ->first();

                if (! $matchedArmada) {
                    return $this->error('Armada tidak ditemukan.', 422);
                }
            } elseif ($requestedArmadaNopol !== '') {
                $matchedArmada = DB::table('armadas')
                    ->select(['id', 'nopol', 'kategori'])
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol])
                    ->first();
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

        if ($hasArmadaIdColumn) {
            $payload['armada_id'] = $armadaId > 0 ? $armadaId : null;
        }

        if ($hasArmadaNopolColumn) {
            $payload['armada_nopol'] = $armadaNopol ? strtoupper(trim((string) $armadaNopol)) : null;
        }

        if ($hasStatusColumn && isset($data['status'])) {
            $payload['status'] = (string) $data['status'];
        }

        if ($id > 0) {
            DB::table('charters')->where('id', $id)->update($payload);
            $this->syncCustomerCharterFromCharterPayload($payload);
            ActivityLog::write(
                'CHARTER',
                'Carter '.$this->charterIdentityLabel($payload + $before).' diperbarui',
                $this->charterChangeSummary($before, $payload + $before),
                $this->activityActor(),
                ['charter_id' => $id],
            );
            return $this->ok(['message' => 'Charter updated.', 'id' => $id]);
        }

        if ($hasStatusColumn && !isset($payload['status'])) {
            $payload['status'] = 'active';
        }

        $newId = DB::table('charters')->insertGetId(array_merge($payload, ['created_at' => now()]));
        $this->syncCustomerCharterFromCharterPayload($payload);
        ActivityLog::write(
            'CHARTER',
            'Carter baru: '.$this->charterIdentityLabel($payload),
            $this->charterCreateSummary($payload),
            $this->activityActor(),
            ['charter_id' => $newId],
        );
        return $this->ok(['message' => 'Charter created.', 'id' => $newId], 201);
    }

    public function chartersDelete(int $id): JsonResponse
    {
        $before = (array) (DB::table('charters')->where('id', $id)->first() ?? []);

        if ($this->chartersHasStatusColumn()) {
            $updated = DB::table('charters')
                ->where('id', $id)
                ->update(['status' => 'canceled']);
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

        $updated = DB::table('charters')
            ->where('id', $id)
            ->update(['payment_status' => 'Canceled']);
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

        if ($this->chartersHasStatusColumn()) {
            $updated = DB::table('charters')
                ->whereIn('id', $data['ids'])
                ->update(['status' => 'canceled']);
            return $this->ok(['message' => 'Bulk cancel charters done.', 'updated' => $updated]);
        }

        $updated = DB::table('charters')
            ->whereIn('id', $data['ids'])
            ->update(['payment_status' => 'Canceled']);
        return $this->ok(['message' => 'Bulk cancel charters done.', 'updated' => $updated]);
    }

    public function chartersMarkBopDone(int $id): JsonResponse
    {
        $before = (array) (DB::table('charters')->where('id', $id)->first() ?? []);
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

        $senderPhone = $this->normalizePhone((string) $data['sender_phone']);
        $receiverPhone = $this->normalizePhone((string) $data['receiver_phone']);
        $senderName = strtoupper(trim((string) $data['sender_name']));
        $receiverName = strtoupper(trim((string) $data['receiver_name']));
        $senderAddress = $this->nullable($data['sender_address'] ?? null);
        $receiverAddress = $this->nullable($data['receiver_address'] ?? null);

        $pengirimId = $this->upsertCustomerBagasi($senderName, $senderPhone, $senderAddress, 'pengirim');
        $penerimaId = $this->upsertCustomerBagasi($receiverName, $receiverPhone, $receiverAddress, 'penerima');

        $inputPrice = (float) ($data['price'] ?? 0);
        $mappedPrice = $this->resolveMappedLuggagePrice($routeId, $serviceId);
        $resolvedPrice = $inputPrice > 0 ? $inputPrice : $mappedPrice;
        $routeName = $routeId > 0 ? (string) (DB::table('routes')->where('id', $routeId)->value('name') ?? '') : '';

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
            'unit_id' => isset($data['unit_id']) ? (int) $data['unit_id'] : null,
            'pengirim_id' => $pengirimId > 0 ? $pengirimId : null,
            'penerima_id' => $penerimaId > 0 ? $penerimaId : null,
        ];

        if ($id > 0) {
            $previous = DB::table('luggages')->where('id', $id)->first();
            DB::table('luggages')->where('id', $id)->update($payload);
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
        DB::table('luggages')->where('id', $id)->delete();
        return $this->ok(['message' => 'Luggage deleted.']);
    }

    public function luggagesBulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $deleted = DB::table('luggages')->whereIn('id', $data['ids'])->delete();
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

        $payload = ['status' => $this->normalizeLuggageStatus($data['status'] ?? null)];
        if (isset($data['payment_status'])) {
            $payload['payment_status'] = trim((string) $data['payment_status']);
        }

        $beforeRows = DB::table('luggages')
            ->whereIn('id', $data['ids'])
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->id => (array) $row]);

        $updated = DB::table('luggages')
            ->whereIn('id', $data['ids'])
            ->update($payload);

        if ($updated > 0) {
            $rows = DB::table('luggages')->whereIn('id', $data['ids'])->get(['id', 'kode_resi']);
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
        $before = (array) (DB::table('luggages')->where('id', $id)->first() ?? []);
        $updated = DB::table('luggages')->where('id', $id)->update(['payment_status' => 'Lunas']);
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
        $before = (array) (DB::table('luggages')->where('id', $id)->first() ?? []);
        $status = $this->luggagePickedUpStatus();
        $updated = DB::table('luggages')->where('id', $id)->update(['status' => $status]);
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
        $before = (array) (DB::table('luggages')->where('id', $id)->first() ?? []);
        $status = $this->luggageArrivedStatus();
        $updated = DB::table('luggages')->where('id', $id)->update(['status' => $status]);
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
        $before = (array) (DB::table('luggages')->where('id', $id)->first() ?? []);
        $updated = DB::table('luggages')->where('id', $id)->update(['status' => 'canceled']);
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
        $luggage = DB::table('luggages')
            ->where('id', $id)
            ->first(['id', 'kode_resi', 'sender_name', 'receiver_name', 'status', 'payment_status']);

        if (! $luggage) {
            return $this->error('Luggage not found.', 404);
        }

        $resi = $luggage->kode_resi ?: $this->ensureLuggageResi($id);
        $logs = DB::table('bagasi_logs')
            ->where('kode_resi', $resi)
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

        $exists = DB::table('luggages')->where('id', $id)->exists();
        if (! $exists) {
            return $this->error('Luggage not found.', 404);
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
            $select[] = DB::raw("COALESCE(t.armada_nopol, a.nopol) as armada_nopol");
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

        if (Schema::hasTable('armadas')) {
            if ($armadaId <= 0 && $requestedArmadaNopol !== '') {
                $matchedArmada = DB::table('armadas')
                    ->select('id', 'nopol')
                    ->whereRaw('UPPER(nopol) = ?', [$requestedArmadaNopol])
                    ->first();

                if ($matchedArmada) {
                    $armadaId = (int) $matchedArmada->id;
                    $armadaNopol = strtoupper(trim((string) $matchedArmada->nopol));
                }
            } elseif ($armadaId > 0) {
                $storedNopol = DB::table('armadas')->where('id', $armadaId)->value('nopol');

                if ($storedNopol) {
                    $armadaNopol = strtoupper(trim((string) $storedNopol));
                }
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

        $newId = DB::table('trip_assignments')->insertGetId(array_merge($payload, ['created_at' => now()]));
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

        if ($id > 0) {
            DB::table('customer_bagasi')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Customer bagasi updated.', 'id' => $id]);
        }

        $existingId = (int) (DB::table('customer_bagasi')->where('no_hp', $payload['no_hp'])->value('id') ?? 0);
        if ($existingId > 0) {
            DB::table('customer_bagasi')->where('id', $existingId)->update($payload);
            return $this->ok(['message' => 'Customer bagasi updated by phone.', 'id' => $existingId]);
        }

        $newId = DB::table('customer_bagasi')->insertGetId(array_merge($payload, ['created_at' => now()]));
        return $this->ok(['message' => 'Customer bagasi created.', 'id' => $newId], 201);
    }

    public function customerBagasiDelete(int $id): JsonResponse
    {
        DB::table('customer_bagasi')->where('id', $id)->delete();
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

        if ($id > 0) {
            DB::table('customer_charter')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Customer charter updated.', 'id' => $id]);
        }

        $existingId = (int) (DB::table('customer_charter')->where('no_hp', $payload['no_hp'])->value('id') ?? 0);
        if ($existingId > 0) {
            DB::table('customer_charter')->where('id', $existingId)->update($payload);
            return $this->ok(['message' => 'Customer charter updated by phone.', 'id' => $existingId]);
        }

        $newId = DB::table('customer_charter')->insertGetId(array_merge($payload, ['created_at' => now()]));
        return $this->ok(['message' => 'Customer charter created.', 'id' => $newId], 201);
    }

    public function customerCharterDelete(int $id): JsonResponse
    {
        DB::table('customer_charter')->where('id', $id)->delete();
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
            ->orderBy('destination');

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

        if ($id > 0) {
            DB::table('master_carter')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Rute carter updated.', 'id' => $id]);
        }

        $newId = DB::table('master_carter')->insertGetId(array_merge($payload, ['created_at' => now()]));
        return $this->ok(['message' => 'Rute carter created.', 'id' => $newId], 201);
    }

    public function charterRoutesMasterDelete(int $id): JsonResponse
    {
        DB::table('master_carter')->where('id', $id)->delete();
        return $this->ok(['message' => 'Rute carter deleted.']);
    }

    public function unitsIndex(): JsonResponse
    {
        $status = trim((string) request()->query('status', ''));

        $query = DB::table('units')
            ->orderBy('nopol')
            ->select(['id', 'nopol', 'merek', 'type', 'category', 'tahun', 'warna', 'kapasitas', 'status', 'layout']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        $rows = $query
            ->get()
            ->map(function ($row) {
                $row->category = $this->normalizeUnitCategory($row->category ?? null);

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
        $existing = $id > 0
            ? DB::table('units')->where('id', $id)->first()
            : null;

        $duplicate = DB::table('units')
            ->whereRaw('UPPER(nopol) = ?', [$nopol])
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
            DB::table('units')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Unit updated.', 'id' => $id]);
        }

        $newId = DB::table('units')->insertGetId(array_merge($payload, [
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

    public function unitsDelete(int $id): JsonResponse
    {
        DB::table('schedules')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('drivers')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('charters')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('luggages')->where('unit_id', $id)->update(['unit_id' => null]);
        DB::table('units')->where('id', $id)->delete();

        return $this->ok(['message' => 'Unit deleted.']);
    }

    public function armadaCategoriesIndex(): JsonResponse
    {
        if (! Schema::hasTable('units')) {
            return $this->ok(['categories' => []]);
        }

        $rows = DB::table('units')
            ->select('category')
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
            ->select([
                'id',
                'merk',
                'tahun',
                'warna',
                'nopol',
                'nomor_rangka',
                'kategori',
                'ac_type',
                'platform_gps',
                'api_gps',
                'revenue',
                'bop',
                'fixed_cost',
                'target_bulanan',
                'target_tahunan',
            ])
            ->orderBy('nopol');

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function (Builder $builder) use ($qLike) {
                $builder
                    ->where('nopol', 'like', $qLike)
                    ->orWhere('nomor_rangka', 'like', $qLike)
                    ->orWhere('merk', 'like', $qLike)
                    ->orWhere('kategori', 'like', $qLike)
                    ->orWhere('platform_gps', 'like', $qLike);
            });
        }

        if ($kategori !== '') {
            $query->where('kategori', $kategori);
        }

        if (in_array($acType, ['AC', 'Non-AC'], true)) {
            $query->where('ac_type', $acType);
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);

        $rows = $query
            ->get()
            ->map(fn ($row) => $this->withArmadaFinancials($row, $financials))
            ->values();

        return $this->ok(['armadas' => $rows]);
    }

    public function armadasSave(Request $request): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->error('Tabel armada belum tersedia. Jalankan migration terlebih dahulu.', 500);
        }

        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
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
        $nopol = strtoupper(trim((string) $data['nopol']));

        $duplicate = DB::table('armadas')
            ->whereRaw('UPPER(nopol) = ?', [$nopol])
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

        if ($id > 0) {
            DB::table('armadas')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'Armada updated.', 'id' => $id]);
        }

        $newId = DB::table('armadas')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));

        return $this->ok(['message' => 'Armada created.', 'id' => $newId], 201);
    }

    public function armadasDelete(int $id): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->ok(['message' => 'Armada deleted.']);
        }

        $nopol = (string) (DB::table('armadas')->where('id', $id)->value('nopol') ?? '');

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

        DB::table('armadas')->where('id', $id)->delete();
        return $this->ok(['message' => 'Armada deleted.']);
    }

    public function armadasShow(int $id): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->error('Data armada tidak tersedia.', 404);
        }

        $row = DB::table('armadas')
            ->where('id', $id)
            ->first([
                'id',
                'merk',
                'tahun',
                'warna',
                'nopol',
                'nomor_rangka',
                'kategori',
                'ac_type',
                'platform_gps',
                'api_gps',
                'revenue',
                'bop',
                'fixed_cost',
                'target_bulanan',
                'target_tahunan',
            ]);

        if (! $row) {
            return $this->error('Armada tidak ditemukan.', 404);
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $financials = $this->armadaFinancialsForMonth($monthStart, $monthEnd);

        return $this->ok(['armada' => $this->withArmadaFinancials($row, $financials)]);
    }

    public function usersIndex(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        [$page, $perPage] = $this->paginationParams($request);

        $query = DB::table('users')
            ->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
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

        return $this->ok([
            'users' => $result['data'],
            'pagination' => $result['meta'],
        ]);
    }

    public function usersSave(Request $request): JsonResponse
    {
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
        ]);

        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
        ];

        $password = trim((string) ($data['password'] ?? ''));
        if ($password !== '') {
            $payload['password'] = Hash::make($password);
        }

        if ($id > 0) {
            DB::table('users')->where('id', $id)->update($payload);
            return $this->ok(['message' => 'User updated.', 'id' => $id]);
        }

        if (! isset($payload['password'])) {
            return $this->error('Password wajib untuk user baru.', 422);
        }

        $newId = DB::table('users')->insertGetId(array_merge($payload, [
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

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

        DB::table('users')->where('id', $id)->delete();

        return $this->ok(['message' => 'User deleted.']);
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
     * @param array<int, string|null> $row
     * @param array<string, int> $columns
     */
    private function customerImportValue(array $row, array $columns, string $key): string
    {
        if (! array_key_exists($key, $columns)) {
            return '';
        }

        return trim((string) ($row[$columns[$key]] ?? ''));
    }

    /**
     * @param array<int, string|null> $row
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

    private function upsertCustomerBagasi(string $nama, string $noHp, ?string $alamat, string $tipe): int
    {
        if ($noHp === '') {
            return 0;
        }

        $existing = DB::table('customer_bagasi')
            ->where('no_hp', $noHp)
            ->first(['id', 'tipe']);

        if ($existing) {
            $existingTipe = strtolower((string) ($existing->tipe ?? ''));
            $nextTipe = $existingTipe;
            if ($existingTipe !== $tipe && $existingTipe !== 'keduanya') {
                $nextTipe = 'keduanya';
            }

            DB::table('customer_bagasi')->where('id', (int) $existing->id)->update([
                'nama' => $nama,
                'alamat' => $alamat,
                'tipe' => $nextTipe === '' ? $tipe : $nextTipe,
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('customer_bagasi')->insertGetId([
            'nama' => $nama,
            'no_hp' => $noHp,
            'alamat' => $alamat,
            'tipe' => $tipe,
            'created_at' => now(),
        ]);
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

        $last = (string) (DB::table('luggages')
            ->where('kode_resi', 'like', $like)
            ->orderByDesc('id')
            ->value('kode_resi') ?? '');

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

        DB::table('bagasi_logs')->insert([
            'kode_resi' => $resi,
            'status' => $normalizedStatus,
            'notes' => $this->nullable($notes),
            'created_by_username' => $actor,
            'created_at' => now(),
        ]);

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

        $rows = DB::table('routes')->get(['id', 'name']);
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
            'd.target_revenue_bulanan',
            $this->hasDriversRevenueColumn() ? 'd.revenue' : DB::raw('0 as revenue'),
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
            ->orderBy('d.nama')
            ->get($select);

        $scheduleBopMap = $this->scheduleBopMap();
        $bookingRevenueMap = $this->bookingRevenueByTripForDateRange($monthStart, $monthEnd);

        $departureRevenueByDriver = [];
        $departureBopByDriver = [];
        $assignmentRows = DB::table('trip_assignments')
            ->whereNotNull('driver_id')
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                $query->where(function (Builder $statusQuery) {
                    $statusQuery
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'canceled');
                });
            })
            ->get(['driver_id', 'rute', 'tanggal', 'jam', 'unit']);

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
                ])
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
        $charterRows = DB::table('charters')
            ->whereBetween('start_date', [$monthStart, $monthEnd])
            ->get([
                'driver_name',
                'price',
                'bop_price',
                'payment_status',
                'bop_status',
                $hasCharterStatus ? 'status' : DB::raw('NULL as status'),
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
            $this->buildTableMutationSignature('schedules'),
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(5), function (): array {
            $map = [];
            $rows = DB::table('schedules')->get(['rute', 'dow', 'jam', 'bop']);

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
                ->when($this->tripAssignmentsHasStatus(), static function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery
                            ->whereNull('t.status')
                            ->orWhere('t.status', '!=', 'canceled');
                    });
                })
                ->get($assignmentSelect);

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
                ->get($luggageSelect);

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
                ->get($charterSelect);

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
     * @param array<string, array<string, float>> $financials
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
     * @return array{data: \Illuminate\Support\Collection<int, object>, meta: array<string, int>}
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
     * @param array<int, string> $tables
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
     * @param array<string, mixed> $payload
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

        $existingId = (int) (DB::table('customer_charter')->where('no_hp', $phone)->value('id') ?? 0);

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

            DB::table('customer_charter')->where('id', $existingId)->update($updatePayload);

            return;
        }

        try {
            DB::table('customer_charter')->insert(array_merge($customerPayload, ['created_at' => now()]));
        } catch (QueryException) {
            DB::table('customer_charter')->where('no_hp', $phone)->update([
                'nama' => $customerPayload['nama'],
                'alamat' => $customerPayload['alamat'],
                'company' => $customerPayload['company'],
            ]);
        }
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
