<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActivityLog;
use App\Support\PoolScope;
use App\Support\SegmentName;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationsApiController extends Controller
{
    public function charterRoutes(): JsonResponse
    {
        $routes = DB::table('master_carter')
            ->when(Schema::hasColumn('master_carter', 'tenant_id'), function ($query): void {
                PoolScope::applyTenantScope($query, 'tenant_id');
            })
            ->when(Schema::hasColumn('master_carter', 'pool_id'), function (Builder $query): void {
                $this->applyPoolScopeIfExists($query, 'master_carter');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'origin', 'destination', 'duration', 'rental_price', 'bop_price']);

        return $this->ok(['routes' => $routes]);
    }

    public function segments(Request $request): JsonResponse
    {
        $routeName = trim((string) $request->query('route_name', ''));

        $query = DB::table('segments as s')
            ->leftJoin('routes as r', 's.route_id', '=', 'r.id')
            ->select(['s.id', 's.rute', 's.origin', 's.destination', 's.jam', 's.harga']);
        if (Schema::hasColumn('segments', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 's.tenant_id');
        }

        if ($routeName !== '') {
            $query->where('r.name', $routeName);
        }
        PoolScope::applyRouteScope($query, 's.route_id', 's.rute');

        $segments = $query->orderBy('s.rute')->get()->map(function ($row) {
            $row->rute = SegmentName::display(
                $row->origin ?? null,
                $row->destination ?? null,
                $row->rute ?? '',
            );
            $row->jam = SegmentName::jam($row->jam ?? null);

            return $row;
        })->values();

        return $this->ok(['segments' => $segments]);
    }

    public function segmentPrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $query = DB::table('segments as s')
            ->where('s.id', $validated['id']);
        if (Schema::hasColumn('segments', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 's.tenant_id');
        }
        PoolScope::applyRouteScope($query, 's.route_id', 's.rute');

        $price = $query->value('s.harga');

        return $this->ok(['price' => (float) ($price ?? 0)]);
    }

    public function units(): JsonResponse
    {
        if (! Schema::hasTable('units')) {
            return $this->ok(['units' => []]);
        }

        $columns = ['id', 'nopol'];
        foreach (['merek', 'type', 'kapasitas', 'category', 'status', 'layout'] as $column) {
            if (Schema::hasColumn('units', $column)) {
                $columns[] = $column;
            }
        }
        if (Schema::hasColumn('units', 'pool_id')) {
            $columns[] = 'pool_id';
        }

        $query = DB::table('units')->orderBy('nopol');
        if (Schema::hasColumn('units', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($query, 'units');
        if (Schema::hasColumn('units', 'status')) {
            $query->where('status', 'Aktif');
        }

        $units = $query->get($columns);
        $poolNames = $this->poolNameMap($units->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $units = $units->map(function ($row) use ($poolNames) {
            $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
            $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

            return $row;
        })->values();

        return $this->ok(['units' => $units]);
    }

    public function armadas(Request $request): JsonResponse
    {
        if (! Schema::hasTable('armadas')) {
            return $this->ok(['armadas' => []]);
        }

        $q = trim((string) $request->query('q', ''));
        $kategori = trim((string) $request->query('kategori', ''));
        $columns = ['id', 'nopol'];
        foreach (['merk', 'tahun', 'warna', 'kategori', 'ac_type', 'nomor_rangka'] as $column) {
            if (Schema::hasColumn('armadas', $column)) {
                $columns[] = $column;
            }
        }
        if (Schema::hasColumn('armadas', 'pool_id')) {
            $columns[] = 'pool_id';
        }

        $query = DB::table('armadas')->select($columns)->orderBy('nopol');
        if (Schema::hasColumn('armadas', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($query, 'armadas');
        $availableColumns = array_flip($columns);

        if ($q !== '') {
            $qLike = '%'.$q.'%';
            $query->where(function ($builder) use ($qLike, $availableColumns): void {
                $hasClause = false;

                foreach (['nopol', 'nomor_rangka', 'merk', 'kategori'] as $column) {
                    if (! isset($availableColumns[$column])) {
                        continue;
                    }

                    $hasClause
                        ? $builder->orWhere($column, 'like', $qLike)
                        : $builder->where($column, 'like', $qLike);
                    $hasClause = true;
                }
            });
        }

        if ($kategori !== '' && isset($availableColumns['kategori'])) {
            $query->where('kategori', $kategori);
        }

        $armadas = $query->get();
        $poolNames = $this->poolNameMap($armadas->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $armadas = $armadas->map(function ($row) use ($poolNames) {
            $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
            $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

            return $row;
        })->values();

        return $this->ok(['armadas' => $armadas]);
    }

    public function drivers(): JsonResponse
    {
        $query = DB::table('drivers')->orderBy('nama');
        if (Schema::hasColumn('drivers', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($query, 'drivers');

        $columns = ['id', 'nama', 'phone'];
        if (Schema::hasColumn('drivers', 'pool_id')) {
            $columns[] = 'pool_id';
        }

        $drivers = $query->get($columns);
        $poolNames = $this->poolNameMap($drivers->pluck('pool_id')->map(static fn ($value): int => (int) $value)->all());
        $drivers = $drivers->map(function ($row) use ($poolNames) {
            $row->pool_id = (int) ($row->pool_id ?? 0) ?: null;
            $row->pool_name = $row->pool_id ? ($poolNames[$row->pool_id] ?? null) : null;

            return $row;
        })->values();

        return $this->ok(['drivers' => $drivers]);
    }

    public function luggageServices(): JsonResponse
    {
        $query = DB::table('luggage_services')->orderBy('name');
        if (Schema::hasColumn('luggage_services', 'tenant_id')) {
            PoolScope::applyTenantScope($query, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($query, 'luggage_services');

        $services = $query->get(['id', 'name']);

        return $this->ok(['services' => $services]);
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $scope = PoolScope::forCurrentUser();

        if ($q === '' || mb_strlen($q) < 2) {
            return $this->ok([
                'customers' => [],
                'has_more' => false,
                'scope_limited' => ! $scope['all'],
                'scope_name' => $scope['pool_name'],
            ]);
        }

        $normalizedQuery = preg_replace('/\s+/', ' ', mb_strtolower($q)) ?? mb_strtolower($q);
        $tokens = array_slice(
            array_values(array_filter(explode(' ', $normalizedQuery), static fn (string $token): bool => $token !== '')),
            0,
            6,
        );
        $cacheKey = 'ops:customer-search:v2:'.PoolScope::cacheKey().':'.md5($normalizedQuery);
        $result = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($normalizedQuery, $tokens) {
            $normalizedPhoneSql = $this->normalizedCustomerPhoneSql();
            $phoneVariants = $this->customerPhoneSearchVariants($normalizedQuery);

            $query = DB::table('customers')
                ->select([
                    'name',
                    'phone',
                    'pickup_point',
                    'gmaps',
                    DB::raw('gmaps as address'),
                ]);
            PoolScope::applyCustomerScope($query, 'customers');

            foreach ($tokens as $token) {
                $like = '%'.$token.'%';
                $tokenPhoneVariants = $this->customerPhoneSearchVariants($token);

                $query->where(function ($termQuery) use ($like, $normalizedPhoneSql, $tokenPhoneVariants): void {
                    $termQuery
                        ->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(pickup_point, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(gmaps, '')) LIKE ?", [$like]);

                    foreach ($tokenPhoneVariants as $phoneVariant) {
                        $termQuery->orWhereRaw($normalizedPhoneSql.' LIKE ?', ['%'.$phoneVariant.'%']);
                    }
                });
            }

            $rankCases = [];
            $rankBindings = [];
            $rank = 0;

            if ($phoneVariants !== []) {
                $placeholders = implode(', ', array_fill(0, count($phoneVariants), '?'));
                $rankCases[] = "WHEN {$normalizedPhoneSql} IN ({$placeholders}) THEN {$rank}";
                array_push($rankBindings, ...$phoneVariants);
                $rank++;
            }

            $rankCases[] = "WHEN LOWER(COALESCE(name, '')) = ? THEN {$rank}";
            $rankBindings[] = $normalizedQuery;
            $rank++;
            $rankCases[] = "WHEN LOWER(COALESCE(name, '')) LIKE ? THEN {$rank}";
            $rankBindings[] = $normalizedQuery.'%';
            $rank++;

            if ($phoneVariants !== []) {
                $prefixCases = [];
                foreach ($phoneVariants as $phoneVariant) {
                    $prefixCases[] = $normalizedPhoneSql.' LIKE ?';
                    $rankBindings[] = $phoneVariant.'%';
                }
                $rankCases[] = 'WHEN ('.implode(' OR ', $prefixCases).") THEN {$rank}";
                $rank++;
            }

            $customers = $query
                ->orderByRaw('CASE '.implode(' ', $rankCases)." ELSE {$rank} END", $rankBindings)
                ->orderBy('name')
                ->limit(21)
                ->get();

            return [
                'customers' => $customers->take(20)->values(),
                'has_more' => $customers->count() > 20,
            ];
        });

        return $this->ok([
            'customers' => $result['customers'],
            'has_more' => $result['has_more'],
            'scope_limited' => ! $scope['all'],
            'scope_name' => $scope['pool_name'],
        ]);
    }

    public function submitCharter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'pickup_point' => ['nullable', 'string', 'max:180'],
            'drop_point' => ['nullable', 'string', 'max:180'],
            'unit_id' => ['required', 'integer', 'min:1'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'layanan' => ['nullable', 'string', 'max:120'],
            'bop_price' => ['nullable', 'numeric', 'min:0'],
            'pool_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $payload = [
            'name' => strtoupper(trim((string) $data['name'])),
            'company_name' => $this->nullableString($data['company_name'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'departure_time' => ! empty($data['departure_time']) ? $data['departure_time'].':00' : null,
            'pickup_point' => $this->nullableString($data['pickup_point'] ?? null),
            'drop_point' => $this->nullableString($data['drop_point'] ?? null),
            'unit_id' => (int) $data['unit_id'],
            'driver_name' => $this->nullableString($data['driver_name'] ?? null),
            'price' => (float) ($data['price'] ?? 0),
            'layanan' => $this->nullableString($data['layanan'] ?? null) ?? 'Regular',
            'bop_price' => (float) ($data['bop_price'] ?? 0),
            'created_at' => now(),
        ];

        if (Schema::hasColumn('charters', 'pool_id')) {
            $poolId = $this->resolveCharterPoolId(
                (int) ($data['pool_id'] ?? 0),
                (string) ($data['pickup_point'] ?? ''),
                (string) ($data['drop_point'] ?? ''),
            );

            if ($poolId < 0) {
                return $this->error($this->charterPoolErrorMessage($poolId), 422);
            }

            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        $unitQuery = DB::table('units')->where('id', (int) $data['unit_id']);
        if (Schema::hasColumn('units', 'tenant_id')) {
            PoolScope::applyTenantScope($unitQuery, 'tenant_id');
        }
        $this->applyPoolScopeIfExists($unitQuery, 'units', '', (int) ($payload['pool_id'] ?? 0) ?: null);
        if (! $unitQuery->exists()) {
            return $this->error('Kategori armada tidak ditemukan untuk pool aktif.', 422);
        }

        if (Schema::hasColumn('charters', 'status')) {
            $payload['status'] = 'active';
        }
        $payload = array_merge($payload, $this->tenantPayload('charters'));

        $id = DB::table('charters')->insertGetId($payload);
        $this->syncMasterCarterFromCharterPayload($data);

        return $this->ok([
            'message' => 'Charter submitted successfully',
            'charter_id' => $id,
        ], 201);
    }

    public function submitLuggage(Request $request): JsonResponse
    {
        $data = $request->validate([
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
            'payment_status' => ['nullable', 'string', 'max:30'],
        ]);

        $serviceId = (int) ($data['service_id'] ?? $data['layanan_id'] ?? 0);
        $routeId = (int) ($data['rute_id'] ?? 0);
        $senderName = strtoupper(trim((string) $data['sender_name']));
        $receiverName = strtoupper(trim((string) $data['receiver_name']));
        $senderPhone = $this->normalizePhone((string) $data['sender_phone']);
        $receiverPhone = $this->normalizePhone((string) $data['receiver_phone']);
        $senderAddress = $this->nullableString($data['sender_address'] ?? null);
        $receiverAddress = $this->nullableString($data['receiver_address'] ?? null);
        $customerPoolId = PoolScope::customerPoolId($routeId);
        $unitId = (int) ($data['unit_id'] ?? 0);
        if ($unitId > 0 && Schema::hasTable('units')) {
            $unitQuery = DB::table('units')->where('id', $unitId);
            if (Schema::hasColumn('units', 'tenant_id')) {
                PoolScope::applyTenantScope($unitQuery, 'tenant_id');
            }
            $this->applyPoolScopeIfExists($unitQuery, 'units', '', $customerPoolId > 0 ? $customerPoolId : null);
            if (! $unitQuery->exists()) {
                return $this->error('Kategori armada tidak ditemukan untuk pool aktif.', 422);
            }
        }
        $pengirimId = $this->upsertCustomerBagasi($senderName, $senderPhone, $senderAddress, 'pengirim', $customerPoolId);
        $penerimaId = $this->upsertCustomerBagasi($receiverName, $receiverPhone, $receiverAddress, 'penerima', $customerPoolId);
        $mappedPrice = $this->resolveMappedLuggagePrice($routeId, $serviceId);
        $inputPrice = (float) ($data['price'] ?? 0);
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
            'rute_id' => $routeId > 0 ? $routeId : null,
            'rute' => $routeName !== '' ? $routeName : null,
            'tanggal' => $data['tanggal'] ?? now()->toDateString(),
            'unit_id' => $unitId > 0 ? $unitId : null,
            'pengirim_id' => $pengirimId > 0 ? $pengirimId : null,
            'penerima_id' => $penerimaId > 0 ? $penerimaId : null,
            'quantity' => max(1, (int) ($data['quantity'] ?? 1)),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'price' => $resolvedPrice,
            'status' => $this->luggageReceivedStatus(),
            'payment_status' => $this->nullableString($data['payment_status'] ?? null) ?? 'Belum Bayar',
            'kode_resi' => $this->nextLuggageResi(),
            'created_at' => now(),
        ];
        if (Schema::hasColumn('luggages', 'pool_id')) {
            $payload['pool_id'] = $customerPoolId > 0 ? $customerPoolId : null;
        }
        $payload = array_merge($payload, $this->tenantPayload('luggages'));

        $id = DB::table('luggages')->insertGetId($payload);

        $resi = (string) (DB::table('luggages')->where('id', $id)->value('kode_resi') ?? '');
        if ($resi !== '' && DB::getSchemaBuilder()->hasTable('bagasi_logs')) {
            DB::table('bagasi_logs')->insert(array_merge([
                'kode_resi' => $resi,
                'status' => $this->luggageReceivedStatus(),
                'notes' => $this->luggageReceivedStatus(),
                'created_by_username' => auth()->user()?->email ?? auth()->user()?->name ?? 'system',
                'created_at' => now(),
            ], $this->tenantPayload('bagasi_logs')));
        }

        ActivityLog::write(
            'BAGASI',
            'Bagasi '.$resi.' - '.$this->luggageReceivedStatus(),
            $this->luggageReceivedStatus(),
            (string) (auth()->user()?->email ?? auth()->user()?->name ?? 'system'),
            ['kode_resi' => $resi, 'status' => $this->luggageReceivedStatus()],
        );

        return $this->ok([
            'message' => 'Luggage shipment saved successfully',
            'luggage_id' => $id,
            'kode_resi' => $resi,
        ], 201);
    }

    private function nullableString(?string $value): ?string
    {
        $v = trim((string) ($value ?? ''));

        return $v === '' ? null : $v;
    }

    /**
     * @return array<string, int>
     */
    private function tenantPayload(string $table): array
    {
        if (! Schema::hasColumn($table, 'tenant_id')) {
            return [];
        }

        return ['tenant_id' => $this->requireTenantContext()];
    }

    private function requireTenantContext(): int
    {
        $tenantId = PoolScope::tenantId();
        if ($tenantId > 0) {
            return $tenantId;
        }

        abort(response()->json([
            'success' => false,
            'error' => 'Pilih tenant dulu.',
            'redirect_url' => route('platform.dashboard', absolute: false),
        ], 409));
    }

    private function currentPoolContextId(): int
    {
        return PoolScope::currentPoolId(auth()->id());
    }

    private function writablePoolContextId(): int
    {
        return PoolScope::defaultWritablePoolId(auth()->id());
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

    private function poolPayload(string $table, ?int $poolId = null): array
    {
        if (! Schema::hasColumn($table, 'pool_id')) {
            return [];
        }

        $resolvedPoolId = $poolId ?? $this->writablePoolContextId();

        return ['pool_id' => $resolvedPoolId > 0 ? $resolvedPoolId : null];
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

    /**
     * @return array{0: string, 1: string}
     */
    private function parseTableAlias(string $table): array
    {
        $table = trim($table);
        if (preg_match('/^([a-z0-9_]+)(?:\s+as\s+([a-z0-9_]+))?$/i', $table, $matches) === 1) {
            return [
                $matches[1],
                isset($matches[2]) ? $matches[2] : '',
            ];
        }

        return [$table, ''];
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

    private function resolveCharterPoolId(int $requestedPoolId, string $pickupPoint, string $dropPoint): int
    {
        if (! PoolScope::tablesReady() || (int) DB::table('pools')->count() === 0) {
            return 0;
        }

        $scope = PoolScope::forCurrentUser();
        $allowedPoolIds = $scope['all']
            ? DB::table('pools')
                ->where('status', 'active')
                ->pluck('id')
                ->map(static fn ($value) => (int) $value)
                ->values()
                ->all()
            : $scope['pool_ids'];

        if ($allowedPoolIds === []) {
            return -1;
        }

        $isAllowed = static fn (int $poolId): bool => $poolId > 0 && in_array($poolId, $allowedPoolIds, true);
        $mappedPoolId = 0;

        foreach ([$pickupPoint, $dropPoint] as $label) {
            $labelPoolId = $this->poolIdForCharterLabel($label);

            if ($labelPoolId > 0) {
                $mappedPoolId = $labelPoolId;

                break;
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

        if (count($allowedPoolIds) === 1) {
            return (int) $allowedPoolIds[0];
        }

        return $scope['all'] ? 0 : -2;
    }

    private function poolIdForCharterLabel(string $label): int
    {
        $normalized = $this->normalizeCharterLabel($label);
        if ($normalized === '' || ! PoolScope::tablesReady()) {
            return 0;
        }

        $routes = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->get(['pr.pool_id', 'r.name', 'r.origin', 'r.destination']);

        foreach ($routes as $route) {
            foreach (['name', 'origin', 'destination'] as $field) {
                if ($this->normalizeCharterLabel((string) ($route->{$field} ?? '')) === $normalized) {
                    return (int) ($route->pool_id ?? 0);
                }
            }
        }

        return 0;
    }

    private function normalizeCharterLabel(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? '';
    }

    private function charterPoolErrorMessage(int $code): string
    {
        return match ($code) {
            -1 => 'Pool tidak sesuai dengan akses user.',
            -3 => 'Rute yang dipilih sudah dimapping ke pool lain.',
            default => 'Pilih Perwakilan/Pool atau rute yang sesuai dengan akses user.',
        };
    }

    /**
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
            if (Schema::hasColumn('master_carter', 'tenant_id')) {
                PoolScope::applyTenantScope($existingQuery, 'tenant_id');
            }
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
            // Master Carter is only a reusable preset, so charter submission should still succeed.
        }
    }

    private function normalizePhone(string $value): string
    {
        $trimmed = trim($value);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return $digits !== '' ? $digits : $trimmed;
    }

    private function normalizedCustomerPhoneSql(): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), '.', ''), '/', '')";
    }

    /**
     * @return array<int, string>
     */
    private function customerPhoneSearchVariants(string $value): array
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return [];
        }

        $variants = [$digits];

        if (str_starts_with($digits, '0')) {
            $withoutPrefix = substr($digits, 1);
            if ($withoutPrefix !== '') {
                $variants[] = $withoutPrefix;
                $variants[] = '62'.$withoutPrefix;
            }
        } elseif (str_starts_with($digits, '62')) {
            $withoutPrefix = substr($digits, 2);
            if ($withoutPrefix !== '') {
                $variants[] = $withoutPrefix;
                $variants[] = '0'.$withoutPrefix;
            }
        } elseif (str_starts_with($digits, '8')) {
            $variants[] = '0'.$digits;
            $variants[] = '62'.$digits;
        }

        return array_values(array_unique($variants));
    }

    private function upsertCustomerBagasi(string $nama, string $noHp, ?string $alamat, string $tipe, int $poolId = 0): int
    {
        if ($noHp === '') {
            return 0;
        }

        $tenantId = $this->requireTenantContext();
        $existingQuery = DB::table('customer_bagasi')->where('no_hp', $noHp);
        if (Schema::hasColumn('customer_bagasi', 'tenant_id')) {
            if ($tenantId > 0) {
                $existingQuery->where('tenant_id', $tenantId);
            }
        }
        $existing = $existingQuery->first(['id', 'tipe']);
        if ($existing) {
            $existingTipe = strtolower((string) ($existing->tipe ?? ''));
            $nextTipe = $existingTipe;
            if ($existingTipe !== $tipe && $existingTipe !== 'keduanya') {
                $nextTipe = 'keduanya';
            }
            $updateQuery = DB::table('customer_bagasi')->where('id', (int) $existing->id);
            if (Schema::hasColumn('customer_bagasi', 'tenant_id')) {
                if ($tenantId > 0) {
                    $updateQuery->where('tenant_id', $tenantId);
                }
            }
            $updateQuery->update([
                'nama' => $nama,
                'alamat' => $alamat,
                'tipe' => $nextTipe === '' ? $tipe : $nextTipe,
            ]);
            $this->assignCustomerBagasiPoolIfMissing((int) $existing->id, $poolId);

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

    private function assignCustomerBagasiPoolIfMissing(int $customerId, int $poolId): void
    {
        if ($customerId <= 0 || $poolId <= 0 || ! Schema::hasColumn('customer_bagasi', 'pool_id')) {
            return;
        }

        $tenantId = PoolScope::tenantId();
        if ($tenantId <= 0) {
            return;
        }

        $query = DB::table('customer_bagasi')
            ->where('id', $customerId)
            ->whereNull('pool_id');
        if (Schema::hasColumn('customer_bagasi', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }
        $query->update(['pool_id' => $poolId]);
    }

    private function resolveMappedLuggagePrice(int $routeId, int $serviceId): float
    {
        return 0;
    }

    private function nextLuggageResi(): string
    {
        $tenantId = $this->requireTenantContext();
        $date = now()->format('Ymd');
        $prefix = "BGS-{$date}-";
        $query = DB::table('luggages')
            ->where('kode_resi', 'like', "{$prefix}%")
            ->orderByDesc('id');
        if (Schema::hasColumn('luggages', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }
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

    private function luggageReceivedStatus(): string
    {
        return 'Diterima';
    }

    private function ok(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $data), $status);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
        ], $status);
    }
}
