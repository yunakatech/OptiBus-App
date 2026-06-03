<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActivityLog;
use App\Support\PoolScope;
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
            ->orderBy('name')
            ->get(['id', 'name', 'origin', 'destination', 'duration', 'rental_price', 'bop_price']);

        return $this->ok(['routes' => $routes]);
    }

    public function segments(Request $request): JsonResponse
    {
        $routeName = trim((string) $request->query('route_name', ''));

        $query = DB::table('segments as s')
            ->leftJoin('routes as r', 's.route_id', '=', 'r.id')
            ->select(['s.id', 's.rute', 's.harga']);

        if ($routeName !== '') {
            $query->where('r.name', $routeName);
        }
        PoolScope::applyRouteScope($query, 's.route_id', 's.rute');

        $segments = $query->orderBy('s.rute')->get();
        return $this->ok(['segments' => $segments]);
    }

    public function segmentPrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $query = DB::table('segments as s')
            ->where('s.id', $validated['id']);
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

        $query = DB::table('units')->orderBy('nopol');
        if (Schema::hasColumn('units', 'status')) {
            $query->where('status', 'Aktif');
        }

        $units = $query->get($columns);

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

        $query = DB::table('armadas')->select($columns)->orderBy('nopol');
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

        return $this->ok(['armadas' => $query->get()]);
    }

    public function drivers(): JsonResponse
    {
        $drivers = DB::table('drivers')
            ->orderBy('nama')
            ->get(['id', 'nama', 'phone']);

        return $this->ok(['drivers' => $drivers]);
    }

    public function luggageServices(): JsonResponse
    {
        $services = DB::table('luggage_services')
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->ok(['services' => $services]);
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || mb_strlen($q) < 2) {
            return $this->ok(['customers' => []]);
        }

        $cacheKey = 'ops:customer-search:'.PoolScope::cacheKey().':'.md5(mb_strtolower($q));
        $customers = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($q) {
            $phoneQuery = preg_replace('/\D+/', '', $q) ?? '';
            $qLower = mb_strtolower($q);
            $like = '%'.$qLower.'%';
            $rawLike = '%'.$q.'%';
            $phoneLike = $phoneQuery !== '' ? '%'.$phoneQuery.'%' : '';
            $phoneExact = $phoneQuery;

            $query = DB::table('customers')
                ->select([
                    'name',
                    'phone',
                    'pickup_point',
                    'gmaps',
                    DB::raw('gmaps as address'),
                ]);
            PoolScope::applyCustomerScope($query, 'customers');

            return $query
                ->where(function ($query) use ($like, $rawLike, $phoneQuery, $phoneLike) {
                    $query->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$like])
                        ->orWhere('phone', 'like', $rawLike)
                        ->orWhereRaw("LOWER(COALESCE(pickup_point, '')) LIKE ?", [$like]);

                    if ($phoneQuery !== '') {
                        $query->orWhereRaw("REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', '') LIKE ?", [$phoneLike]);
                    }
                })
                ->orderByRaw(
                    "CASE
                        WHEN REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', '') = ? THEN 0
                        WHEN LOWER(COALESCE(name, '')) = ? THEN 1
                        ELSE 2
                    END",
                    [$phoneExact, $qLower]
                )
                ->orderBy('name')
                ->limit(20)
                ->get();
        });

        return $this->ok(['customers' => $customers]);
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

        if (Schema::hasColumn('charters', 'status')) {
            $payload['status'] = 'active';
        }

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
        $pengirimId = $this->upsertCustomerBagasi($senderName, $senderPhone, $senderAddress, 'pengirim');
        $penerimaId = $this->upsertCustomerBagasi($receiverName, $receiverPhone, $receiverAddress, 'penerima');
        $mappedPrice = $this->resolveMappedLuggagePrice($routeId, $serviceId);
        $inputPrice = (float) ($data['price'] ?? 0);
        $resolvedPrice = $inputPrice > 0 ? $inputPrice : $mappedPrice;
        $routeName = $routeId > 0 ? (string) (DB::table('routes')->where('id', $routeId)->value('name') ?? '') : '';

        $id = DB::table('luggages')->insertGetId([
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
            'unit_id' => isset($data['unit_id']) ? (int) $data['unit_id'] : null,
            'pengirim_id' => $pengirimId > 0 ? $pengirimId : null,
            'penerima_id' => $penerimaId > 0 ? $penerimaId : null,
            'quantity' => max(1, (int) ($data['quantity'] ?? 1)),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'price' => $resolvedPrice,
            'status' => $this->luggageReceivedStatus(),
            'payment_status' => $this->nullableString($data['payment_status'] ?? null) ?? 'Belum Bayar',
            'kode_resi' => $this->nextLuggageResi(),
            'created_at' => now(),
        ]);

        $resi = (string) (DB::table('luggages')->where('id', $id)->value('kode_resi') ?? '');
        if ($resi !== '' && DB::getSchemaBuilder()->hasTable('bagasi_logs')) {
            DB::table('bagasi_logs')->insert([
                'kode_resi' => $resi,
                'status' => $this->luggageReceivedStatus(),
                'notes' => $this->luggageReceivedStatus(),
                'created_by_username' => auth()->user()?->email ?? auth()->user()?->name ?? 'system',
                'created_at' => now(),
            ]);
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

        if ($requestedPoolId > 0) {
            return $isAllowed($requestedPoolId) ? $requestedPoolId : -1;
        }

        foreach ([$pickupPoint, $dropPoint] as $label) {
            $poolId = $this->poolIdForCharterLabel($label);

            if ($poolId > 0) {
                return $isAllowed($poolId) ? $poolId : -1;
            }
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
            default => 'Pilih Perwakilan/Pool atau rute yang sesuai dengan akses user.',
        };
    }

    /**
     * @param array<string, mixed> $payload
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

        try {
            $existingId = (int) (DB::table('master_carter')
                ->whereRaw("UPPER(COALESCE(origin, '')) = ?", [strtoupper($origin)])
                ->whereRaw("UPPER(COALESCE(destination, '')) = ?", [strtoupper($destination)])
                ->whereRaw("UPPER(COALESCE(duration, '')) = ?", [strtoupper($duration)])
                ->value('id') ?? 0);

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

    private function upsertCustomerBagasi(string $nama, string $noHp, ?string $alamat, string $tipe): int
    {
        if ($noHp === '') {
            return 0;
        }

        $existing = DB::table('customer_bagasi')->where('no_hp', $noHp)->first(['id', 'tipe']);
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

    private function nextLuggageResi(): string
    {
        $date = now()->format('Ymd');
        $prefix = "BGS-{$date}-";
        $last = (string) (DB::table('luggages')
            ->where('kode_resi', 'like', "{$prefix}%")
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
