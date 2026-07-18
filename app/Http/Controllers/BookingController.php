<?php

namespace App\Http\Controllers;

use App\Support\BookingCode;
use App\Support\Code39;
use App\Support\DeferredInertia;
use App\Support\HeadlessPdf;
use App\Support\ManifestLifecycle;
use App\Support\PoolScope;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFactory;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    private ?bool $bookingsHasDepartureCode = null;

    private ?bool $bookingsHasTicketCode = null;

    private ?bool $tripAssignmentsHasArmadaId = null;

    private ?bool $tripAssignmentsHasArmadaNopol = null;

    private ?bool $tripAssignmentsHasStatus = null;

    public function __invoke(Request $request): Response
    {
        $component = $request->routeIs('booking-console.index') ? 'BookingConsole' : 'Bookings';
        $isGroupDetailPage = $request->routeIs('bookings.detail');
        $listOnly = $request->routeIs('bookings.index') || $isGroupDetailPage;
        $usesDeferredInertia = DeferredInertia::opsEnabled();
        $deferBookingList = $usesDeferredInertia && $request->routeIs('bookings.index');
        $deferLatestBookings = $usesDeferredInertia && $request->routeIs('booking-console.index');
        $groupDetailKey = $isGroupDetailPage ? (string) $request->route('groupKey', '') : '';
        $bookingGroups = null;
        $resolveBookingGroups = function () use ($listOnly, &$bookingGroups): array {
            if (! $listOnly) {
                return [];
            }

            return $bookingGroups ??= $this->buildBookingGroups();
        };
        $emptyTotals = [
            'bookings' => 0,
            'customers' => 0,
            'routes' => 0,
            'schedules' => 0,
        ];

        return Inertia::render($component, [
            'totals' => $listOnly ? $emptyTotals : fn (): array => $this->bookingTotals(),
            'latestBookings' => $listOnly
                ? []
                : ($deferLatestBookings
                    ? Inertia::defer(fn (): array => $this->latestBookings(), 'booking-console-preview')
                    : fn (): array => $this->latestBookings()),
            'bookingGroups' => $deferBookingList
                ? Inertia::defer(fn (): array => $resolveBookingGroups(), 'booking-list')
                : fn (): array => $resolveBookingGroups(),
            'bookingRouteOptions' => $deferBookingList
                ? Inertia::defer(fn (): array => $this->bookingRouteOptions($resolveBookingGroups()), 'booking-list')
                : fn (): array => $listOnly ? $this->bookingRouteOptions($resolveBookingGroups()) : [],
            'bookingListReady' => $deferBookingList
                ? Inertia::defer(fn (): bool => true, 'booking-list')
                : true,
            'listOnly' => $listOnly,
            'groupDetailPage' => $isGroupDetailPage,
            'groupDetailKey' => $groupDetailKey,
            'serverNow' => now()->format('Y-m-d H:i'),
            'migrationChecklist' => [
                'Pindahkan API booking seats dan validasi konflik kursi',
                'Migrasikan master data routes, schedules, units, dan segments',
                'Porting UI seat layout ke komponen Svelte reusable',
                'Tambahkan test integrasi untuk create / update / cancel booking',
            ],
        ]);
    }

    public function printManifest(string $groupKey): View
    {
        $group = $this->findBookingGroupByKey($groupKey);
        abort_unless($group !== null, 404);

        $activePassengers = array_values(array_filter(
            $group['bookings'],
            fn (array $row): bool => strtolower((string) ($row['status'] ?? '')) !== 'canceled',
        ));
        $historyPassengers = array_values(array_filter(
            $group['bookings'],
            fn (array $row): bool => strtolower((string) ($row['status'] ?? '')) === 'canceled',
        ));

        return view('bookings.manifest-print', [
            'manifest' => $this->manifestPayload($group, $activePassengers, $historyPassengers),
        ]);
    }

    public function downloadManifestPdf(Request $request, string $groupKey)
    {
        $group = $this->findBookingGroupByKey($groupKey);
        abort_unless($group !== null, 404);

        $activePassengers = array_values(array_filter(
            $group['bookings'],
            fn (array $row): bool => strtolower((string) ($row['status'] ?? '')) !== 'canceled',
        ));
        $historyPassengers = array_values(array_filter(
            $group['bookings'],
            fn (array $row): bool => strtolower((string) ($row['status'] ?? '')) === 'canceled',
        ));
        $payload = $this->manifestPayload($group, $activePassengers, $historyPassengers);

        $pdfPath = $this->generatePdfFromView(
            'bookings.manifest-print',
            ['manifest' => $payload, 'exportMode' => 'pdf'],
            'Manifest Keberangkatan '.$payload['departure_code']
        );

        $filename = 'manifest-'.$payload['departure_code'].'.pdf';

        if ($request->boolean('inline')) {
            $contents = (string) file_get_contents($pdfPath);
            @unlink($pdfPath);

            return response($contents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return response()->download($pdfPath, $filename, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
    }

    public function downloadTicketPdf(Request $request, int $bookingId)
    {
        $payload = $this->ticketPayload($bookingId);

        $pdfPath = $this->generatePdfFromView(
            'bookings.ticket-print',
            ['ticket' => $payload, 'exportMode' => 'pdf'],
            'Tiket '.$payload['ticket_code']
        );

        $filename = 'ticket-'.$payload['ticket_code'].'.pdf';

        if ($request->boolean('inline')) {
            $contents = (string) file_get_contents($pdfPath);
            @unlink($pdfPath);

            return response($contents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return response()->download($pdfPath, $filename, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
    }

    public function printTicket(int $bookingId): View
    {
        return view('bookings.ticket-print', [
            'ticket' => $this->ticketPayload($bookingId),
        ]);
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<int, array<string, mixed>>  $activePassengers
     * @param  array<int, array<string, mixed>>  $historyPassengers
     * @return array<string, mixed>
     */
    private function manifestPayload(array $group, array $activePassengers, array $historyPassengers): array
    {
        $manifestLuggages = $this->manifestLuggages($group);
        $historyPassengers = $this->decorateHistoryPassengers($historyPassengers);

        return [
            'title' => 'Manifest Keberangkatan',
            'departure_code' => $group['departure_code'],
            'barcode_svg' => Code39::svgDataUri((string) $group['departure_code']),
            'rute' => $group['rute'],
            'tanggal' => $group['tanggal'],
            'jam' => $group['jam'],
            'unit' => $group['unit'],
            'driver_name' => $group['driver_name'],
            'armada_nopol' => $group['armada_nopol'],
            'total' => $group['total'],
            'active' => $group['active'],
            'canceled' => $group['canceled'],
            'lunas' => $group['lunas'],
            'refund' => $group['refund'],
            'belum_lunas' => $group['belum_lunas'],
            'passengers' => $activePassengers,
            'luggages' => $manifestLuggages,
            'luggage_total' => count($manifestLuggages),
            'luggage_revenue' => array_reduce(
                $manifestLuggages,
                static fn (float $carry, array $row): float => $carry + (float) ($row['price'] ?? 0),
                0.0,
            ),
            'history_passengers' => $historyPassengers,
            'logo_data_uri' => $this->brandingLogoDataUri(),
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function manifestLuggages(array $group): array
    {
        if (! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return [];
        }

        $assignmentIds = $this->matchingTripAssignmentIds(
            (string) ($group['rute'] ?? ''),
            (string) ($group['tanggal'] ?? ''),
            (string) ($group['jam'] ?? ''),
            (int) ($group['unit'] ?? 1),
            (int) ($group['assignment_id'] ?? 0),
        );

        if ($assignmentIds === []) {
            return [];
        }

        return DB::table('luggages')
            ->whereIn('trip_assignment_id', $assignmentIds)
            ->orderByDesc('id')
            ->get([
                'id',
                'kode_resi',
                'sender_name',
                'receiver_name',
                'rute',
                'tanggal',
                'quantity',
                'price',
                'status',
                'payment_status',
                'notes',
                'trip_assignment_id',
            ])
            ->map(function ($row): array {
                return [
                    'id' => (int) ($row->id ?? 0),
                    'kode_resi' => (string) ($row->kode_resi ?? ''),
                    'sender_name' => (string) ($row->sender_name ?? ''),
                    'receiver_name' => (string) ($row->receiver_name ?? ''),
                    'rute' => (string) ($row->rute ?? ''),
                    'tanggal' => (string) ($row->tanggal ?? ''),
                    'quantity' => (int) ($row->quantity ?? 0),
                    'price' => (float) ($row->price ?? 0),
                    'status' => $this->normalizeManifestLuggageStatus($row->status ?? null),
                    'payment_status' => (string) ($row->payment_status ?? ''),
                    'notes' => (string) ($row->notes ?? ''),
                    'trip_assignment_id' => (int) ($row->trip_assignment_id ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $historyPassengers
     * @return array<int, array<string, mixed>>
     */
    private function decorateHistoryPassengers(array $historyPassengers): array
    {
        if ($historyPassengers === []) {
            return [];
        }

        $fallbackMeta = $this->emptyManifestCancellationMeta();
        $bookingIds = array_values(array_filter(array_map(
            static fn (array $row): int => (int) ($row['id'] ?? 0),
            $historyPassengers,
        )));

        if ($bookingIds === [] || ! Schema::hasTable('cancellations')) {
            return array_map(
                static fn (array $row): array => array_merge($row, $fallbackMeta),
                $historyPassengers,
            );
        }

        $cancellationMeta = [];
        $rows = DB::table('cancellations')
            ->whereIn('booking_id', $bookingIds)
            ->orderByDesc('id')
            ->get(['booking_id', 'admin_user', 'reason', 'created_at']);

        foreach ($rows as $row) {
            $bookingId = (int) ($row->booking_id ?? 0);
            if ($bookingId <= 0 || array_key_exists($bookingId, $cancellationMeta)) {
                continue;
            }

            $cancellationMeta[$bookingId] = [
                'cancel_reason' => trim((string) ($row->reason ?? '')),
                'canceled_by' => trim((string) ($row->admin_user ?? '')),
                'canceled_at' => $this->formatManifestDateTime($row->created_at ?? null),
            ];
        }

        return array_map(function (array $row) use ($cancellationMeta, $fallbackMeta): array {
            $bookingId = (int) ($row['id'] ?? 0);
            $meta = $cancellationMeta[$bookingId] ?? $fallbackMeta;

            return array_merge($row, $meta);
        }, $historyPassengers);
    }

    /**
     * @return array{cancel_reason: string, canceled_by: string, canceled_at: string}
     */
    private function emptyManifestCancellationMeta(): array
    {
        return [
            'cancel_reason' => '',
            'canceled_by' => '',
            'canceled_at' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketPayload(int $bookingId): array
    {
        abort_unless(Schema::hasTable('bookings'), 404);
        $select = [
            'b.id',
            'b.name',
            'b.phone',
            'b.rute',
            'b.tanggal',
            'b.jam',
            'b.unit',
            'b.seat',
            'b.status',
            'b.pembayaran',
            'b.pickup_point',
            'b.price',
            'b.discount',
            DB::raw('s.rute as segment_name'),
            DB::raw('c.gmaps as gmaps'),
        ];

        if ($this->bookingsHasDepartureCode()) {
            $select[] = 'b.departure_code';
        }

        if ($this->bookingsHasTicketCode()) {
            $select[] = 'b.ticket_code';
        }

        $booking = $this->scopedBookingQuery('bookings as b', 'b.rute')
            ->leftJoin('segments as s', 's.id', '=', 'b.segment_id')
            ->leftJoin('customers as c', 'c.phone', '=', 'b.phone')
            ->select($select)
            ->where('b.id', $bookingId)
            ->first();

        abort_unless($booking !== null, 404);

        $jam = substr((string) ($booking->jam ?? ''), 0, 5);
        $departureCode = trim((string) ($booking->departure_code ?? ''));
        if ($departureCode === '') {
            $departureCode = BookingCode::departureCode(
                (string) ($booking->tanggal ?? ''),
                $jam,
                (int) ($booking->unit ?? 1),
                (string) ($booking->rute ?? ''),
            );
        }

        $ticketCode = trim((string) ($booking->ticket_code ?? ''));
        if ($ticketCode === '') {
            $ticketCode = BookingCode::ticketCode((int) $booking->id, (string) ($booking->tanggal ?? ''));
        }

        $assignmentMeta = $this->resolveAssignmentMeta(
            (string) ($booking->rute ?? ''),
            (string) ($booking->tanggal ?? ''),
            $jam,
            (int) ($booking->unit ?? 1),
        );

        return [
            'ticket_code' => $ticketCode,
            'barcode_svg' => Code39::svgDataUri($ticketCode),
            'departure_code' => $departureCode,
            'name' => (string) ($booking->name ?? ''),
            'phone' => (string) ($booking->phone ?? ''),
            'rute' => (string) ($booking->rute ?? ''),
            'tanggal' => (string) ($booking->tanggal ?? ''),
            'jam' => $jam,
            'unit' => (int) ($booking->unit ?? 1),
            'seat' => (string) ($booking->seat ?? ''),
            'status' => (string) ($booking->status ?? ''),
            'pembayaran' => (string) ($booking->pembayaran ?? ''),
            'pickup_point' => (string) ($booking->pickup_point ?? ''),
            'segment_name' => (string) ($booking->segment_name ?? ''),
            'gmaps' => (string) ($booking->gmaps ?? ''),
            'price' => (float) ($booking->price ?? 0),
            'discount' => (float) ($booking->discount ?? 0),
            'driver_name' => $assignmentMeta['driver_name'],
            'armada_nopol' => $assignmentMeta['armada_nopol'],
            'logo_data_uri' => $this->brandingLogoDataUri(),
        ];
    }

    /**
     * @return array{bookings: int, customers: int, routes: int, schedules: int}
     */
    private function bookingTotals(): array
    {
        return Cache::remember('bookings:totals:'.PoolScope::cacheKey(), now()->addSeconds(30), function (): array {
            return [
                'bookings' => Schema::hasTable('bookings') ? $this->scopedBookingQuery()->count() : 0,
                'customers' => Schema::hasTable('customers') ? $this->scopedCustomersCount() : 0,
                'routes' => Schema::hasTable('routes') ? $this->scopedRoutesQuery()->count() : 0,
                'schedules' => Schema::hasTable('schedules') ? $this->scopedScheduleQuery()->count() : 0,
            ];
        });
    }

    private function scopedBookingQuery(string $table = 'bookings', string $routeNameColumn = 'rute'): Builder
    {
        $query = DB::table($table);
        PoolScope::applyRouteScope($query, $this->bookingRouteIdColumn($table), $routeNameColumn);
        $this->applyTenantScopeIfExists($query, $table);

        return $query;
    }

    private function bookingRouteIdColumn(string $table): string
    {
        if (! Schema::hasColumn('bookings', 'route_id')) {
            return '';
        }

        if (preg_match('/^bookings(?:\s+as\s+([a-z0-9_]+))?$/i', trim($table), $matches) !== 1) {
            return '';
        }

        return isset($matches[1]) ? $matches[1].'.route_id' : 'route_id';
    }

    private function applyTenantScopeIfExists(Builder $query, string $table, string $alias = ''): void
    {
        [$baseTable, $tableAlias] = $this->parseTableAlias($table);
        if (! Schema::hasColumn($baseTable, 'tenant_id')) {
            return;
        }

        $effectiveAlias = $alias !== '' ? $alias : $tableAlias;
        $prefix = $effectiveAlias !== '' ? $effectiveAlias.'.' : '';
        PoolScope::applyTenantScope($query, $prefix.'tenant_id');
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

    private function scopedRoutesQuery(): Builder
    {
        $query = DB::table('routes');
        PoolScope::applyRouteScope($query, 'routes.id', 'routes.name');
        $this->applyTenantScopeIfExists($query, 'routes');

        return $query;
    }

    private function scopedScheduleQuery(string $table = 'schedules', string $alias = ''): Builder
    {
        $query = DB::table($table);
        $prefix = $alias !== '' ? $alias.'.' : '';

        PoolScope::applyRouteScope(
            $query,
            Schema::hasColumn('schedules', 'route_id') ? $prefix.'route_id' : '',
            $prefix.'rute',
        );
        $this->applyTenantScopeIfExists($query, $table, $alias);

        return $query;
    }

    private function scopedCustomersCount(): int
    {
        $query = DB::table('customers');
        PoolScope::applyCustomerScope($query, 'customers');
        $this->applyTenantScopeIfExists($query, 'customers');

        return (int) $query->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function latestBookings(): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $select = ['id', 'name', 'phone', 'rute', 'tanggal', 'jam', 'unit', 'seat', 'status', 'pembayaran'];
        if ($this->bookingsHasDepartureCode()) {
            $select[] = 'departure_code';
        }
        if ($this->bookingsHasTicketCode()) {
            $select[] = 'ticket_code';
        }

        return $this->scopedBookingQuery()
            ->select($select)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $jam = substr((string) $row->jam, 0, 5);
                $departureCode = trim((string) ($row->departure_code ?? ''));
                $ticketCode = trim((string) ($row->ticket_code ?? ''));

                return [
                    'id' => (int) $row->id,
                    'group_key' => md5($this->tripRawKey((string) $row->rute, (string) $row->tanggal, $jam, (int) $row->unit)),
                    'name' => (string) $row->name,
                    'phone' => (string) $row->phone,
                    'rute' => (string) $row->rute,
                    'tanggal' => (string) $row->tanggal,
                    'jam' => $jam,
                    'unit' => (int) $row->unit,
                    'seat' => (string) $row->seat,
                    'status' => (string) $row->status,
                    'pembayaran' => (string) $row->pembayaran,
                    'departure_code' => $departureCode !== ''
                        ? $departureCode
                        : BookingCode::departureCode((string) $row->tanggal, $jam, (int) $row->unit, (string) $row->rute),
                    'ticket_code' => $ticketCode !== ''
                        ? $ticketCode
                        : BookingCode::ticketCode((int) $row->id, (string) $row->tanggal),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildBookingGroups(): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $signature = $this->scopedBookingQuery()
            ->selectRaw('COALESCE(MAX(id), 0) as max_id, COUNT(*) as total_rows')
            ->first();
        $maxId = (int) ($signature->max_id ?? 0);
        $totalRows = (int) ($signature->total_rows ?? 0);
        $schedulesSignature = $this->buildTableMutationSignature('schedules');
        $assignmentsSignature = $this->buildTableMutationSignature('trip_assignments');
        $cacheKey = "bookings:list-groups:v10:".PoolScope::cacheKey().":{$maxId}:{$totalRows}:{$schedulesSignature}:{$assignmentsSignature}";

        return Cache::remember($cacheKey, now()->addSeconds(20), function (): array {
            $select = [
                'b.id',
                'b.name',
                'b.phone',
                'b.rute',
                'b.tanggal',
                'b.jam',
                'b.unit',
                'b.seat',
                'b.status',
                'b.pembayaran',
                'b.pickup_point',
                'b.price',
                'b.discount',
                DB::raw('s.rute as segment_name'),
                DB::raw('c.gmaps as gmaps'),
            ];

            if ($this->bookingsHasDepartureCode()) {
                $select[] = 'b.departure_code';
            }

            if ($this->bookingsHasTicketCode()) {
                $select[] = 'b.ticket_code';
            }

            $rows = $this->scopedBookingQuery('bookings as b', 'b.rute')
                ->leftJoin('segments as s', 's.id', '=', 'b.segment_id')
                ->leftJoin('customers as c', 'c.phone', '=', 'b.phone')
                ->select($select)
                ->orderByDesc('b.tanggal')
                ->orderBy('b.jam')
                ->orderBy('b.unit')
                ->orderBy('b.seat')
                ->limit(2000)
                ->get();

            $grouped = [];
            foreach ($rows as $row) {
                $jam = substr((string) $row->jam, 0, 5);
                $tripKey = $this->tripRawKey((string) $row->rute, (string) $row->tanggal, $jam, (int) $row->unit);
                $hashedKey = md5($tripKey);
                $departureCode = trim((string) ($row->departure_code ?? ''));
                if ($departureCode === '') {
                    $departureCode = BookingCode::departureCode(
                        (string) ($row->tanggal ?? ''),
                        $jam,
                        (int) ($row->unit ?? 1),
                        (string) ($row->rute ?? ''),
                    );
                }

                $ticketCode = trim((string) ($row->ticket_code ?? ''));
                if ($ticketCode === '') {
                    $ticketCode = BookingCode::ticketCode((int) $row->id, (string) ($row->tanggal ?? ''));
                }

                if (! array_key_exists($tripKey, $grouped)) {
                    $grouped[$tripKey] = [
                        'key' => $hashedKey,
                        'rute' => (string) $row->rute,
                        'tanggal' => (string) $row->tanggal,
                        'jam' => $jam,
                        'unit' => (int) $row->unit,
                        'assignment_id' => null,
                        'departure_code' => $departureCode,
                        'driver_name' => '-',
                        'armada_nopol' => '-',
                        'total' => 0,
                        'active' => 0,
                        'canceled' => 0,
                        'lunas' => 0,
                        'refund' => 0,
                        'belum_lunas' => 0,
                        'bop' => 0,
                        'departure_status' => 'active',
                        'departure_can_arrive' => false,
                        'bookings' => [],
                    ];
                }

                $status = strtolower((string) $row->status);
                $payment = strtolower((string) $row->pembayaran);
                $hideFromDepartureTotals = $this->shouldHideDeparturePassenger($status, $payment);

                if (! $hideFromDepartureTotals) {
                    $grouped[$tripKey]['total'] += 1;
                    if ($status === 'canceled') {
                        $grouped[$tripKey]['canceled'] += 1;
                    } else {
                        $grouped[$tripKey]['active'] += 1;
                    }
                    if ($payment === 'lunas') {
                        $grouped[$tripKey]['lunas'] += 1;
                    } elseif ($payment === 'refund') {
                        $grouped[$tripKey]['refund'] += 1;
                    } else {
                        $grouped[$tripKey]['belum_lunas'] += 1;
                    }
                }

                $grouped[$tripKey]['bookings'][] = [
                    'id' => (int) $row->id,
                    'ticket_code' => $ticketCode,
                    'name' => (string) $row->name,
                    'phone' => (string) $row->phone,
                    'seat' => (string) $row->seat,
                    'status' => (string) $row->status,
                    'pembayaran' => (string) $row->pembayaran,
                    'pickup_point' => (string) ($row->pickup_point ?? ''),
                    'segment_name' => (string) ($row->segment_name ?? ''),
                    'gmaps' => (string) ($row->gmaps ?? ''),
                    'price' => (float) ($row->price ?? 0),
                    'discount' => (float) ($row->discount ?? 0),
                ];
            }

            if (Schema::hasTable('trip_assignments')) {
                $assignmentSelect = [
                    't.id',
                    't.rute',
                    't.tanggal',
                    't.jam',
                    't.unit',
                    DB::raw('d.nama as driver_name'),
                ];

                if ($this->tripAssignmentsHasStatus()) {
                    $assignmentSelect[] = 't.status';
                }

                if ($this->tripAssignmentsHasArmadaId()) {
                    $assignmentSelect[] = 't.armada_id';

                    if (Schema::hasTable('armadas')) {
                        $assignmentSelect[] = DB::raw('a.nopol as nopol');
                    }
                }

                if ($this->tripAssignmentsHasArmadaNopol()) {
                    $assignmentSelect[] = 't.armada_nopol';
                }

                $assignmentRows = $this->scopedBookingQuery('trip_assignments as t', 't.rute')
                    ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id')
                    ->when($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas'), static function ($query) {
                        $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
                    })
                    ->select($assignmentSelect)
                    ->orderByDesc('t.tanggal')
                    ->orderBy('t.jam')
                    ->orderBy('t.unit')
                    ->limit(2000)
                    ->get();

                foreach ($assignmentRows as $row) {
                    $status = ManifestLifecycle::syncTripAssignmentStatus($row);
                    $jam = substr((string) ($row->jam ?? ''), 0, 5);
                    $unit = max(1, (int) ($row->unit ?? 1));
                    $route = (string) ($row->rute ?? '');
                    $date = (string) ($row->tanggal ?? '');

                    if (trim($route) === '' || trim($date) === '' || trim($jam) === '') {
                        continue;
                    }

                    $tripKey = $this->tripRawKey($route, $date, $jam, $unit);
                    if (! array_key_exists($tripKey, $grouped)) {
                        $grouped[$tripKey] = [
                            'key' => md5($tripKey),
                            'rute' => $route,
                            'tanggal' => $date,
                            'jam' => $jam,
                            'unit' => $unit,
                            'assignment_id' => null,
                            'departure_code' => BookingCode::departureCode($date, $jam, $unit, $route),
                            'driver_name' => '-',
                            'armada_nopol' => '-',
                            'total' => 0,
                            'active' => 0,
                            'canceled' => 0,
                            'lunas' => 0,
                            'refund' => 0,
                            'belum_lunas' => 0,
                            'bop' => 0,
                            'departure_status' => 'active',
                            'departure_can_arrive' => false,
                            'bookings' => [],
                        ];
                    }

                    $status = strtolower(trim((string) ($status ?: ($row->status ?? 'active'))));
                    if (! in_array($status, ['active', 'departed', 'canceled', 'arrived'], true)) {
                        $status = 'closed';
                    }

                    $grouped[$tripKey]['assignment_id'] = (int) ($row->id ?? 0) ?: null;
                    $grouped[$tripKey]['departure_status'] = $status;
                    if ($status === 'canceled') {
                        $grouped[$tripKey]['driver_name'] = '-';
                        $grouped[$tripKey]['armada_nopol'] = '-';
                    } else {
                        $grouped[$tripKey]['driver_name'] = trim((string) ($row->driver_name ?? '')) !== ''
                            ? trim((string) ($row->driver_name ?? ''))
                            : '-';
                        $grouped[$tripKey]['armada_nopol'] = trim((string) ($row->armada_nopol ?? '')) !== ''
                            ? strtoupper(trim((string) ($row->armada_nopol ?? '')))
                            : (trim((string) ($row->nopol ?? '')) !== ''
                                ? strtoupper(trim((string) ($row->nopol ?? '')))
                                : '-');
                    }
                }
            }

            if (! empty($grouped) && Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'bop')) {
                $routeKeys = [];
                $dows = [];
                $jams = [];

                foreach ($grouped as $item) {
                    $route = trim((string) ($item['rute'] ?? ''));
                    $date = trim((string) ($item['tanggal'] ?? ''));
                    $jam = substr((string) ($item['jam'] ?? ''), 0, 5);
                    $dayOfWeek = $this->safeDayOfWeek($date);

                    if ($route !== '') {
                        $routeKeys[$this->normalizeTripRoute($route)] = true;
                    }
                    if ($dayOfWeek !== null) {
                        $dows[$dayOfWeek] = true;
                    }
                    if ($jam !== '') {
                        $jams[$jam.':00'] = true;
                    }
                }

                if (! empty($routeKeys) && ! empty($dows) && ! empty($jams)) {
                    $scheduleRows = DB::table('schedules')
                        ->whereIn('dow', array_keys($dows))
                        ->whereIn('jam', array_keys($jams));
                    $this->applyTenantScopeIfExists($scheduleRows, 'schedules');
                    $scheduleRows = $scheduleRows->get(['rute', 'dow', 'jam', 'bop']);

                    $bopBySchedule = [];
                    foreach ($scheduleRows as $row) {
                        if (! isset($routeKeys[$this->normalizeTripRoute((string) ($row->rute ?? ''))])) {
                            continue;
                        }

                        $bopBySchedule[$this->tripScheduleKey(
                            (string) ($row->rute ?? ''),
                            (int) ($row->dow ?? 0),
                            substr((string) ($row->jam ?? ''), 0, 5),
                        )] = (float) ($row->bop ?? 0);
                    }

                    foreach ($grouped as $key => $item) {
                        if (strtolower((string) ($item['departure_status'] ?? 'active')) === 'canceled') {
                            $grouped[$key]['bop'] = 0;

                            continue;
                        }

                        $dayOfWeek = $this->safeDayOfWeek((string) ($item['tanggal'] ?? ''));
                        if ($dayOfWeek === null) {
                            $grouped[$key]['bop'] = 0;

                            continue;
                        }

                        $grouped[$key]['bop'] = $bopBySchedule[$this->tripScheduleKey(
                            (string) ($item['rute'] ?? ''),
                            $dayOfWeek,
                            (string) ($item['jam'] ?? ''),
                        )] ?? 0;
                    }
                }
            }

            foreach ($grouped as $key => $item) {
                $grouped[$key]['departure_can_arrive'] = $this->departureCanArrive(
                    (string) ($item['tanggal'] ?? ''),
                    (string) ($item['jam'] ?? ''),
                    (string) ($item['departure_status'] ?? 'active'),
                    (string) ($item['driver_name'] ?? ''),
                    (string) ($item['armada_nopol'] ?? ''),
                );
            }

            $grouped = array_filter(
                $grouped,
                static fn (array $item): bool => (int) ($item['total'] ?? 0) > 0
                    || (int) ($item['assignment_id'] ?? 0) > 0,
            );

            return array_values($grouped);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $bookingGroups
     * @return array<int, string>
     */
    private function bookingRouteOptions(array $bookingGroups): array
    {
        $groupRoutes = [];
        foreach ($bookingGroups as $group) {
            $route = trim((string) ($group['rute'] ?? ''));
            if ($route === '') {
                continue;
            }

            $groupRoutes[$this->normalizeTripRoute($route)] = $route;
        }

        if ($groupRoutes === []) {
            return [];
        }

        if (! Schema::hasTable('routes')) {
            return $this->sortBookingRouteLabels(array_values($groupRoutes));
        }

        $masterRoutes = $this->scopedRoutesQuery()
            ->orderBy('name')
            ->pluck('name')
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        if ($masterRoutes === []) {
            return $this->sortBookingRouteLabels(array_values($groupRoutes));
        }

        $options = [];
        $seen = [];
        foreach ($masterRoutes as $route) {
            $key = $this->normalizeTripRoute($route);
            if (! isset($groupRoutes[$key]) || isset($seen[$key])) {
                continue;
            }

            $options[] = $route;
            $seen[$key] = true;
        }

        return $options;
    }

    /**
     * @param  array<int, string>  $routes
     * @return array<int, string>
     */
    private function sortBookingRouteLabels(array $routes): array
    {
        usort($routes, static fn (string $left, string $right): int => strnatcasecmp($left, $right));

        return $routes;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findBookingGroupByKey(string $groupKey): ?array
    {
        foreach ($this->buildBookingGroups() as $group) {
            if ((string) ($group['key'] ?? '') === $groupKey) {
                return $group;
            }
        }

        return null;
    }

    private function safeDayOfWeek(string $date): ?int
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
                return Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
            }

            return Carbon::parse($date)->dayOfWeek;
        } catch (\Throwable) {
            return null;
        }
    }

    private function departureCanArrive(
        string $tanggal,
        string $jam,
        string $status = 'active',
        string $driverName = '',
        string $armadaNopol = '',
    ): bool {
        $normalizedStatus = strtolower(trim($status));
        if ($normalizedStatus !== 'departed') {
            return false;
        }

        if (ManifestLifecycle::isClosedStatus($normalizedStatus)) {
            return false;
        }

        if (! $this->hasRequiredDepartureAssignmentMeta($driverName, $armadaNopol)) {
            return false;
        }

        $datePart = substr(trim($tanggal), 0, 10);

        try {
            $today = now()->startOfDay();
            $departureDate = Carbon::createFromFormat('Y-m-d', $datePart)->startOfDay();

            return $departureDate->lte($today);
        } catch (\Throwable) {
            return false;
        }
    }

    private function hasRequiredDepartureAssignmentMeta(string $driverName, string $armadaNopol): bool
    {
        $normalizedDriverName = trim($driverName);
        $normalizedArmadaNopol = strtoupper(trim($armadaNopol));

        return $normalizedDriverName !== ''
            && $normalizedDriverName !== '-'
            && $normalizedArmadaNopol !== ''
            && $normalizedArmadaNopol !== '-';
    }

    private function shouldHideDeparturePassenger(string $status, string $payment): bool
    {
        return strtolower(trim($status)) === 'canceled'
            && strtolower(trim($payment)) === 'belum lunas';
    }

    /**
     * @return array{driver_name: string, armada_nopol: string}
     */
    private function resolveAssignmentMeta(string $rute, string $tanggal, string $jam, int $unit): array
    {
        if (! Schema::hasTable('trip_assignments')) {
            return [
                'driver_name' => '-',
                'armada_nopol' => '-',
            ];
        }

        $select = [
            't.rute',
            DB::raw('d.nama as driver_name'),
        ];

        if ($this->tripAssignmentsHasStatus()) {
            $select[] = 't.status';
        }

        if ($this->tripAssignmentsHasArmadaNopol()) {
            $select[] = 't.armada_nopol';
        }

        if ($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas')) {
            $select[] = DB::raw('a.nopol as nopol');
        }

        $rows = DB::table('trip_assignments as t')
            ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id')
            ->when($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas'), static function ($query) {
                $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
            })
            ->select($select)
            ->where('t.tanggal', $tanggal)
            ->where('t.jam', $jam.':00')
            ->where('t.unit', $unit)
            ->get();
        $routeKey = $this->normalizeTripRoute($rute);
        $row = $rows->first(fn ($item) => $this->normalizeTripRoute((string) ($item->rute ?? '')) === $routeKey);
        $status = strtolower(trim((string) ManifestLifecycle::syncTripAssignmentStatus($row)));

        if ($status === 'canceled') {
            return [
                'driver_name' => '-',
                'armada_nopol' => '-',
            ];
        }

        return [
            'driver_name' => trim((string) ($row->driver_name ?? '')) !== ''
                ? trim((string) ($row->driver_name ?? ''))
                : '-',
            'armada_nopol' => trim((string) ($row->armada_nopol ?? '')) !== ''
                ? strtoupper(trim((string) ($row->armada_nopol ?? '')))
                : (trim((string) ($row->nopol ?? '')) !== ''
                    ? strtoupper(trim((string) ($row->nopol ?? '')))
                    : '-'),
        ];
    }

    private function tripRawKey(string $rute, string $tanggal, string $jam, int $unit): string
    {
        return implode('|', [
            $this->normalizeTripRoute($rute),
            trim($tanggal),
            substr(trim($jam), 0, 5),
            (string) max(1, $unit),
        ]);
    }

    private function tripScheduleKey(string $rute, int $dow, string $jam): string
    {
        return implode('|', [
            $this->normalizeTripRoute($rute),
            (string) $dow,
            substr(trim($jam), 0, 5),
        ]);
    }

    private function normalizeTripRoute(string $rute): string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $rute) ?? ''));
        $normalized = str_replace([' => ', ' -> ', ' → ', ' – ', ' — ', ' - '], ' TO ', $normalized);
        $normalized = str_replace(['=>', '->', '→', '–', '—'], ' TO ', $normalized);
        $normalized = preg_replace('/\s*-\s*/', ' TO ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    /**
     * @return array<int, int>
     */
    private function matchingTripAssignmentIds(string $rute, string $tanggal, string $jam, int $unit, int $preferredId = 0): array
    {
        $ids = $preferredId > 0 ? [$preferredId] : [];

        if (! Schema::hasTable('trip_assignments')) {
            return $ids;
        }

        $targetRoute = $this->normalizeTripRoute($rute);
        $rows = DB::table('trip_assignments')
            ->where('tanggal', $tanggal)
            ->where('jam', $this->normalizeManifestTime($jam))
            ->where('unit', max(1, $unit))
            ->get(['id', 'rute']);

        foreach ($rows as $row) {
            if ($this->normalizeTripRoute((string) ($row->rute ?? '')) === $targetRoute) {
                $ids[] = (int) ($row->id ?? 0);
            }
        }

        return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
    }

    private function normalizeManifestTime(string $jam): string
    {
        $value = trim($jam);

        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value.':00';
        }

        return substr($value, 0, 8);
    }

    private function normalizeManifestLuggageStatus(mixed $status): string
    {
        $raw = trim((string) ($status ?? ''));
        $normalized = strtolower($raw);

        return match ($normalized) {
            '', 'pending', 'done', 'diterima', 'barang sudah diterima' => 'Diterima',
            'active', 'sent', 'barang sudah dipickup', 'dalam perjalanan' => 'Dalam Perjalanan',
            'barang sudah tiba', 'tiba di tujuan' => 'Tiba di Tujuan',
            'canceled' => 'Batal',
            default => $raw !== '' ? $raw : '-',
        };
    }

    private function formatManifestDateTime(mixed $value): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        try {
            return Carbon::parse($raw)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $raw;
        }
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

    private function tripAssignmentsHasArmadaId(): bool
    {
        if ($this->tripAssignmentsHasArmadaId === null) {
            $this->tripAssignmentsHasArmadaId = Schema::hasColumn('trip_assignments', 'armada_id');
        }

        return $this->tripAssignmentsHasArmadaId;
    }

    private function bookingsHasDepartureCode(): bool
    {
        if ($this->bookingsHasDepartureCode === null) {
            $this->bookingsHasDepartureCode = Schema::hasColumn('bookings', 'departure_code');
        }

        return $this->bookingsHasDepartureCode;
    }

    private function bookingsHasTicketCode(): bool
    {
        if ($this->bookingsHasTicketCode === null) {
            $this->bookingsHasTicketCode = Schema::hasColumn('bookings', 'ticket_code');
        }

        return $this->bookingsHasTicketCode;
    }

    private function tripAssignmentsHasArmadaNopol(): bool
    {
        if ($this->tripAssignmentsHasArmadaNopol === null) {
            $this->tripAssignmentsHasArmadaNopol = Schema::hasColumn('trip_assignments', 'armada_nopol');
        }

        return $this->tripAssignmentsHasArmadaNopol;
    }

    private function tripAssignmentsHasStatus(): bool
    {
        if ($this->tripAssignmentsHasStatus === null) {
            $this->tripAssignmentsHasStatus = Schema::hasTable('trip_assignments')
                && Schema::hasColumn('trip_assignments', 'status');
        }

        return $this->tripAssignmentsHasStatus;
    }

    private function brandingLogoDataUri(): ?string
    {
        static $cached = false;
        static $value = null;

        if ($cached) {
            return $value;
        }

        $path = public_path('branding/qbus-logo-full.png');
        if (! is_file($path)) {
            $cached = true;

            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $value = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        $cached = true;

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function generatePdfFromView(string $view, array $data, string $title): string
    {
        $html = ViewFactory::make($view, $data)->render();
        $pdfPath = storage_path('app/tmp/'.md5($title.microtime(true)).'.pdf');
        $pdfDir = dirname($pdfPath);

        if (! is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        HeadlessPdf::renderHtmlToPdf($html, $pdfPath, ['title' => $title]);

        return $pdfPath;
    }
}
