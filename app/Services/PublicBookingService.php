<?php

namespace App\Services;

use App\Support\ActivityLog;
use App\Support\BookingCode;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use App\Support\SchemaCache;
use App\Support\SegmentName;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PublicBookingService
{
    private const HOLD_MINUTES = 15;

    /** @return array<string, mixed> */
    public function settings(?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId <= 0 || ! SchemaCache::hasTable('tenants')) {
            return [
                'tenant' => null,
                'enabled' => false,
                'entitled' => false,
                'url' => null,
            ];
        }

        $tenant = DB::table('tenants')->where('id', $tenantId)->first([
            'id', 'name', 'slug', 'phone', 'logo_url', 'status',
            SchemaCache::hasColumn('tenants', 'public_booking_enabled')
                ? 'public_booking_enabled'
                : DB::raw('false as public_booking_enabled'),
            SchemaCache::hasColumn('tenants', 'public_booking_whatsapp')
                ? 'public_booking_whatsapp'
                : DB::raw('NULL as public_booking_whatsapp'),
        ]);
        $entitled = FeatureGate::can('saas.online_booking', 0, $userId);

        return [
            'tenant' => $tenant ? [
                'id' => (int) $tenant->id,
                'name' => (string) $tenant->name,
                'slug' => (string) $tenant->slug,
                'phone' => (string) ($tenant->phone ?? ''),
                'whatsapp' => (string) ($tenant->public_booking_whatsapp ?? ''),
                'logo_url' => $tenant->logo_url ?? null,
                'status' => (string) ($tenant->status ?? 'active'),
            ] : null,
            'enabled' => (bool) ($tenant->public_booking_enabled ?? false),
            'entitled' => $entitled,
            'url' => $tenant ? route('public.booking.show', ['tenantSlug' => $tenant->slug], absolute: true) : null,
        ];
    }

    public function updateSettings(bool $enabled, ?string $whatsapp = null, ?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId <= 0) {
            throw new RuntimeException('Tenant aktif belum dipilih.');
        }
        if ($enabled && ! FeatureGate::can('saas.online_booking', 0, $userId)) {
            throw new RuntimeException('Fitur booking online belum tersedia pada paket Anda.');
        }
        if (! SchemaCache::hasColumn('tenants', 'public_booking_enabled')) {
            throw new RuntimeException('Schema booking publik belum dimigrasikan.');
        }
        if (! SchemaCache::hasColumn('tenants', 'public_booking_whatsapp')) {
            throw new RuntimeException('Kolom WhatsApp booking belum tersedia. Jalankan php artisan migrate --force terlebih dahulu.');
        }

        $payload = [
            'public_booking_enabled' => $enabled,
            'updated_at' => now(),
        ];
        if ($whatsapp !== null) {
            $payload['public_booking_whatsapp'] = $this->normalizeWhatsapp($whatsapp);
        }

        DB::table('tenants')->where('id', $tenantId)->update($payload);

        return $this->settings($userId);
    }

    /**
     * Store the tenant's public booking logo in Supabase Storage.
     *
     * The bucket must be public because this URL is rendered for guests.
     */
    public function uploadLogo(UploadedFile $file, ?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId <= 0) {
            throw new RuntimeException('Tenant aktif belum dipilih.');
        }
        if (! SchemaCache::hasColumn('tenants', 'logo_url')) {
            throw new RuntimeException('Kolom logo tenant belum tersedia. Jalankan migrasi database terlebih dahulu.');
        }
        if (! $this->hasSupabaseStorage()) {
            throw new RuntimeException('Supabase Storage belum dikonfigurasi di server.');
        }

        $tenant = DB::table('tenants')->where('id', $tenantId)->first(['id', 'slug', 'logo_url']);
        if (! $tenant) {
            throw new RuntimeException('Tenant aktif tidak ditemukan.');
        }

        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';
        $path = 'public-booking/logos/'.Str::slug((string) $tenant->slug).'-'.Str::uuid()->toString().'.'.$extension;
        $storedPath = $file->storeAs('', $path, 'supabase');

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException('Upload logo ke Supabase Storage mengembalikan path kosong.');
        }

        $logoUrl = $this->supabaseStorageUrl($storedPath);
        DB::table('tenants')->where('id', $tenantId)->update([
            'logo_url' => $logoUrl,
            'updated_at' => now(),
        ]);

        $previousPath = $this->supabaseStoragePath($tenant->logo_url ?? null);
        if ($previousPath !== null && $previousPath !== $storedPath) {
            Storage::disk('supabase')->delete($previousPath);
        }

        return $this->settings($userId);
    }

    private function hasSupabaseStorage(): bool
    {
        return trim((string) config('filesystems.disks.supabase.key', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.secret', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.bucket', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.endpoint', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.url', '')) !== '';
    }

    private function supabaseStorageUrl(string $path): string
    {
        return rtrim((string) config('filesystems.disks.supabase.url'), '/').'/'.ltrim($path, '/');
    }

    private function supabaseStoragePath(?string $value): ?string
    {
        $value = trim((string) $value);
        $bucket = trim((string) config('filesystems.disks.supabase.bucket', ''));

        if ($value === '' || $bucket === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $parsedPath = parse_url($value, PHP_URL_PATH);
            if (! is_string($parsedPath)) {
                return null;
            }
            $value = $parsedPath;
        }

        $marker = '/storage/v1/object/public/'.$bucket.'/';
        if (Str::contains($value, $marker)) {
            return Str::after($value, $marker);
        }

        if (Str::startsWith($value, 'public-booking/logos/')) {
            return $value;
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function pageData(string $tenantSlug): array
    {
        $tenant = $this->publicTenant($tenantSlug, true);
        if (! $tenant) {
            abort(404);
        }

        return [
            'tenant' => $this->tenantPayload($tenant),
            'date_min' => now()->toDateString(),
            'date_max' => now()->addDays(30)->toDateString(),
            'payment_methods' => ['Belum Lunas', 'Transfer', 'Cash', 'QRIS'],
        ];
    }

    /** @return array<string, mixed> */
    public function availability(string $tenantSlug, string $date, int $routeId = 0, int $segmentId = 0): array
    {
        $tenant = $this->publicTenant($tenantSlug, true);
        if (! $tenant) {
            abort(404);
        }

        $dateObject = Carbon::createFromFormat('Y-m-d', $date);
        if (! $dateObject || $dateObject->isBefore(now()->startOfDay()) || $dateObject->isAfter(now()->addDays(30)->endOfDay())) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal booking harus antara hari ini dan 30 hari ke depan.']);
        }

        $tenantId = (int) $tenant->id;
        $routes = $this->publicRoutes($tenantId);
        $segments = $this->publicSegments($tenantId, $routes);
        $selectedSegment = $segmentId > 0
            ? collect($segments)->firstWhere('id', $segmentId)
            : null;

        if ($segmentId > 0 && ! $selectedSegment) {
            abort(404);
        }

        $selectedRouteId = $selectedSegment
            ? (int) $selectedSegment['route_id']
            : $routeId;
        $selectedRoute = $routeId > 0
            ? collect($routes)->firstWhere('id', $selectedRouteId)
            : null;

        if ($selectedSegment) {
            $selectedRoute = collect($routes)->firstWhere('id', $selectedRouteId);
        }

        if (($routeId > 0 || $segmentId > 0) && ! $selectedRoute) {
            abort(404);
        }

        return [
            'tenant' => $this->tenantPayload($tenant),
            'routes' => $routes,
            'segments' => $segments,
            'selected_segment_id' => $selectedSegment ? $segmentId : null,
            'selected_route_id' => $selectedRoute ? (int) $selectedRoute['id'] : null,
            'schedules' => $selectedRoute ? $this->publicSchedules($tenantId, $selectedRoute, $date, $selectedSegment) : [],
        ];
    }

    /** @return array<string, mixed> */
    public function createRequest(string $tenantSlug, array $data): array
    {
        $tenant = $this->publicTenant($tenantSlug, true);
        if (! $tenant) {
            abort(404);
        }

        if (trim((string) ($data['website'] ?? '')) !== '') {
            throw ValidationException::withMessages(['website' => 'Request tidak valid.']);
        }

        $date = (string) $data['tanggal'];
        $dateObject = Carbon::createFromFormat('Y-m-d', $date);
        if (! $dateObject || $dateObject->isBefore(now()->startOfDay()) || $dateObject->isAfter(now()->addDays(30)->endOfDay())) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal booking harus antara hari ini dan 30 hari ke depan.']);
        }

        $tenantId = (int) $tenant->id;
        $routeId = (int) ($data['route_id'] ?? 0);
        $segmentId = (int) ($data['segment_id'] ?? 0);
        $scheduleId = (int) ($data['schedule_id'] ?? 0);
        $unit = max(1, (int) ($data['unit'] ?? 1));
        $routes = $this->publicRoutes($tenantId);
        $segments = $this->publicSegments($tenantId, $routes);
        $segment = $segmentId > 0 ? collect($segments)->firstWhere('id', $segmentId) : null;
        if ($segmentId > 0 && ! $segment) {
            throw ValidationException::withMessages(['segment_id' => 'Tujuan segment tidak tersedia.']);
        }
        if ($segment) {
            $routeId = (int) $segment['route_id'];
        }
        $route = collect($routes)->firstWhere('id', $routeId);
        if (! $route) {
            throw ValidationException::withMessages(['route_id' => 'Rute tidak tersedia.']);
        }
        $schedule = $this->findPublicSchedule($tenantId, $route, $scheduleId, $dateObject->dayOfWeek, $segment);
        if (! $schedule) {
            throw ValidationException::withMessages(['schedule_id' => 'Jadwal tidak tersedia.']);
        }

        $jam = substr((string) $schedule->jam, 0, 5);
        $seatTokens = $this->seatTokens($schedule);
        $passengers = is_array($data['passengers'] ?? null) ? $data['passengers'] : [];
        $selectedSeats = array_values(array_unique(array_map(
            fn ($passenger): string => $this->normalizeSeat((string) ($passenger['seat'] ?? '')),
            $passengers,
        )));
        if ($selectedSeats === [] || count($selectedSeats) !== count($passengers)) {
            throw ValidationException::withMessages(['passengers' => 'Pilih kursi yang valid dan tidak boleh sama.']);
        }
        foreach ($selectedSeats as $seat) {
            if (! in_array($seat, $seatTokens, true)) {
                throw ValidationException::withMessages(['passengers' => "Kursi {$seat} tidak tersedia pada unit ini."]);
            }
        }

        $poolId = (int) ($route['pool_id'] ?? 0);
        $price = $segment ? (float) ($segment['price'] ?? 0) : 0;
        $pickupTime = $segment
            ? (string) ($this->segmentSchedulePayload($segment, $schedule)['pickup_time'] ?? '')
            : null;
        $requestId = 0;
        $requestCode = '';
        $holdExpiresAt = now()->addMinutes(self::HOLD_MINUTES);
        DB::transaction(function () use (&$requestId, &$requestCode, $tenantId, $route, $segment, $schedule, $date, $unit, $price, $pickupTime, $selectedSeats, $passengers, $data, $poolId, $holdExpiresAt): void {
            $lockedSchedule = DB::table('schedules')->where('id', (int) $schedule->id)->lockForUpdate()->first();
            if (! $lockedSchedule) {
                throw ValidationException::withMessages(['schedule_id' => 'Jadwal tidak tersedia.']);
            }

            $occupied = $this->occupiedSeatValues($tenantId, (int) $route['id'], (string) $route['name'], $lockedSchedule, $date, $unit, true);

            $conflicts = array_values(array_intersect($selectedSeats, $occupied));
            if ($conflicts !== []) {
                throw ValidationException::withMessages(['passengers' => 'Kursi baru saja dipilih pelanggan lain: '.implode(', ', $conflicts).'.']);
            }

            $requestCode = $this->newRequestCode();
            $requestId = (int) DB::table('public_booking_requests')->insertGetId([
                'request_code' => $requestCode,
                'tenant_id' => $tenantId,
                'pool_id' => $poolId,
                'route_id' => (int) $route['id'],
                'segment_id' => $segment ? (int) $segment['id'] : null,
                'schedule_id' => (int) $schedule->id,
                'tanggal' => $date,
                'jam' => $schedule->jam,
                'unit' => $unit,
                'price' => $price,
                'pickup_time' => $pickupTime,
                'contact_name' => trim((string) $data['contact_name']),
                'phone' => trim((string) $data['phone']),
                'pickup_address' => trim((string) $data['pickup_address']),
                'payment_method' => trim((string) $data['payment_method']),
                'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
                'status' => 'pending',
                'hold_expires_at' => $holdExpiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $now = now();
            DB::table('public_booking_request_seats')->insert(array_map(
                fn (array $passenger): array => [
                    'request_id' => $requestId,
                    'seat' => $this->normalizeSeat((string) ($passenger['seat'] ?? '')),
                    'passenger_name' => trim((string) ($passenger['passenger_name'] ?? '')),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $passengers,
            ));
        }, 3);

        $whatsappUrl = $this->whatsappUrl($tenant, $requestCode, $route, $schedule, $date, $unit, $selectedSeats, (string) $data['contact_name']);

        return [
            'request_id' => $requestId,
            'request_code' => $requestCode,
            'status' => 'pending',
            'hold_expires_at' => $holdExpiresAt->toIso8601String(),
            'whatsapp_url' => $whatsappUrl,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function adminRequests(?int $userId = null, string $status = 'pending'): array
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $tenantId = PoolScope::tenantId($userId);
        $poolIds = PoolScope::accessiblePoolIds($userId, false);
        if ($tenantId <= 0 || $poolIds === []) {
            return [];
        }

        DB::table('public_booking_requests')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->where('hold_expires_at', '<=', now())
            ->update(['status' => 'expired', 'updated_at' => now()]);

        $query = DB::table('public_booking_requests as r')
            ->join('routes as route', 'r.route_id', '=', 'route.id')
            ->join('pools as pool', 'r.pool_id', '=', 'pool.id')
            ->where('r.tenant_id', $tenantId)
            ->whereIn('r.pool_id', $poolIds)
            ->when($status !== '', fn (Builder $builder) => $builder->where('r.status', $status))
            ->orderByDesc('r.created_at');

        if (SchemaCache::hasTable('segments') && SchemaCache::hasColumn('public_booking_requests', 'segment_id')) {
            $query->leftJoin('segments as segment', function (JoinClause $join) {
                $join->on('r.segment_id', '=', 'segment.id');
                if (SchemaCache::hasColumn('segments', 'tenant_id')) {
                    $join->whereColumn('segment.tenant_id', 'r.tenant_id');
                }
            });
        }

        $select = [
            'r.id', 'r.request_code', 'r.route_id', 'r.schedule_id', 'r.tanggal', 'r.jam', 'r.unit',
            'r.contact_name', 'r.phone', 'r.pickup_address', 'r.payment_method', 'r.notes', 'r.status',
            'r.hold_expires_at', 'r.rejection_reason', 'r.created_at', 'route.name as route_name', 'pool.name as pool_name',
        ];
        $select[] = SchemaCache::hasColumn('public_booking_requests', 'pickup_time')
            ? 'r.pickup_time'
            : DB::raw('NULL as pickup_time');
        if (SchemaCache::hasTable('segments') && SchemaCache::hasColumn('public_booking_requests', 'segment_id')) {
            $select = array_merge($select, [
                'r.segment_id',
                'r.price',
                'segment.origin as segment_origin',
                'segment.destination as segment_destination',
                'segment.rute as segment_route_name',
                SchemaCache::hasColumn('segments', 'jam')
                    ? 'segment.jam as segment_jam'
                    : DB::raw('NULL as segment_jam'),
                SchemaCache::hasColumn('segments', 'jam_pickups')
                    ? 'segment.jam_pickups as segment_jam_pickups'
                    : DB::raw('NULL as segment_jam_pickups'),
            ]);
        }

        $rows = $query->limit(100)->get($select);
        $requestIds = $rows->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $seatMap = $requestIds === [] ? [] : DB::table('public_booking_request_seats')->whereIn('request_id', $requestIds)->get()->groupBy('request_id');

        return $rows->map(fn ($row): array => [
            'id' => (int) $row->id,
            'request_code' => (string) $row->request_code,
            'route_name' => (string) $row->route_name,
            'pool_name' => (string) $row->pool_name,
            'segment_id' => (int) ($row->segment_id ?? 0),
            'segment_name' => SegmentName::display(
                $row->segment_origin ?? null,
                $row->segment_destination ?? null,
                $row->segment_route_name ?? '',
            ),
            'segment_pickup_times' => SegmentName::jamList(
                $row->segment_jam_pickups ?? null,
                $row->segment_jam ?? null,
            ),
            'price' => (float) ($row->price ?? 0),
            'pickup_time' => (string) ($row->pickup_time ?? ''),
            'schedule_id' => (int) $row->schedule_id,
            'tanggal' => (string) $row->tanggal,
            'jam' => substr((string) $row->jam, 0, 5),
            'unit' => (int) $row->unit,
            'contact_name' => (string) $row->contact_name,
            'phone' => (string) $row->phone,
            'pickup_address' => (string) $row->pickup_address,
            'payment_method' => (string) $row->payment_method,
            'notes' => (string) ($row->notes ?? ''),
            'status' => (string) $row->status,
            'hold_expires_at' => $row->hold_expires_at,
            'rejection_reason' => (string) ($row->rejection_reason ?? ''),
            'created_at' => $row->created_at,
            'seats' => ($seatMap[(int) $row->id] ?? collect())->map(fn ($seat): array => [
                'seat' => (string) $seat->seat,
                'passenger_name' => (string) $seat->passenger_name,
            ])->values()->all(),
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function approve(int $requestId, ?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);

        return DB::transaction(function () use ($requestId, $userId): array {
            $request = DB::table('public_booking_requests')->where('id', $requestId)->lockForUpdate()->first();
            $this->assertAdminRequestAccess($request, $userId);

            if ((string) $request->status !== 'pending') {
                throw new RuntimeException('Request ini sudah diproses.');
            }
            if (Carbon::parse((string) $request->hold_expires_at)->isPast()) {
                DB::table('public_booking_requests')->where('id', $requestId)->update(['status' => 'expired', 'updated_at' => now()]);
                throw new RuntimeException('Hold kursi sudah kedaluwarsa.');
            }

            $route = DB::table('routes')
                ->where('id', (int) $request->route_id)
                ->where('tenant_id', (int) $request->tenant_id)
                ->first(['id', 'name']);
            $schedule = DB::table('schedules')
                ->where('id', (int) $request->schedule_id)
                ->where('tenant_id', (int) $request->tenant_id)
                ->first();
            if (! $route || ! $schedule) {
                throw new RuntimeException('Rute atau jadwal request tidak ditemukan.');
            }
            if (! DB::table('pool_route')->where('pool_id', (int) $request->pool_id)->where('route_id', (int) $request->route_id)->exists()) {
                throw new RuntimeException('Rute tidak terdaftar pada pool request.');
            }
            $segment = null;
            if ((int) ($request->segment_id ?? 0) > 0) {
                $segmentQuery = DB::table('segments')
                    ->where('id', (int) $request->segment_id)
                    ->when(SchemaCache::hasColumn('segments', 'tenant_id'), fn (Builder $query) => $query->where('tenant_id', (int) $request->tenant_id));
                if (SchemaCache::hasColumn('segments', 'route_id')) {
                    $segmentQuery->where(function (Builder $query) use ($request, $route): void {
                        $query->where('route_id', (int) $request->route_id)
                            ->orWhere(function (Builder $legacy) use ($route): void {
                                $legacy->where('route_id', 0)->where('rute', (string) $route->name);
                            });
                    });
                } else {
                    $segmentQuery->where('rute', (string) $route->name);
                }
                $segment = $segmentQuery->first();
                if (! $segment) {
                    throw new RuntimeException('Segment request tidak lagi tersedia pada rute ini.');
                }
            }
            $seats = DB::table('public_booking_request_seats')->where('request_id', $requestId)->get();
            $schedule = DB::table('schedules')->where('id', (int) $request->schedule_id)->lockForUpdate()->first();
            if (! $schedule) {
                throw new RuntimeException('Jadwal request tidak ditemukan.');
            }
            $occupied = $this->occupiedSeatValues((int) $request->tenant_id, (int) $request->route_id, (string) $route->name, $schedule, (string) $request->tanggal, (int) $request->unit, true, $requestId);
            $conflicts = array_values(array_intersect($seats->pluck('seat')->map(fn ($seat): string => $this->normalizeSeat((string) $seat))->all(), $occupied));
            if ($conflicts !== []) {
                DB::table('public_booking_requests')->where('id', $requestId)->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Kursi sudah tidak tersedia: '.implode(', ', $conflicts),
                    'updated_at' => now(),
                ]);

                return [
                    'request_id' => $requestId,
                    'request_code' => (string) $request->request_code,
                    'booking_ids' => [],
                    'status' => 'rejected',
                    'rejection_reason' => 'Kursi sudah tidak tersedia: '.implode(', ', $conflicts),
                ];
            }

            $bookingIds = [];
            foreach ($seats as $seat) {
                $payload = [
                    'rute' => (string) $route->name,
                    'tanggal' => $request->tanggal,
                    'jam' => $request->jam,
                    'unit' => (int) $request->unit,
                    'seat' => (string) $seat->seat,
                    'name' => (string) $seat->passenger_name,
                    'phone' => (string) $request->phone,
                    'pickup_point' => (string) $request->pickup_address,
                    'pembayaran' => (string) $request->payment_method,
                    'status' => 'active',
                    'price' => (float) ($request->price ?? 0),
                    'discount' => 0,
                    'created_by_user_id' => $userId,
                    'created_by_username' => auth()->user()?->name ?: auth()->user()?->email ?: 'Admin Pool',
                    'public_booking_request_id' => $requestId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (SchemaCache::hasColumn('bookings', 'route_id')) {
                    $payload['route_id'] = (int) $request->route_id;
                }
                if (SchemaCache::hasColumn('bookings', 'segment_id')) {
                    $payload['segment_id'] = (int) ($request->segment_id ?? 0) > 0
                        ? (int) $request->segment_id
                        : null;
                }
                if (SchemaCache::hasColumn('bookings', 'tenant_id')) {
                    $payload['tenant_id'] = (int) $request->tenant_id;
                }
                if (SchemaCache::hasColumn('bookings', 'departure_code')) {
                    $payload['departure_code'] = BookingCode::departureCode((string) $request->tanggal, substr((string) $request->jam, 0, 5), (int) $request->unit, (string) $route->name);
                }
                $bookingId = (int) DB::table('bookings')->insertGetId($payload);
                if (SchemaCache::hasColumn('bookings', 'ticket_code')) {
                    DB::table('bookings')->where('id', $bookingId)->update([
                        'ticket_code' => BookingCode::ticketCode($bookingId, (string) $request->tanggal),
                        'updated_at' => now(),
                    ]);
                }
                $bookingIds[] = $bookingId;

                $this->upsertPublicCustomer(
                    (int) $request->tenant_id,
                    (int) $request->pool_id,
                    (string) $seat->passenger_name,
                    (string) $request->phone,
                    (string) $request->pickup_address,
                );
            }

            DB::table('public_booking_requests')->where('id', $requestId)->update([
                'status' => 'approved',
                'approved_by_user_id' => $userId,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
            ActivityLog::write('BOOKING', 'Booking publik '.$request->request_code.' disetujui', 'Request '.$request->request_code.' menjadi booking resmi.', auth()->user()?->name ?: auth()->user()?->email, ['tenant_id' => (int) $request->tenant_id, 'booking_ids' => $bookingIds, 'public_booking_request_id' => $requestId]);

            return ['request_id' => $requestId, 'request_code' => (string) $request->request_code, 'booking_ids' => $bookingIds, 'status' => 'approved'];
        }, 3);
    }

    public function reject(int $requestId, string $reason, ?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);

        return DB::transaction(function () use ($requestId, $reason, $userId): array {
            $request = DB::table('public_booking_requests')->where('id', $requestId)->lockForUpdate()->first();
            $this->assertAdminRequestAccess($request, $userId);
            if ((string) $request->status !== 'pending') {
                throw new RuntimeException('Request ini sudah diproses.');
            }
            DB::table('public_booking_requests')->where('id', $requestId)->update([
                'status' => 'rejected',
                'rejection_reason' => trim($reason),
                'updated_at' => now(),
            ]);

            return ['request_id' => $requestId, 'request_code' => (string) $request->request_code, 'status' => 'rejected'];
        }, 3);
    }

    private function publicTenant(string $slug, bool $requireEnabled): ?object
    {
        if (! SchemaCache::hasTable('tenants')) {
            return null;
        }
        $tenant = DB::table('tenants')->where('slug', trim($slug))->first();
        if (! $tenant || (string) ($tenant->status ?? '') !== 'active') {
            return null;
        }
        if ($requireEnabled && (! SchemaCache::hasColumn('tenants', 'public_booking_enabled') || ! (bool) ($tenant->public_booking_enabled ?? false))) {
            return null;
        }
        if (! $this->onlineBookingEntitled((int) $tenant->id)) {
            return null;
        }

        return $tenant;
    }

    private function onlineBookingEntitled(int $tenantId): bool
    {
        if (! FeatureGate::enabled()) {
            return true;
        }
        $subscription = DB::table('subscriptions as s')
            ->leftJoin('plans as p', 's.plan_id', '=', 'p.id')
            ->where('s.tenant_id', $tenantId)
            ->whereIn('s.status', ['trial', 'active'])
            ->orderByRaw("CASE s.status WHEN 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('s.created_at')
            ->select(['s.*', 'p.slug as plan_slug'])
            ->first();
        if (! $subscription) {
            return false;
        }
        $expiry = $subscription->status === 'trial' ? ($subscription->trial_ends_at ?? $subscription->ends_at) : $subscription->ends_at;
        if ($expiry && Carbon::parse((string) $expiry)->endOfDay()->isPast()) {
            return false;
        }
        if (FeatureGate::isPrivatePricing($subscription)) {
            if (! SchemaCache::hasTable('subscription_feature_overrides')) {
                return true;
            }

            $gateId = (int) DB::table('feature_gates')->where('feature_key', 'saas.online_booking')->value('id');
            if ($gateId <= 0) {
                return true;
            }

            $override = DB::table('subscription_feature_overrides')
                ->where('subscription_id', (int) $subscription->id)
                ->where('feature_gate_id', $gateId)
                ->first(['max_value']);

            return ! $override || $override->max_value === null || (int) $override->max_value > 0;
        }
        $gateId = (int) DB::table('feature_gates')->where('feature_key', 'saas.online_booking')->value('id');
        if ($gateId <= 0) {
            return false;
        }

        return DB::table('plan_feature')->where('plan_id', (int) $subscription->plan_id)->where('feature_gate_id', $gateId)->where(function (Builder $query): void {
            $query->whereNull('max_value')->orWhere('max_value', '>', 0);
        })->exists();
    }

    /** @return array<string, mixed> */
    private function tenantPayload(object $tenant): array
    {
        return [
            'name' => (string) $tenant->name,
            'slug' => (string) $tenant->slug,
            'phone' => (string) ($tenant->phone ?? ''),
            'logo_url' => $tenant->logo_url ?? null,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function publicRoutes(int $tenantId): array
    {
        if (! SchemaCache::hasTable('pool_route') || ! SchemaCache::hasTable('pools')) {
            return [];
        }

        return DB::table('routes as r')
            ->join('pool_route as pr', 'pr.route_id', '=', 'r.id')
            ->join('pools as p', 'p.id', '=', 'pr.pool_id')
            ->where('p.status', 'active')
            ->where('p.tenant_id', $tenantId)
            ->where('r.tenant_id', $tenantId)
            ->orderBy('r.name')
            ->get(['r.id', 'r.name', 'r.origin', 'r.destination', 'p.id as pool_id', 'p.name as pool_name'])
            ->map(fn ($route): array => [
                'id' => (int) $route->id,
                'name' => (string) $route->name,
                'origin' => (string) ($route->origin ?? ''),
                'destination' => (string) ($route->destination ?? ''),
                'pool_id' => (int) $route->pool_id,
                'pool_name' => (string) $route->pool_name,
            ])->unique('id')->values()->all();
    }

    /** @param array<int, array<string, mixed>> $routes */
    private function publicSegments(int $tenantId, array $routes): array
    {
        if (! SchemaCache::hasTable('segments')) {
            return [];
        }

        $hasRouteId = SchemaCache::hasColumn('segments', 'route_id');
        $hasTenantId = SchemaCache::hasColumn('segments', 'tenant_id');
        $hasJamPickups = SchemaCache::hasColumn('segments', 'jam_pickups');
        $hasPublicBookingEnabled = SchemaCache::hasColumn('segments', 'public_booking_enabled');
        $segments = collect();

        foreach ($routes as $route) {
            $query = DB::table('segments')->where(function (Builder $builder) use ($route, $hasRouteId): void {
                if ($hasRouteId) {
                    $builder->where('route_id', (int) $route['id'])
                        ->orWhere(function (Builder $legacy) use ($route): void {
                            $legacy->where('route_id', 0)->where('rute', (string) $route['name']);
                        });
                } else {
                    $builder->where('rute', (string) $route['name']);
                }
            });
            if ($hasTenantId) {
                $query->where('tenant_id', $tenantId);
            }
            if ($hasPublicBookingEnabled) {
                $query->where('public_booking_enabled', true);
            }

            $select = ['id', 'origin', 'destination', 'harga'];
            if (SchemaCache::hasColumn('segments', 'rute')) {
                $select[] = 'rute';
            }
            if (SchemaCache::hasColumn('segments', 'jam')) {
                $select[] = 'jam';
            }
            if ($hasJamPickups) {
                $select[] = 'jam_pickups';
            }

            foreach ($query->orderBy('origin')->get($select) as $segment) {
                $pickupTimes = SegmentName::jamList(
                    $hasJamPickups ? ($segment->jam_pickups ?? null) : null,
                    $segment->jam ?? null,
                );
                $segments->push([
                    'id' => (int) $segment->id,
                    'route_id' => (int) $route['id'],
                    'label' => SegmentName::display(
                        $segment->origin ?? null,
                        $segment->destination ?? null,
                        $segment->rute ?? $route['name'],
                    ),
                    'origin' => (string) ($segment->origin ?? ''),
                    'destination' => (string) ($segment->destination ?? ''),
                    'pickup_times' => $pickupTimes,
                    'price' => (float) ($segment->harga ?? 0),
                    'pool_id' => (int) $route['pool_id'],
                    'pool_name' => (string) $route['pool_name'],
                    'route_name' => (string) $route['name'],
                ]);
            }
        }

        return $segments->unique('id')->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function publicSchedules(int $tenantId, array $route, string $date, ?array $segment = null): array
    {
        $query = DB::table('schedules as s')->where('s.tenant_id', $tenantId)->where('s.dow', Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek);
        $this->scheduleRouteWhere($query, $route);
        $rows = $query->orderBy('s.jam')->get();

        return $rows
            ->filter(fn ($schedule): bool => ! $segment || $this->scheduleSupportsSegment($schedule, $segment))
            ->flatMap(fn ($schedule): array => $this->schedulePayload($tenantId, $route, $schedule, $date, $segment))
            ->values()->all();
    }

    private function findPublicSchedule(int $tenantId, array $route, int $scheduleId, int $dayOfWeek, ?array $segment = null): ?object
    {
        $query = DB::table('schedules as s')->where('s.id', $scheduleId)->where('s.tenant_id', $tenantId)->where('s.dow', $dayOfWeek);
        $this->scheduleRouteWhere($query, $route);

        $schedule = $query->first();

        return $schedule && (! $segment || $this->scheduleSupportsSegment($schedule, $segment))
            ? $schedule
            : null;
    }

    private function scheduleRouteWhere(Builder $query, array $route): void
    {
        if (SchemaCache::hasColumn('schedules', 'route_id')) {
            $query->where(function (Builder $builder) use ($route): void {
                $builder->where('s.route_id', (int) $route['id'])->orWhere(function (Builder $legacy) use ($route): void {
                    $legacy->whereNull('s.route_id')->where('s.rute', (string) $route['name']);
                });
            });
        } else {
            $query->where('s.rute', (string) $route['name']);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function schedulePayload(int $tenantId, array $route, object $schedule, string $date, ?array $segment = null): array
    {
        $seats = $this->seatTokens($schedule);
        $units = max(1, (int) ($schedule->units ?? 1));
        $unitLabels = $this->scheduleUnitLabels($tenantId, (int) $schedule->id);
        $payload = [];
        for ($unit = 1; $unit <= $units; $unit++) {
            $booked = $this->bookingSeatsQuery($tenantId, (int) $route['id'], (string) $route['name'], $schedule, $date, $unit)->get()->map(fn ($row): string => $this->normalizeSeat((string) $row->seat))->unique()->all();
            $held = $this->heldSeatsQuery($tenantId, (int) $route['id'], $schedule, $date, $unit)->get()->map(fn ($row): string => $this->normalizeSeat((string) $row->seat))->unique()->all();
            $layout = $this->scheduleLayout($schedule);
            $unitLabel = trim((string) ($unitLabels[$unit] ?? ''));
            if ($unitLabel === '' && $unit === 1) {
                $unitLabel = trim((string) ($schedule->unit_label ?? ''));
            }
            if ($unitLabel === '') {
                $unitLabel = "Unit {$unit}";
            }
            $payload[] = [
                'id' => (int) $schedule->id,
                'jam' => substr((string) $schedule->jam, 0, 5),
                'unit' => $unit,
                'unit_label' => $unitLabel,
                'layout' => $layout,
                'seats' => array_map(fn (string $seat): array => ['code' => $seat, 'status' => in_array($seat, $booked, true) ? 'booked' : (in_array($seat, $held, true) ? 'held' : 'available')], $seats),
                'total_seats' => count($seats),
                'segment' => $segment ? $this->segmentSchedulePayload($segment, $schedule) : null,
            ];
        }

        return $payload;
    }

    /** @return array<int, string> */
    private function scheduleUnitLabels(int $tenantId, int $scheduleId): array
    {
        if ($scheduleId <= 0 || ! SchemaCache::hasTable('schedule_units')) {
            return [];
        }

        $query = DB::table('schedule_units')
            ->where('schedule_id', $scheduleId);
        if (SchemaCache::hasColumn('schedule_units', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get(['unit_no', 'label'])
            ->mapWithKeys(fn ($row): array => [
                (int) $row->unit_no => trim((string) ($row->label ?? '')),
            ])
            ->all();
    }

    private function scheduleSupportsSegment(object $schedule, array $segment): bool
    {
        $segmentId = (int) ($segment['id'] ?? 0);
        if ($segmentId <= 0) {
            return false;
        }

        if (SchemaCache::hasTable('schedule_segment')) {
            $pivot = DB::table('schedule_segment')
                ->where('schedule_id', (int) $schedule->id)
                ->where('segment_id', $segmentId)
                ->first(['jam_pickup']);
            if ($pivot) {
                return true;
            }

            if (DB::table('schedule_segment')->where('schedule_id', (int) $schedule->id)->exists()) {
                return false;
            }
        }

        return in_array(
            substr((string) $schedule->jam, 0, 5),
            $segment['pickup_times'] ?? [],
            true,
        );
    }

    /** @return array<string, mixed> */
    private function segmentSchedulePayload(array $segment, object $schedule): array
    {
        $pickupTimes = $segment['pickup_times'] ?? [];

        if (SchemaCache::hasTable('schedule_segment')) {
            $pivotTimes = DB::table('schedule_segment')
                ->where('schedule_id', (int) $schedule->id)
                ->where('segment_id', (int) $segment['id'])
                ->pluck('jam_pickup')
                ->map(fn ($value): string => SegmentName::jam((string) $value))
                ->filter()
                ->values()
                ->all();
            if ($pivotTimes !== []) {
                $pickupTimes = $pivotTimes;
            }
        }

        return [
            'id' => (int) $segment['id'],
            'label' => (string) $segment['label'],
            'pickup_time' => (string) ($pickupTimes[0] ?? ''),
            'pickup_times' => array_values($pickupTimes),
            'price' => (float) ($segment['price'] ?? 0),
            'route_id' => (int) $segment['route_id'],
            'route_name' => (string) $segment['route_name'],
        ];
    }

    /** @return array<int, string> */
    private function occupiedSeatValues(int $tenantId, int $routeId, string $routeName, object $schedule, string $date, int $unit, bool $lock = false, int $ignoreRequestId = 0): array
    {
        $booked = $this->bookingSeatsQuery($tenantId, $routeId, $routeName, $schedule, $date, $unit);
        $requestSeats = $this->heldSeatsQuery($tenantId, $routeId, $schedule, $date, $unit, $ignoreRequestId);
        if ($lock) {
            $booked->lockForUpdate();
            $requestSeats->lockForUpdate();
        }

        return collect($booked->pluck('seat')->all())
            ->merge($requestSeats->pluck('seat')->all())
            ->map(fn ($seat): string => $this->normalizeSeat((string) $seat))
            ->unique()
            ->values()
            ->all();
    }

    private function upsertPublicCustomer(int $tenantId, int $poolId, string $name, string $phone, string $pickupAddress): void
    {
        if (! SchemaCache::hasTable('customers')) {
            return;
        }

        $payload = [
            'name' => $name,
            'phone' => $phone,
            'pickup_point' => $pickupAddress,
            'gmaps' => $pickupAddress,
            'created_at' => now(),
        ];
        if (SchemaCache::hasColumn('customers', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (SchemaCache::hasColumn('customers', 'pool_id')) {
            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        $query = DB::table('customers')->where('phone', $phone);
        if (SchemaCache::hasColumn('customers', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }
        $customerId = (int) ($query->value('id') ?? 0);
        if ($customerId > 0) {
            DB::table('customers')->where('id', $customerId)->update([
                'name' => $name,
                'pickup_point' => $pickupAddress,
                'gmaps' => $pickupAddress,
            ]);
        } else {
            DB::table('customers')->insert($payload);
        }
    }

    private function bookingSeatsQuery(int $tenantId, int $routeId, string $routeName, object $schedule, string $date, int $unit): Builder
    {
        $query = DB::table('bookings')->where('tanggal', $date)->where('jam', $schedule->jam)->where('unit', $unit)->where('status', '!=', 'canceled')->where('tenant_id', $tenantId);
        if (SchemaCache::hasColumn('bookings', 'route_id')) {
            $query->where(function (Builder $builder) use ($routeId, $routeName): void {
                $builder->where('route_id', $routeId)->orWhere(function (Builder $legacy) use ($routeName): void {
                    $legacy->whereNull('route_id')->where('rute', $routeName);
                });
            });
        } else {
            $query->where('rute', $routeName);
        }

        return $query->select('seat');
    }

    private function heldSeatsQuery(int $tenantId, int $routeId, object $schedule, string $date, int $unit, int $ignoreRequestId = 0): Builder
    {
        return DB::table('public_booking_request_seats as seats')
            ->join('public_booking_requests as requests', 'requests.id', '=', 'seats.request_id')
            ->where('requests.tenant_id', $tenantId)
            ->where('requests.route_id', $routeId)
            ->where('requests.tanggal', $date)
            ->where('requests.jam', $schedule->jam)
            ->where('requests.unit', $unit)
            ->where('requests.status', 'pending')
            ->where('requests.hold_expires_at', '>', now())
            ->when($ignoreRequestId > 0, fn (Builder $query) => $query->where('requests.id', '!=', $ignoreRequestId))
            ->select('seats.seat');
    }

    /** @return array<int, string> */
    private function seatTokens(object $schedule): array
    {
        $layout = $this->scheduleLayout($schedule);
        $tokens = [];
        foreach ($layout as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $type = strtolower(trim((string) ($cell['type'] ?? $cell['kind'] ?? '')));
                    if ($type !== '' && ! in_array($type, ['seat', 'sleeper'], true)) {
                        continue;
                    }
                    $value = $cell['label'] ?? $cell['seat'] ?? $cell['number'] ?? '';
                } else {
                    $value = $cell;
                }

                $token = $this->normalizeSeat((string) $value);
                if ($token !== '' && ! in_array($token, ['DRIVER', 'AISLE', '-', '_'], true)) {
                    $tokens[] = $token;
                }
            }
        }
        $tokens = array_values(array_unique($tokens));
        if ($tokens === []) {
            for ($i = 1; $i <= max(0, (int) ($schedule->seats ?? 0)); $i++) {
                $tokens[] = (string) $i;
            }
        }
        usort($tokens, fn (string $a, string $b): int => (is_numeric($a) && is_numeric($b)) ? ((int) $a <=> (int) $b) : strcmp($a, $b));

        return $tokens;
    }

    /** @return array<int, mixed> */
    private function scheduleLayout(object $schedule): array
    {
        $layout = $this->decodeLayout($schedule->layout ?? null);
        if (empty($layout) && ! empty($schedule->unit_id) && SchemaCache::hasTable('category_armada')) {
            $layout = $this->decodeLayout(
                DB::table('category_armada')
                    ->where('id', (int) $schedule->unit_id)
                    ->value('layout'),
            );
        }

        return $layout;
    }

    private function assertAdminRequestAccess(?object $request, int $userId): void
    {
        if (! $request || ! PoolScope::canAccessPool((int) $request->pool_id, $userId)) {
            abort(404);
        }
    }

    private function newRequestCode(): string
    {
        do {
            $code = 'PB-'.strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        } while (DB::table('public_booking_requests')->where('request_code', $code)->exists());

        return $code;
    }

    private function whatsappUrl(object $tenant, string $code, array $route, object $schedule, string $date, int $unit, array $seats, string $contactName): ?string
    {
        $phone = $this->normalizeWhatsapp((string) ($tenant->public_booking_whatsapp ?? ''));
        if (! $phone) {
            return null;
        }
        $message = "Halo {$tenant->name}, saya mengirim request booking {$code}.\nNama: {$contactName}\nRute: {$route['name']}\nTanggal: {$date}\nJam: ".substr((string) $schedule->jam, 0, 5)."\nUnit: {$unit}\nKursi: ".implode(', ', $seats)."\nMohon dibantu konfirmasi.";

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    private function normalizeWhatsapp(string $value): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $value) ?? '';
        if ($phone === '') {
            return null;
        }
        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }
        if (str_starts_with($phone, '8')) {
            return '62'.$phone;
        }

        return $phone;
    }

    private function normalizeSeat(string $seat): string
    {
        return strtoupper(trim($seat));
    }

    /** @return array<int, mixed> */
    private function decodeLayout(mixed $layout): array
    {
        if (is_array($layout)) {
            return $layout;
        }
        if (is_string($layout) && trim($layout) !== '') {
            $decoded = json_decode($layout, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
