<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActivityLog;
use App\Support\BookingCode;
use App\Support\PoolScope;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BookingApiController extends Controller
{
    private ?bool $bookingsHasDepartureCode = null;

    private ?bool $bookingsHasTicketCode = null;

    private ?bool $bookingsHasRouteId = null;

    private ?bool $tripAssignmentsHasArmadaId = null;

    private ?bool $tripAssignmentsHasArmadaNopol = null;

    private ?bool $tripAssignmentsHasStatus = null;

    private ?bool $schedulesHasSeatsColumn = null;

    private ?bool $schedulesHasBopColumn = null;

    public function routesByDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $validated['tanggal'];
        $cacheKey = 'booking:routes-by-date:'.$date.':'.PoolScope::cacheKey();
        $routes = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($date) {
            $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

            $query = DB::table('schedules')
                ->where('dow', $dow);
            PoolScope::applyRouteScope(
                $query,
                Schema::hasColumn('schedules', 'route_id') ? 'route_id' : '',
                'rute',
            );

            return $query
                ->distinct()
                ->orderBy('rute')
                ->pluck('rute')
                ->values()
                ->all();
        });

        return $this->ok(['routes' => $routes]);
    }

    public function schedules(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rute' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
        ]);

        $rute = $validated['rute'];
        $date = $validated['tanggal'];
        if (! PoolScope::canAccessRouteName($rute)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $cacheKey = 'booking:schedules:v2:'.PoolScope::cacheKey().':'.md5($rute.'|'.$date);
        $rows = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($rute, $date) {
            $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
            $select = [
                's.id',
                's.jam',
                's.units',
                's.unit_label',
                's.layout',
                's.unit_id',
            ];
            if ($this->schedulesHasSeatsColumn()) {
                $select[] = 's.seats';
            }
            if ($this->schedulesHasBopColumn()) {
                $select[] = 's.bop';
            } else {
                $select[] = DB::raw('0 as bop');
            }

            $query = DB::table('schedules as s')
                ->where('s.rute', $rute)
                ->where('s.dow', $dow)
                ->orderBy('s.jam');
            PoolScope::applyRouteScope(
                $query,
                Schema::hasColumn('schedules', 'route_id') ? 's.route_id' : '',
                's.rute',
            );

            return $query->get($select);
        });

        $optionsBySchedule = [];
        $unitIds = $rows
            ->pluck('unit_id')
            ->filter(static fn ($id) => $id !== null)
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->values()
            ->all();

        if (Schema::hasTable('schedule_units')) {
            $scheduleIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
            if (! empty($scheduleIds)) {
                $items = DB::table('schedule_units')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->orderBy('schedule_id')
                    ->orderBy('unit_no')
                    ->get(['schedule_id', 'unit_no', 'label', 'unit_id']);

                foreach ($items as $item) {
                    $scheduleId = (int) ($item->schedule_id ?? 0);
                    if ($scheduleId <= 0) {
                        continue;
                    }
                    if (! isset($optionsBySchedule[$scheduleId])) {
                        $optionsBySchedule[$scheduleId] = [];
                    }
                    $resolvedUnitId = $item->unit_id !== null ? (int) $item->unit_id : null;
                    if ($resolvedUnitId !== null && $resolvedUnitId > 0) {
                        $unitIds[] = $resolvedUnitId;
                    }
                    $optionsBySchedule[$scheduleId][] = [
                        'unit_no' => (int) ($item->unit_no ?? 0),
                        'label' => (string) ($item->label ?? ''),
                        'unit_id' => $resolvedUnitId,
                    ];
                }
            }
        }

        $unitMap = [];
        $unitIds = array_values(array_unique(array_filter($unitIds, static fn ($id) => (int) $id > 0)));
        if (! empty($unitIds)) {
            $unitMap = DB::table('units')
                ->whereIn('id', $unitIds)
                ->get(['id', 'kapasitas', 'layout', 'nopol'])
                ->keyBy(static fn ($row) => (int) ($row->id ?? 0))
                ->all();
        }

        $schedules = $rows->map(function ($row) use ($optionsBySchedule, $unitMap) {
            $scheduleId = (int) ($row->id ?? 0);
            $baseUnitId = (int) ($row->unit_id ?? 0);
            $baseUnit = $baseUnitId > 0 ? ($unitMap[$baseUnitId] ?? null) : null;
            $baseLayoutData = $this->decodeLayout($baseUnit->layout ?? null);
            if (empty($baseLayoutData)) {
                $baseLayoutData = $this->decodeLayout($row->layout ?? null);
            }
            $baseSeatCount = $this->countSeatsFromLayout($baseLayoutData);
            if ($baseSeatCount <= 0) {
                $baseSeatCount = max(
                    (int) ($baseUnit->kapasitas ?? 0),
                    (int) ($row->seats ?? 0),
                );
            }

            $rawUnitOptions = $optionsBySchedule[$scheduleId] ?? [];
            $unitOptions = [];
            foreach ($rawUnitOptions as $option) {
                $optionUnitId = (int) ($option['unit_id'] ?? 0);
                $optionUnit = $optionUnitId > 0 ? ($unitMap[$optionUnitId] ?? null) : null;
                $optionLayout = $this->decodeLayout($optionUnit->layout ?? null);
                if (empty($optionLayout)) {
                    $optionLayout = $baseLayoutData;
                }

                $optionSeats = $this->countSeatsFromLayout($optionLayout);
                if ($optionSeats <= 0) {
                    $optionSeats = max(
                        (int) ($optionUnit->kapasitas ?? 0),
                        $baseSeatCount,
                    );
                }

                $unitOptions[] = [
                    'unit_no' => (int) ($option['unit_no'] ?? 0),
                    'label' => (string) ($option['label'] ?? ''),
                    'unit_id' => $optionUnitId > 0 ? $optionUnitId : null,
                    'layout' => $optionLayout,
                    'seats' => $optionSeats,
                    'nopol' => strtoupper(trim((string) ($optionUnit->nopol ?? ($baseUnit->nopol ?? '')))),
                ];
            }

            if (empty($unitOptions)) {
                $count = max(1, (int) ($row->units ?? 1));
                $fallbackLabel = trim((string) ($row->unit_label ?? ''));
                $unitOptions = [];
                for ($i = 1; $i <= $count; $i += 1) {
                    $unitOptions[] = [
                        'unit_no' => $i,
                        'label' => $fallbackLabel !== '' ? $fallbackLabel : "Unit {$i}",
                        'unit_id' => $baseUnitId > 0 ? $baseUnitId : null,
                        'layout' => $baseLayoutData,
                        'seats' => $baseSeatCount,
                        'nopol' => strtoupper(trim((string) ($baseUnit->nopol ?? ''))),
                    ];
                }
            }

            $defaultOption = collect($unitOptions)->first(static fn (array $option) => (int) ($option['unit_no'] ?? 0) === 1)
                ?? ($unitOptions[0] ?? null);
            $defaultLayout = is_array($defaultOption) && is_array($defaultOption['layout'] ?? null)
                ? $defaultOption['layout']
                : $baseLayoutData;
            $defaultSeats = max(is_array($defaultOption) ? (int) ($defaultOption['seats'] ?? 0) : 0, $baseSeatCount);
            $defaultNopol = strtoupper(trim((string) (is_array($defaultOption) ? ($defaultOption['nopol'] ?? '') : '')));
            if ($defaultNopol === '') {
                $defaultNopol = strtoupper(trim((string) ($baseUnit->nopol ?? '')));
            }

            return [
                'jam' => substr((string) $row->jam, 0, 5),
                'units' => (int) ($row->units ?? 1),
                'seats' => $defaultSeats,
                'layout' => $defaultLayout,
                'unit_id' => $baseUnitId,
                'nopol' => $defaultNopol,
                'unit_label' => (string) ($row->unit_label ?? ''),
                'bop' => (float) ($row->bop ?? 0),
                'unit_options' => $unitOptions,
            ];
        })->values()->all();

        return $this->ok(['schedules' => $schedules]);
    }

    public function bookedSeatsDetail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rute' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['nullable', 'integer', 'min:1'],
        ]);

        $jamSql = $this->normalizeTime($validated['jam']);
        $unit = (int) ($validated['unit'] ?? 1);

        if (! PoolScope::canAccessRouteName((string) $validated['rute'])) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $query = DB::table('bookings as b')
            ->leftJoin('segments as s', 'b.segment_id', '=', 's.id')
            ->where('b.tanggal', $validated['tanggal'])
            ->where('b.jam', $jamSql)
            ->where('b.unit', $unit)
            ->where('b.status', '!=', 'canceled');
        PoolScope::applyRouteIdentity($query, (string) $validated['rute'], $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');
        PoolScope::applyRouteScope($query, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');

        $rows = $query->get([
                'b.id',
                'b.seat',
                'b.name',
                'b.phone',
                'b.pembayaran',
                'b.pickup_point',
                'b.segment_id',
                'b.price',
                'b.discount',
                DB::raw('s.rute as segment_name'),
            ]);

        $details = [];
        foreach ($rows as $row) {
            $seat = (string) $row->seat;
            $details[$seat] = [
                'id' => (int) ($row->id ?? 0),
                'name' => (string) ($row->name ?? ''),
                'phone' => (string) ($row->phone ?? ''),
                'pembayaran' => (string) ($row->pembayaran ?? 'Belum Lunas'),
                'pickup_point' => (string) ($row->pickup_point ?? ''),
                'segment_id' => (int) ($row->segment_id ?? 0),
                'segment_name' => (string) ($row->segment_name ?? ''),
                'price' => (float) ($row->price ?? 0),
                'discount' => (float) ($row->discount ?? 0),
            ];
        }

        return $this->ok(['details' => $details]);
    }

    public function editSeatOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rute' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['nullable', 'integer', 'min:1'],
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'current_seat' => ['nullable', 'string', 'max:20'],
        ]);

        $rute = trim((string) $validated['rute']);
        $tanggal = (string) $validated['tanggal'];
        $jam = (string) $validated['jam'];
        $jamSql = $this->normalizeTime($jam);
        $unit = max(1, (int) ($validated['unit'] ?? 1));
        $bookingId = (int) ($validated['booking_id'] ?? 0);
        $dow = Carbon::createFromFormat('Y-m-d', $tanggal)->dayOfWeek;

        if (! PoolScope::canAccessRouteName($rute)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $scheduleQuery = DB::table('schedules as s')
            ->where('s.rute', $rute)
            ->where('s.dow', $dow)
            ->where('s.jam', $jamSql)
            ->orderBy('s.id');
        PoolScope::applyRouteScope(
            $scheduleQuery,
            Schema::hasColumn('schedules', 'route_id') ? 's.route_id' : '',
            's.rute',
        );

        $scheduleRows = $scheduleQuery->get([
                's.id',
                's.rute',
                's.dow',
                's.jam',
                's.units',
                's.unit_label',
                's.layout',
                's.unit_id',
            ]);

        $optionsBySchedule = [];
        $unitIds = $scheduleRows
            ->pluck('unit_id')
            ->filter(static fn ($id) => $id !== null)
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->values()
            ->all();

        if (Schema::hasTable('schedule_units')) {
            $scheduleIds = $scheduleRows
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->filter(static fn ($id) => $id > 0)
                ->values()
                ->all();

            if (! empty($scheduleIds)) {
                $optionRows = DB::table('schedule_units')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->orderBy('schedule_id')
                    ->orderBy('unit_no')
                    ->get(['schedule_id', 'unit_no', 'label', 'unit_id']);

                foreach ($optionRows as $item) {
                    $scheduleId = (int) ($item->schedule_id ?? 0);
                    if ($scheduleId <= 0) {
                        continue;
                    }

                    if (! isset($optionsBySchedule[$scheduleId])) {
                        $optionsBySchedule[$scheduleId] = [];
                    }

                    $resolvedUnitId = $item->unit_id !== null ? (int) $item->unit_id : null;
                    if ($resolvedUnitId !== null && $resolvedUnitId > 0) {
                        $unitIds[] = $resolvedUnitId;
                    }

                    $optionsBySchedule[$scheduleId][] = [
                        'unit_no' => (int) ($item->unit_no ?? 0),
                        'label' => trim((string) ($item->label ?? '')),
                        'unit_id' => $resolvedUnitId,
                    ];
                }
            }
        }

        $unitMap = [];
        $unitIds = array_values(array_unique(array_filter($unitIds, static fn ($id) => (int) $id > 0)));
        if (! empty($unitIds)) {
            $unitMap = DB::table('units')
                ->whereIn('id', $unitIds)
                ->get(['id', 'kapasitas', 'layout', 'nopol'])
                ->keyBy(static fn ($row) => (int) ($row->id ?? 0))
                ->all();
        }

        $matchedSchedule = null;
        $matchedOption = null;
        foreach ($scheduleRows as $row) {
            $scheduleId = (int) ($row->id ?? 0);
            $options = $optionsBySchedule[$scheduleId] ?? [];
            $foundOption = collect($options)->first(static fn (array $option) => (int) ($option['unit_no'] ?? 0) === $unit);

            if ($foundOption) {
                $matchedSchedule = $row;
                $matchedOption = $foundOption;
                break;
            }

            if ($matchedSchedule === null) {
                $scheduleUnits = max(1, (int) ($row->units ?? 1));
                if ($unit <= $scheduleUnits) {
                    $matchedSchedule = $row;
                }
            }
        }

        if ($matchedSchedule === null && $scheduleRows->isNotEmpty()) {
            $matchedSchedule = $scheduleRows->first();
        }

        $currentSeat = $this->normalizeSeat((string) ($validated['current_seat'] ?? ''));
        if ($bookingId > 0) {
            $bookingQuery = DB::table('bookings')
                ->where('id', $bookingId);
            PoolScope::applyRouteIdentity($bookingQuery, $rute, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute');
            PoolScope::applyRouteScope($bookingQuery, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute');
            $bookingRow = $bookingQuery->first(['seat']);

            if ($bookingRow !== null) {
                $currentSeat = $this->normalizeSeat((string) ($bookingRow->seat ?? ''));
            }
        }

        $layoutSource = 'schedule_unit';
        $layoutWarning = null;
        $resolvedUnitId = (int) ($matchedOption['unit_id'] ?? ($matchedSchedule->unit_id ?? 0));
        $resolvedUnit = $resolvedUnitId > 0 ? ($unitMap[$resolvedUnitId] ?? null) : null;
        $layout = $this->decodeLayout($resolvedUnit->layout ?? null);
        if (empty($layout)) {
            $layoutSource = 'schedule';
            $layout = $this->decodeLayout($matchedSchedule->layout ?? null);
        }
        $seatCount = $this->countSeatsFromLayout($layout);
        if ($seatCount <= 0) {
            $seatCount = max(0, (int) ($resolvedUnit->kapasitas ?? 0));
        }

        if (($resolvedUnitId <= 0 || empty($layout)) && $this->tripAssignmentsHasArmadaId() && Schema::hasTable('trip_assignments') && Schema::hasTable('armadas')) {
            $assignmentQuery = DB::table('trip_assignments as t')
                ->leftJoin('armadas as a', 't.armada_id', '=', 'a.id')
                ->where('t.tanggal', $tanggal)
                ->where('t.jam', $jamSql)
                ->where('t.unit', $unit);
            PoolScope::applyRouteIdentity($assignmentQuery, $rute, '', 't.rute');
            PoolScope::applyRouteScope($assignmentQuery, '', 't.rute');
            $assignment = $assignmentQuery->first([
                    't.armada_id',
                    DB::raw('a.nopol as armada_nopol'),
                    DB::raw('a.kategori as armada_kategori'),
                ]);

            $armadaCategory = trim((string) ($assignment->armada_kategori ?? ''));
            if ($armadaCategory !== '') {
                $candidateCount = (int) DB::table('units')
                    ->whereRaw('UPPER(category) = ?', [strtoupper($armadaCategory)])
                    ->count();

                if ($candidateCount > 1) {
                    $layoutWarning = 'Layout keberangkatan belum bisa dipastikan otomatis karena jadwal untuk jam ini belum dikonfigurasi dan kategori armada memiliki lebih dari satu template kursi.';
                } elseif ($candidateCount === 1) {
                    $fallbackUnit = DB::table('units')
                        ->whereRaw('UPPER(category) = ?', [strtoupper($armadaCategory)])
                        ->first(['id', 'layout', 'kapasitas', 'nopol']);

                    if ($fallbackUnit) {
                        $fallbackLayout = $this->decodeLayout($fallbackUnit->layout ?? null);
                        $fallbackSeatCount = $this->countSeatsFromLayout($fallbackLayout);
                        if ($fallbackSeatCount <= 0) {
                            $fallbackSeatCount = max(0, (int) ($fallbackUnit->kapasitas ?? 0));
                        }

                        if (! empty($fallbackLayout) || $fallbackSeatCount > 0) {
                            $layoutSource = 'armada_category_fallback';
                            $resolvedUnitId = (int) ($fallbackUnit->id ?? 0);
                            $resolvedUnit = $fallbackUnit;
                            $layout = $fallbackLayout;
                            $seatCount = $fallbackSeatCount;
                            $layoutWarning = 'Layout diambil dari template kategori armada karena jadwal keberangkatan untuk jam ini belum dikonfigurasi.';
                        }
                    }
                } else {
                    $layoutWarning = 'Layout keberangkatan belum ditemukan pada Jadwal untuk jam ini.';
                }
            } else {
                $layoutWarning = 'Layout keberangkatan belum ditemukan pada Jadwal untuk jam ini.';
            }
        }

        if (empty($layout) && $seatCount <= 0) {
            $layoutSource = 'current_seat_only';
            $layoutWarning = $layoutWarning ?: 'Layout keberangkatan belum tersedia. Saat ini hanya kursi aktif penumpang yang bisa dipertahankan.';
        }

        $layoutSeatTokens = $this->extractSeatTokensFromLayoutData($layout, $seatCount);
        if ($currentSeat !== '' && ! in_array($currentSeat, $layoutSeatTokens, true)) {
            $layoutSeatTokens[] = $currentSeat;
            usort($layoutSeatTokens, [$this, 'compareSeatTokens']);
        }

        $bookedSeatQuery = DB::table('bookings')
            ->where('tanggal', $tanggal)
            ->where('jam', $jamSql)
            ->where('unit', $unit)
            ->where('status', '!=', 'canceled')
            ->when($bookingId > 0, static function ($query) use ($bookingId) {
                $query->where('id', '!=', $bookingId);
            });
        PoolScope::applyRouteIdentity($bookedSeatQuery, $rute, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute');
        PoolScope::applyRouteScope($bookedSeatQuery, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute');

        $bookedSeatTokens = $bookedSeatQuery
            ->pluck('seat')
            ->map(fn ($seat) => $this->normalizeSeat((string) $seat))
            ->filter(static fn ($seat) => $seat !== '')
            ->unique()
            ->sort(fn ($a, $b) => $this->compareSeatTokens((string) $a, (string) $b))
            ->values()
            ->all();

        $availableSeatTokens = array_values(array_filter(
            $layoutSeatTokens,
            static fn ($seat) => ! in_array($seat, $bookedSeatTokens, true)
        ));

        if ($currentSeat !== '' && ! in_array($currentSeat, $availableSeatTokens, true)) {
            $availableSeatTokens[] = $currentSeat;
            usort($availableSeatTokens, [$this, 'compareSeatTokens']);
        }

        return $this->ok([
            'layout' => $layout,
            'layout_seats' => $layoutSeatTokens,
            'booked_seats' => $bookedSeatTokens,
            'available_seats' => $availableSeatTokens,
            'current_seat' => $currentSeat,
            'total_seats' => max($seatCount, count($layoutSeatTokens)),
            'layout_source' => $layoutSource,
            'layout_warning' => $layoutWarning,
            'trip' => [
                'rute' => $rute,
                'tanggal' => $tanggal,
                'jam' => $jam,
                'unit' => $unit,
                'unit_id' => $resolvedUnitId > 0 ? $resolvedUnitId : null,
                'unit_label' => trim((string) ($matchedOption['label'] ?? ($matchedSchedule->unit_label ?? ''))),
                'nopol' => strtoupper(trim((string) ($resolvedUnit->nopol ?? ''))),
            ],
        ]);
    }

    public function emptyDeparture(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
        ]);

        if (! Schema::hasTable('trip_assignments')) {
            return $this->error('Tabel keberangkatan belum tersedia.', 422);
        }

        $schedule = $this->findScheduleForDeparture((string) $data['rute'], (string) $data['tanggal'], (string) $data['jam']);
        if (! $schedule) {
            return $this->error('Jadwal tidak ditemukan.', 422);
        }
        if (! PoolScope::canAccessRouteName((string) ($schedule->rute ?? $data['rute']))) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $unit = (int) $data['unit'];
        if ($unit > max(1, (int) ($schedule->units ?? 1))) {
            return $this->error('Unit tidak tersedia untuk jadwal ini.', 422);
        }

        $payload = [
            'rute' => (string) ($schedule->rute ?? $data['rute']),
            'tanggal' => (string) $data['tanggal'],
            'jam' => $data['jam'].':00',
            'unit' => $unit,
        ];

        if ($this->tripAssignmentsHasStatus()) {
            $payload['status'] = 'active';
        }

        $existing = $this->findTripAssignment($payload['rute'], $payload['tanggal'], substr((string) $payload['jam'], 0, 5), $unit);
        $existingStatus = strtolower(trim((string) ($existing->status ?? 'active')));
        if ($existing && $existingStatus !== 'canceled') {
            return $this->error('Keberangkatan untuk rute, tanggal, jam, dan unit ini sudah ada.', 409);
        }

        if ($this->hasActiveBookingForDeparture($payload['rute'], $payload['tanggal'], substr((string) $payload['jam'], 0, 5), $unit)) {
            return $this->error('Keberangkatan untuk rute, tanggal, jam, dan unit ini sudah memiliki data penumpang.', 409);
        }

        if ($existing) {
            DB::table('trip_assignments')->where('id', (int) $existing->id)->update($payload);
            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('trip_assignments')->insertGetId(array_merge($payload, ['created_at' => now()]));
        }

        ActivityLog::write(
            'BOOKING',
            'Jadwal tanpa penumpang '.$payload['rute'].' ditambahkan',
            'Tanggal '.$payload['tanggal'].' | Jam '.substr((string) $payload['jam'], 0, 5).' | Unit '.$unit.' | BOP Rp '.number_format((float) ($schedule->bop ?? 0), 0, ',', '.'),
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            [
                'trip_assignment_id' => $id,
                'rute' => $payload['rute'],
                'tanggal' => $payload['tanggal'],
                'jam' => substr((string) $payload['jam'], 0, 5),
                'unit' => $unit,
            ],
        );

        return $this->ok([
            'message' => 'Keberangkatan kosong berhasil dibuat.',
            'id' => $id,
            'rute' => $payload['rute'],
            'tanggal' => $payload['tanggal'],
            'jam' => substr((string) $payload['jam'], 0, 5),
            'unit' => $unit,
            'bop' => (float) ($schedule->bop ?? 0),
            'status' => 'active',
        ], $existing ? 200 : 201);
    }

    public function cancelDeparture(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
        ]);

        if (! Schema::hasTable('trip_assignments')) {
            return $this->error('Tabel keberangkatan belum tersedia.', 422);
        }

        $schedule = $this->findScheduleForDeparture((string) $data['rute'], (string) $data['tanggal'], (string) $data['jam']);
        $routeName = (string) ($schedule->rute ?? $data['rute']);
        if (! PoolScope::canAccessRouteName($routeName)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }
        $unit = (int) $data['unit'];

        $payload = [
            'rute' => $routeName,
            'tanggal' => (string) $data['tanggal'],
            'jam' => $data['jam'].':00',
            'unit' => $unit,
        ];

        if ($this->tripAssignmentsHasStatus()) {
            $payload['status'] = 'canceled';
        }

        $assignmentMetaReset = [];
        if (Schema::hasColumn('trip_assignments', 'driver_id')) {
            $assignmentMetaReset['driver_id'] = null;
        }
        if ($this->tripAssignmentsHasArmadaId()) {
            $assignmentMetaReset['armada_id'] = null;
        }
        if ($this->tripAssignmentsHasArmadaNopol()) {
            $assignmentMetaReset['armada_nopol'] = null;
        }

        $existing = $this->findTripAssignment($routeName, $payload['tanggal'], substr((string) $payload['jam'], 0, 5), $unit);
        $existingStatus = strtolower(trim((string) ($existing->status ?? 'active')));
        if ($existingStatus === 'arrived') {
            return $this->error('Keberangkatan yang sudah tiba tidak bisa dibatalkan.', 409);
        }

        if ($existing) {
            DB::table('trip_assignments')->where('id', (int) $existing->id)->update(array_merge($payload, $assignmentMetaReset));
            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('trip_assignments')->insertGetId(array_merge($payload, $assignmentMetaReset, ['created_at' => now()]));
        }

        ActivityLog::write(
            'CANCEL',
            'Jadwal '.$routeName.' dibatalkan',
            'Tanggal '.$payload['tanggal'].' | Jam '.substr((string) $payload['jam'], 0, 5).' | Unit '.$unit.' | BOP Rp 0 | Driver & Nopol dikosongkan',
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            [
                'trip_assignment_id' => $id,
                'rute' => $routeName,
                'tanggal' => $payload['tanggal'],
                'jam' => substr((string) $payload['jam'], 0, 5),
                'unit' => $unit,
            ],
        );

        return $this->ok([
            'message' => 'Jadwal keberangkatan dibatalkan. Driver dan nopol dikosongkan.',
            'id' => $id,
            'status' => 'canceled',
            'bop' => 0,
            'driver_name' => '-',
            'armada_nopol' => '-',
        ]);
    }

    public function arriveDeparture(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
        ]);

        if (! Schema::hasTable('trip_assignments')) {
            return $this->error('Tabel keberangkatan belum tersedia.', 422);
        }

        $schedule = $this->findScheduleForDeparture((string) $data['rute'], (string) $data['tanggal'], (string) $data['jam']);
        $routeName = (string) ($schedule->rute ?? $data['rute']);
        if (! PoolScope::canAccessRouteName($routeName)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }
        $unit = (int) $data['unit'];

        $payload = [
            'rute' => $routeName,
            'tanggal' => (string) $data['tanggal'],
            'jam' => $data['jam'].':00',
            'unit' => $unit,
        ];

        if ($this->tripAssignmentsHasStatus()) {
            $payload['status'] = 'arrived';
        }

        $existing = $this->findTripAssignment($routeName, $payload['tanggal'], substr((string) $payload['jam'], 0, 5), $unit);
        $existingStatus = strtolower(trim((string) ($existing->status ?? 'active')));

        if ($existingStatus === 'canceled') {
            return $this->error('Jadwal yang sudah dibatalkan tidak bisa ditandai tiba.', 409);
        }

        if (! $this->departureHasRequiredAssignmentMeta($existing)) {
            return $this->error('Armada hanya bisa ditandai tiba jika Driver dan Nopol sudah diisi.', 422);
        }

        if (! $this->departureCanBeMarkedArrived((string) $data['tanggal'], (string) $data['jam'])) {
            return $this->error('Armada hanya bisa ditandai tiba pada hari keberangkatan atau setelahnya.', 422);
        }

        if ($existing) {
            DB::table('trip_assignments')->where('id', (int) $existing->id)->update($payload);
            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('trip_assignments')->insertGetId(array_merge($payload, ['created_at' => now()]));
        }

        $actor = (string) ($request->user()?->email ?? $request->user()?->name ?? 'system');
        $arrivedLuggageCount = $this->markMappedLuggagesArrived(
            $id,
            $routeName,
            $payload['tanggal'],
            substr((string) $payload['jam'], 0, 5),
            $unit,
            $actor,
        );

        ActivityLog::write(
            'BOOKING',
            'Armada untuk jadwal '.$this->departureIdentityLabel($routeName, $payload['tanggal'], substr((string) $payload['jam'], 0, 5), $unit).' sudah tiba',
            'Status keberangkatan: active -> arrived'.($arrivedLuggageCount > 0 ? ' | Bagasi ikut tiba: '.$arrivedLuggageCount : ''),
            $actor,
            [
                'trip_assignment_id' => $id,
                'rute' => $routeName,
                'tanggal' => $payload['tanggal'],
                'jam' => substr((string) $payload['jam'], 0, 5),
                'unit' => $unit,
                'luggage_arrived_count' => $arrivedLuggageCount,
            ],
        );

        return $this->ok([
            'message' => 'Armada berhasil ditandai sudah tiba.',
            'id' => $id,
            'status' => 'arrived',
            'bop' => (float) ($schedule->bop ?? 0),
            'luggage_arrived_count' => $arrivedLuggageCount,
        ]);
    }

    public function departureRiturs(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        if (! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return $this->ok([
                'mapped_luggages' => [],
                'available_luggages' => [],
                'trip_assignment_id' => null,
            ]);
        }

        $routeName = (string) $data['rute'];
        $tanggal = (string) $data['tanggal'];
        $jam = (string) $data['jam'];
        $unit = (int) $data['unit'];
        $query = trim((string) ($data['q'] ?? ''));
        if (! PoolScope::canAccessRouteName($routeName)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }
        $assignment = Schema::hasTable('trip_assignments')
            ? $this->findTripAssignment($routeName, $tanggal, $jam, $unit)
            : null;
        $assignmentId = (int) ($assignment->id ?? 0);
        $assignmentIds = $this->matchingTripAssignmentIds($routeName, $tanggal, $jam, $unit, $assignmentId);
        $assignmentStatus = strtolower(trim((string) ($assignment->status ?? 'active')));

        if ($assignmentId > 0 && $assignmentStatus === 'arrived') {
            $this->markMappedLuggagesArrived(
                $assignmentId,
                $routeName,
                $tanggal,
                $jam,
                $unit,
                (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            );
        }

        $mappedRows = collect();
        if ($assignmentIds !== []) {
            $mappedQuery = DB::table('luggages')
                ->whereIn('trip_assignment_id', $assignmentIds)
                ->orderByDesc('id');
            $this->applyLuggagePoolScope($mappedQuery);

            $mappedRows = $mappedQuery->get([
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
                ]);

            $mappedRows = $mappedRows
                ->map(function ($row) {
                    $row->status = $this->normalizeLuggageStatus($row->status ?? null);

                    return $row;
                })
                ->values();
        }

        $availableQuery = DB::table('luggages')
            ->where(function ($builder) {
                $this->applyLuggageStatusFilter($builder, 'status', $this->luggageReceivedStatuses());
            })
            ->whereNull('trip_assignment_id');
        $this->applyLuggagePoolScope($availableQuery);

        if ($query !== '') {
            $like = '%'.$query.'%';
            $availableQuery->where(function ($builder) use ($like) {
                $builder
                    ->where('kode_resi', 'like', $like)
                    ->orWhere('sender_name', 'like', $like)
                    ->orWhere('sender_phone', 'like', $like)
                    ->orWhere('receiver_name', 'like', $like)
                    ->orWhere('receiver_phone', 'like', $like)
                    ->orWhere('rute', 'like', $like)
                    ->orWhere('notes', 'like', $like);
            });
        }

        $targetRoute = $this->normalizeRouteName($routeName);
        $availableRows = $availableQuery
            ->orderByDesc('id')
            ->limit($query !== '' ? 200 : 48)
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
            ->filter(function ($row) use ($targetRoute) {
                return $this->normalizeRouteName((string) ($row->rute ?? '')) === $targetRoute;
            })
            ->map(function ($row) {
                $row->status = $this->normalizeLuggageStatus($row->status ?? null);

                return $row;
            })
            ->values();

        return $this->ok([
            'trip_assignment_id' => $assignmentId > 0 ? $assignmentId : null,
            'mapped_luggages' => $mappedRows->values()->all(),
            'available_luggages' => $availableRows->values()->all(),
        ]);
    }

    public function mapDepartureRitur(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
            'luggage_id' => ['required', 'integer', 'min:1'],
        ]);

        if (! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return $this->error('Fitur mapping bagasi belum siap. Jalankan migrasi terlebih dahulu.', 422);
        }

        if (! PoolScope::canAccessRouteName((string) $data['rute'])) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $luggageQuery = DB::table('luggages')->where('id', (int) $data['luggage_id']);
        $this->applyLuggagePoolScope($luggageQuery);
        $luggage = $luggageQuery->first([
            'id',
            'kode_resi',
            'sender_name',
            'receiver_name',
            'status',
            'trip_assignment_id',
            'price',
        ]);

        if (! $luggage) {
            return $this->error('Bagasi tidak ditemukan.', 404);
        }

        $luggageStatus = strtolower(trim((string) ($luggage->status ?? '')));
        if (! in_array($luggageStatus, $this->luggageReceivedStatuses(), true)) {
            return $this->error('Hanya bagasi dengan status '.$this->luggageReceivedStatus().' yang bisa dimapping ke keberangkatan.', 422);
        }

        $assignment = $this->ensureTripAssignmentForDeparture(
            (string) $data['rute'],
            (string) $data['tanggal'],
            (string) $data['jam'],
            (int) $data['unit'],
        );

        $currentAssignmentId = (int) ($luggage->trip_assignment_id ?? 0);
        if ($currentAssignmentId > 0 && $currentAssignmentId !== (int) $assignment->id) {
            return $this->error('Bagasi ini sudah dipasang ke keberangkatan lain.', 409);
        }

        $assignmentStatus = strtolower(trim((string) ($assignment->status ?? 'active')));
        $nextStatus = $assignmentStatus === 'arrived'
            ? $this->luggageArrivedStatus()
            : $this->luggagePickedUpStatus();
        $actor = (string) ($request->user()?->email ?? $request->user()?->name ?? 'system');
        $departureLabel = $this->departureIdentityLabel((string) $assignment->rute, (string) $assignment->tanggal, substr((string) $assignment->jam, 0, 5), (int) $assignment->unit);

        DB::table('luggages')
            ->where('id', (int) $luggage->id)
            ->update([
                'trip_assignment_id' => (int) $assignment->id,
                'status' => $nextStatus,
            ]);
        $this->appendLuggageTrackingLog(
            (string) ($luggage->kode_resi ?? ''),
            $nextStatus,
            ($assignmentStatus === 'arrived' ? 'Bagasi tiba bersama keberangkatan ' : 'Bagasi dipickup ke keberangkatan ').$departureLabel,
            $actor,
        );

        ActivityLog::write(
            'BOOKING',
            'Bagasi '.trim((string) ($luggage->kode_resi ?? '')).' dimapping ke keberangkatan '.$departureLabel,
            'Pengirim: '.trim((string) ($luggage->sender_name ?? '-')).' | Penerima: '.trim((string) ($luggage->receiver_name ?? '-')).' | Status: '.$this->luggageReceivedStatus().' -> '.$nextStatus.' | Revenue: Rp '.number_format((float) ($luggage->price ?? 0), 0, ',', '.'),
            $actor,
            [
                'trip_assignment_id' => (int) $assignment->id,
                'luggage_id' => (int) $luggage->id,
                'kode_resi' => (string) ($luggage->kode_resi ?? ''),
                'luggage_status' => $nextStatus,
            ],
        );

        return $this->ok([
            'message' => 'Bagasi berhasil dimapping ke keberangkatan.',
            'trip_assignment_id' => (int) $assignment->id,
            'luggage_id' => (int) $luggage->id,
        ]);
    }

    public function unmapDepartureRitur(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['required', 'integer', 'min:1'],
            'luggage_id' => ['required', 'integer', 'min:1'],
        ]);

        if (! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return $this->error('Fitur mapping bagasi belum siap. Jalankan migrasi terlebih dahulu.', 422);
        }

        if (! PoolScope::canAccessRouteName((string) $data['rute'])) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }

        $assignment = Schema::hasTable('trip_assignments')
            ? $this->findTripAssignment((string) $data['rute'], (string) $data['tanggal'], (string) $data['jam'], (int) $data['unit'])
            : null;
        $assignmentId = (int) ($assignment->id ?? 0);
        if ($assignmentId <= 0) {
            return $this->error('Keberangkatan belum punya mapping bagasi.', 404);
        }

        $luggageQuery = DB::table('luggages')->where('id', (int) $data['luggage_id']);
        $this->applyLuggagePoolScope($luggageQuery);
        $luggage = $luggageQuery->first([
            'id',
            'kode_resi',
            'sender_name',
            'receiver_name',
            'trip_assignment_id',
            'price',
        ]);

        if (! $luggage) {
            return $this->error('Bagasi tidak ditemukan.', 404);
        }

        if ((int) ($luggage->trip_assignment_id ?? 0) !== $assignmentId) {
            return $this->error('Bagasi ini tidak terpasang pada keberangkatan yang dipilih.', 409);
        }

        DB::table('luggages')
            ->where('id', (int) $luggage->id)
            ->update([
                'trip_assignment_id' => null,
                'status' => $this->luggageReceivedStatus(),
            ]);
        $this->appendLuggageTrackingLog(
            (string) ($luggage->kode_resi ?? ''),
            $this->luggageReceivedStatus(),
            'Mapping bagasi dilepas dari keberangkatan '.$this->departureIdentityLabel((string) $assignment->rute, (string) $assignment->tanggal, substr((string) $assignment->jam, 0, 5), (int) $assignment->unit),
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
        );

        ActivityLog::write(
            'BOOKING',
            'Mapping bagasi '.trim((string) ($luggage->kode_resi ?? '')).' dilepas dari '.$this->departureIdentityLabel((string) $assignment->rute, (string) $assignment->tanggal, substr((string) $assignment->jam, 0, 5), (int) $assignment->unit),
            'Pengirim: '.trim((string) ($luggage->sender_name ?? '-')).' | Penerima: '.trim((string) ($luggage->receiver_name ?? '-')).' | Status: '.$this->luggagePickedUpStatus().' -> '.$this->luggageReceivedStatus().' | Revenue: Rp '.number_format((float) ($luggage->price ?? 0), 0, ',', '.'),
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            [
                'trip_assignment_id' => $assignmentId,
                'luggage_id' => (int) $luggage->id,
                'kode_resi' => (string) ($luggage->kode_resi ?? ''),
            ],
        );

        return $this->ok([
            'message' => 'Mapping bagasi berhasil dilepas.',
            'luggage_id' => (int) $luggage->id,
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rute' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'gmaps' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'pembayaran' => ['nullable', 'string', 'max:50'],
            'segment_id' => ['nullable', 'integer', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'seat' => ['nullable', 'string', 'max:20'],
            'seats' => ['nullable', 'array'],
            'seats.*' => ['nullable', 'string', 'max:20'],
        ]);

        $rute = trim((string) $data['rute']);
        $tanggal = $data['tanggal'];
        $jamSql = $this->normalizeTime((string) $data['jam']);
        $unit = max(1, (int) ($data['unit'] ?? 1));
        $name = strtoupper(trim((string) $data['name']));
        $phone = $this->normalizePhone((string) $data['phone']);
        $pickupPoint = trim((string) ($data['pickup_point'] ?? ''));
        $address = trim((string) ($data['gmaps'] ?? $data['address'] ?? ''));
        $payment = $this->normalizePayment((string) ($data['pembayaran'] ?? 'Belum Lunas'));
        $segmentId = (int) ($data['segment_id'] ?? 0);
        $discount = max(0, (float) ($data['discount'] ?? 0));
        $price = $this->segmentPrice($segmentId);

        if (! PoolScope::canAccessRouteName($rute)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }
        $routeId = PoolScope::routeIdForName($rute);

        $seats = $data['seats'] ?? null;
        if (! is_array($seats)) {
            $single = $this->normalizeSeat((string) ($data['seat'] ?? ''));
            $seats = $single !== '' ? [$single] : [];
        }
        $seats = array_values(array_unique(array_filter(array_map(fn ($seat) => $this->normalizeSeat((string) $seat), $seats))));

        if (empty($seats)) {
            return $this->error('no_seats', 422);
        }
        foreach ($seats as $seat) {
            if (! preg_match('/^[A-Z0-9-]{1,20}$/', $seat)) {
                return $this->error('invalid_seat', 422, ['seat' => $seat]);
            }
        }

        $discountPerSeat = count($seats) > 0 ? ($discount / count($seats)) : 0;
        $createdByUserId = $request->user()?->id;
        $createdByUsername = $request->user()?->name ?: $request->user()?->email ?: 'System';

        try {
            $result = DB::transaction(function () use (
                $rute,
                $tanggal,
                $jamSql,
                $unit,
                $seats,
                $name,
                $phone,
                $pickupPoint,
                $address,
                $payment,
                $segmentId,
                $price,
                $discountPerSeat,
                $createdByUserId,
                $createdByUsername,
                $routeId,
            ) {
                $supportsDepartureCode = $this->bookingsHasDepartureCode();
                $supportsTicketCode = $this->bookingsHasTicketCode();
                $departureCode = BookingCode::departureCode($tanggal, substr($jamSql, 0, 5), $unit, $rute);
                $conflictQuery = DB::table('bookings')
                    ->where('tanggal', $tanggal)
                    ->where('jam', $jamSql)
                    ->where('unit', $unit)
                    ->where('status', '!=', 'canceled')
                    ->whereIn('seat', $seats);
                PoolScope::applyRouteIdentity($conflictQuery, $rute, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute', $routeId);
                $conflict = $conflictQuery
                    ->lockForUpdate()
                    ->pluck('seat')
                    ->map(static fn ($seat) => (string) $seat)
                    ->values()
                    ->all();

                if (! empty($conflict)) {
                    return ['conflict' => $conflict];
                }

                $ids = [];
                $records = [];
                foreach ($seats as $seat) {
                    $insert = [
                        'rute' => $rute,
                        'tanggal' => $tanggal,
                        'jam' => $jamSql,
                        'unit' => $unit,
                        'departure_code' => $supportsDepartureCode ? $departureCode : null,
                        'name' => $name,
                        'phone' => $phone,
                        'pickup_point' => $pickupPoint,
                        'pembayaran' => $payment,
                        'status' => 'active',
                        'segment_id' => $segmentId > 0 ? $segmentId : null,
                        'price' => $segmentId > 0 ? $price : 0,
                        'discount' => $discountPerSeat,
                        'created_by_user_id' => $createdByUserId,
                        'created_by_username' => $createdByUsername,
                        'created_at' => now(),
                        'seat' => $seat,
                    ];
                    if ($this->bookingsHasRouteId()) {
                        $insert['route_id'] = $routeId > 0 ? $routeId : null;
                    }
                    if (! $supportsDepartureCode) {
                        unset($insert['departure_code']);
                    }

                    $id = DB::table('bookings')->insertGetId($insert);
                    $ids[] = $id;

                    $ticketCode = BookingCode::ticketCode($id, $tanggal);
                    if ($supportsTicketCode) {
                        DB::table('bookings')->where('id', $id)->update(['ticket_code' => $ticketCode]);
                    }

                    $records[] = [
                        'id' => $id,
                        'seat' => $seat,
                        'departure_code' => $departureCode,
                        'ticket_code' => $ticketCode,
                    ];
                }

                $this->upsertCustomer($name, $phone, $pickupPoint, $address, $routeId);

                return ['ids' => $ids, 'records' => $records, 'departure_code' => $departureCode];
            }, 3);
        } catch (\Throwable $e) {
            return $this->error('exception', 500, ['detail' => $e->getMessage()]);
        }

        if (! empty($result['conflict'])) {
            return $this->error('conflict', 409, ['conflict' => $result['conflict']]);
        }

        ActivityLog::write(
            'BOOKING',
            'Booking baru: '.$name.' ('.count($seats).' kursi)',
            'Rute '.$rute.' | '.substr($jamSql, 0, 5).' | Unit '.$unit,
            (string) $createdByUsername,
            ['booking_ids' => $result['ids']],
        );

        return $this->ok([
            'added' => count($seats),
            'booking_ids' => $result['ids'],
            'booking_records' => $result['records'] ?? [],
            'departure_code' => $result['departure_code'] ?? BookingCode::departureCode($tanggal, substr($jamSql, 0, 5), $unit, $rute),
            'rute' => $rute,
            'tanggal' => $tanggal,
            'jam' => substr($jamSql, 0, 5),
            'unit' => $unit,
        ], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'id' => ['nullable', 'integer', 'min:1'],
            'rute' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'jam' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['nullable', 'integer', 'min:1'],
            'current_seat' => ['nullable', 'string', 'max:20'],
            'seat' => ['nullable', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'gmaps' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'pembayaran' => ['nullable', 'string', 'max:50'],
            'segment_id' => ['nullable', 'integer', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $current = $this->resolveBooking($payload, true);
        if (! $current) {
            return $this->error('booking_not_found', 404);
        }

        $id = (int) $current['id'];
        $isCanceled = strtolower((string) ($current['status'] ?? '')) === 'canceled';
        $paymentOnlyUpdate = array_key_exists('pembayaran', $payload)
            && ! array_key_exists('rute', $payload)
            && ! array_key_exists('tanggal', $payload)
            && ! array_key_exists('jam', $payload)
            && ! array_key_exists('unit', $payload)
            && ! array_key_exists('current_seat', $payload)
            && ! array_key_exists('seat', $payload)
            && ! array_key_exists('name', $payload)
            && ! array_key_exists('phone', $payload)
            && ! array_key_exists('pickup_point', $payload)
            && ! array_key_exists('gmaps', $payload)
            && ! array_key_exists('address', $payload)
            && ! array_key_exists('segment_id', $payload)
            && ! array_key_exists('discount', $payload);
        $targetRoute = trim((string) ($payload['rute'] ?? $current['rute'] ?? ''));
        $targetDate = (string) ($payload['tanggal'] ?? $current['tanggal'] ?? '');
        $targetJam = array_key_exists('jam', $payload)
            ? $this->normalizeTime((string) ($payload['jam'] ?? ''))
            : (string) ($current['jam'] ?? '');
        $targetUnit = array_key_exists('unit', $payload)
            ? max(1, (int) ($payload['unit'] ?? 1))
            : max(1, (int) ($current['unit'] ?? 1));
        $name = strtoupper(trim((string) ($payload['name'] ?? $current['name'] ?? '')));
        $phone = $this->normalizePhone((string) ($payload['phone'] ?? $current['phone'] ?? ''));
        $seat = $this->normalizeSeat((string) ($payload['seat'] ?? $current['seat'] ?? ''));
        $pickupPoint = trim((string) ($payload['pickup_point'] ?? $current['pickup_point'] ?? ''));
        $address = trim((string) ($payload['gmaps'] ?? $payload['address'] ?? ($current['address'] ?? '')));
        $segmentId = array_key_exists('segment_id', $payload) ? (int) ($payload['segment_id'] ?? 0) : (int) ($current['segment_id'] ?? 0);
        $discount = array_key_exists('discount', $payload) ? max(0, (float) ($payload['discount'] ?? 0)) : (float) ($current['discount'] ?? 0);
        $payment = $this->normalizePayment((string) ($payload['pembayaran'] ?? ($current['pembayaran'] ?? 'Belum Lunas')));

        if (! PoolScope::canAccessRouteName($targetRoute)) {
            return $this->error('Anda tidak memiliki akses ke rute ini.', 403);
        }
        $targetRouteId = PoolScope::routeIdForName($targetRoute);

        if ($isCanceled && ! $paymentOnlyUpdate) {
            return $this->error('Booking cancel hanya bisa diubah status pembayarannya.', 422);
        }

        if ($paymentOnlyUpdate) {
            $paymentChangeSummary = $this->bookingChangeSummary($current, [
                'pembayaran' => $payment,
            ]);

            DB::table('bookings')
                ->where('id', $id)
                ->update([
                    'pembayaran' => $payment,
                ]);

            $bookingIdentity = $this->bookingIdentityLabel($current);

            ActivityLog::write(
                'PAYMENT',
                'Pembayaran booking '.$bookingIdentity.' diperbarui',
                $paymentChangeSummary,
                (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
                ['booking_id' => $id],
            );

            return $this->ok([
                'booking_id' => $id,
                'seat' => (string) ($current['seat'] ?? ''),
                'message' => 'Status pembayaran booking berhasil diperbarui',
                'rute' => (string) ($current['rute'] ?? ''),
                'tanggal' => (string) ($current['tanggal'] ?? ''),
                'jam' => substr((string) ($current['jam'] ?? ''), 0, 5),
                'unit' => (int) ($current['unit'] ?? 1),
                'pembayaran' => $payment,
            ]);
        }

        if ($id <= 0 || $targetRoute === '' || $targetDate === '' || $targetJam === '' || $name === '' || $phone === '' || $pickupPoint === '' || $seat === '') {
            return $this->error('missing_fields', 422);
        }
        if (! preg_match('/^[A-Z0-9-]{1,20}$/', $seat)) {
            return $this->error('invalid_seat', 422);
        }

        $tripChanged = $targetRoute !== (string) ($current['rute'] ?? '')
            || $targetDate !== (string) ($current['tanggal'] ?? '')
            || $targetJam !== (string) ($current['jam'] ?? '')
            || $targetUnit !== (int) ($current['unit'] ?? 1);

        $conflictQuery = DB::table('bookings')
            ->where('tanggal', $targetDate)
            ->where('jam', $targetJam)
            ->where('unit', $targetUnit)
            ->where('seat', $seat)
            ->where('status', '!=', 'canceled')
            ->where('id', '!=', $id);
        PoolScope::applyRouteIdentity($conflictQuery, $targetRoute, $this->bookingsHasRouteId() ? 'route_id' : '', 'rute', $targetRouteId);
        $conflict = $conflictQuery
            ->exists();

        if ($conflict) {
            return $this->error('Kursi sudah terpakai pada keberangkatan ini', 409);
        }

        $price = $segmentId > 0 ? $this->segmentPrice($segmentId) : 0;
        $departureCode = BookingCode::departureCode($targetDate, substr($targetJam, 0, 5), $targetUnit, $targetRoute);

        $updatePayload = [
            'rute' => $targetRoute,
            'tanggal' => $targetDate,
            'jam' => $targetJam,
            'unit' => $targetUnit,
            'seat' => $seat,
            'name' => $name,
            'phone' => $phone,
            'pickup_point' => $pickupPoint,
            'pembayaran' => $payment,
            'segment_id' => $segmentId > 0 ? $segmentId : null,
            'price' => $price,
            'discount' => $discount,
        ];
        if ($this->bookingsHasDepartureCode()) {
            $updatePayload['departure_code'] = $departureCode;
        }
        if ($this->bookingsHasRouteId()) {
            $updatePayload['route_id'] = $targetRouteId > 0 ? $targetRouteId : null;
        }

        DB::table('bookings')
            ->where('id', $id)
            ->where('status', '!=', 'canceled')
            ->update($updatePayload);

        $this->upsertCustomer($name, $phone, $pickupPoint, $address, $targetRouteId);

        $changeSummary = $this->bookingChangeSummary($current, [
            'rute' => $targetRoute,
            'tanggal' => $targetDate,
            'jam' => $targetJam,
            'unit' => $targetUnit,
            'seat' => $seat,
            'name' => $name,
            'phone' => $phone,
            'pickup_point' => $pickupPoint,
            'address' => $address,
            'pembayaran' => $payment,
            'segment_id' => $segmentId > 0 ? $segmentId : null,
            'price' => $price,
            'discount' => $discount,
        ]);
        $bookingIdentity = $this->bookingIdentityLabel([
            'name' => $name,
            'phone' => $phone,
            'seat' => $seat,
        ]);

        ActivityLog::write(
            'BOOKING',
            'Booking '.$bookingIdentity.' diperbarui',
            $changeSummary,
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            ['booking_id' => $id],
        );

        return $this->ok([
            'booking_id' => $id,
            'seat' => $seat,
            'message' => 'Booking berhasil diperbarui',
            'departure_code' => $departureCode,
            'trip_changed' => $tripChanged,
            'rute' => $targetRoute,
            'tanggal' => $targetDate,
            'jam' => substr($targetJam, 0, 5),
            'unit' => $targetUnit,
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'id' => ['nullable', 'integer', 'min:1'],
            'rute' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'jam' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'unit' => ['nullable', 'integer', 'min:1'],
            'seat' => ['nullable', 'string', 'max:20'],
            'reason' => ['nullable', 'string'],
        ]);

        $current = $this->resolveBooking($payload, false);
        if (! $current || ($current['status'] ?? '') === 'canceled') {
            return $this->error('booking_not_found', 404);
        }

        DB::table('bookings')
            ->where('id', (int) $current['id'])
            ->where('status', '!=', 'canceled')
            ->update([
                'status' => 'canceled',
            ]);

        DB::table('cancellations')->insert([
            'booking_id' => (int) $current['id'],
            'admin_user' => $request->user()?->email ?? $request->user()?->name ?? 'system',
            'reason' => trim((string) ($payload['reason'] ?? '')),
            'created_at' => now(),
        ]);

        $bookingIdentity = $this->bookingIdentityLabel($current);
        $tripSummary = $this->bookingTripSummaryFromData($current);
        $reason = trim((string) ($payload['reason'] ?? '')) ?: 'Tanpa alasan';
        $cancelSummary = $this->buildSummaryParts([
            $this->formatChangeLine('Status', (string) ($current['status'] ?? 'active'), 'canceled'),
            $tripSummary,
            'Alasan: '.$reason,
        ], ' | ');

        ActivityLog::write(
            'CANCEL',
            'Booking '.$bookingIdentity.' dibatalkan',
            $cancelSummary,
            (string) ($request->user()?->email ?? $request->user()?->name ?? 'system'),
            [
                'booking_id' => (int) $current['id'],
                'rute' => (string) $current['rute'],
                'tanggal' => (string) $current['tanggal'],
                'jam' => substr((string) $current['jam'], 0, 5),
                'unit' => (int) $current['unit'],
            ],
        );

        return $this->ok([
            'booking_id' => (int) $current['id'],
            'message' => 'Booking berhasil dibatalkan',
            'rute' => $current['rute'],
            'tanggal' => $current['tanggal'],
            'jam' => substr((string) $current['jam'], 0, 5),
            'unit' => (int) $current['unit'],
        ]);
    }

    private function resolveBooking(array $payload, bool $isUpdate): ?array
    {
        $bookingId = (int) ($payload['booking_id'] ?? $payload['id'] ?? 0);
        if ($bookingId > 0) {
            $query = DB::table('bookings as b')
                ->leftJoin('customers as c', 'c.phone', '=', 'b.phone')
                ->where('b.id', $bookingId);
            PoolScope::applyRouteScope($query, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');

            $row = $query->first([
                    'b.id',
                    $this->bookingsHasRouteId() ? 'b.route_id' : DB::raw('NULL as route_id'),
                    'b.seat',
                    'b.rute',
                    'b.tanggal',
                    'b.jam',
                    'b.unit',
                    'b.status',
                    'b.name',
                    'b.phone',
                    'b.pickup_point',
                    'b.pembayaran',
                    'b.segment_id',
                    'b.price',
                    'b.discount',
                    DB::raw('c.gmaps as address'),
                ]);

            return $row ? (array) $row : null;
        }

        $rute = trim((string) ($payload['rute'] ?? ''));
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));
        $jam = trim((string) ($payload['jam'] ?? ''));
        $unit = max(1, (int) ($payload['unit'] ?? 1));
        $seatKey = $isUpdate ? 'current_seat' : 'seat';
        $lookupSeat = $this->normalizeSeat((string) ($payload[$seatKey] ?? ''));

        if ($rute === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || ! preg_match('/^\d{2}:\d{2}$/', $jam) || $lookupSeat === '') {
            return null;
        }

        $query = DB::table('bookings as b')
            ->leftJoin('customers as c', 'c.phone', '=', 'b.phone')
            ->where('b.tanggal', $tanggal)
            ->where('b.jam', $this->normalizeTime($jam))
            ->where('b.unit', $unit)
            ->where('b.seat', $lookupSeat);
        PoolScope::applyRouteIdentity($query, $rute, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');
        PoolScope::applyRouteScope($query, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');

        $row = $query->first([
                'b.id',
                $this->bookingsHasRouteId() ? 'b.route_id' : DB::raw('NULL as route_id'),
                'b.seat',
                'b.rute',
                'b.tanggal',
                'b.jam',
                'b.unit',
                'b.status',
                'b.name',
                'b.phone',
                'b.pickup_point',
                'b.pembayaran',
                'b.segment_id',
                'b.price',
                'b.discount',
                DB::raw('c.gmaps as address'),
            ]);

        return $row ? (array) $row : null;
    }

    private function bookingIdentityLabel(array $booking): string
    {
        $name = trim((string) ($booking['name'] ?? ''));
        $phone = trim((string) ($booking['phone'] ?? ''));
        $seat = $this->normalizeSeat((string) ($booking['seat'] ?? ''));

        if ($name !== '' && $seat !== '') {
            return $name.' (kursi '.$seat.')';
        }

        if ($name !== '') {
            return $name;
        }

        if ($phone !== '' && $seat !== '') {
            return $phone.' (kursi '.$seat.')';
        }

        if ($phone !== '') {
            return $phone;
        }

        if ($seat !== '') {
            return 'kursi '.$seat;
        }

        return 'penumpang';
    }

    private function bookingTripSummaryFromData(array $booking): string
    {
        return $this->bookingTripSummary(
            trim((string) ($booking['rute'] ?? '')),
            (string) ($booking['jam'] ?? ''),
            (int) ($booking['unit'] ?? 0),
        );
    }

    private function bookingTripSummary(string $route, string $time, int $unit): string
    {
        $summary = [];
        $timeLabel = substr(trim($time), 0, 5);

        if ($route !== '') {
            $summary[] = 'Rute '.$route;
        }

        if ($timeLabel !== '') {
            $summary[] = $timeLabel;
        }

        if ($unit > 0) {
            $summary[] = 'Unit '.$unit;
        }

        return implode(' | ', $summary);
    }

    private function bookingChangeSummary(array $before, array $after): string
    {
        $changes = [];

        $fieldMap = [
            'name' => 'Nama',
            'phone' => 'Telepon',
            'pickup_point' => 'Pickup',
            'address' => 'Alamat',
            'rute' => 'Rute',
            'tanggal' => 'Tanggal',
            'jam' => 'Jam',
            'unit' => 'Unit',
            'seat' => 'Kursi',
            'pembayaran' => 'Pembayaran',
            'discount' => 'Diskon',
            'price' => 'Harga',
        ];

        foreach ($fieldMap as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $beforeValue = $this->bookingFieldDisplayValue($field, $before[$field] ?? null);
            $afterValue = $this->bookingFieldDisplayValue($field, $after[$field]);

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[] = $this->formatChangeLine($label, $beforeValue, $afterValue);
        }

        if (! empty($changes)) {
            return implode(' | ', $changes);
        }

        $tripSummary = $this->bookingTripSummaryFromData($after + $before);

        return $tripSummary !== '' ? $tripSummary.' | Tidak ada perubahan data utama' : 'Tidak ada perubahan data utama';
    }

    private function bookingFieldDisplayValue(string $field, mixed $value): string
    {
        return match ($field) {
            'jam' => substr(trim((string) $value), 0, 5),
            'unit' => (string) max(0, (int) $value),
            'seat' => $this->normalizeSeat((string) $value),
            'phone' => trim((string) $value),
            'discount', 'price' => number_format((float) $value, 0, ',', '.'),
            default => trim((string) $value),
        };
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

    private function bookingsHasRouteId(): bool
    {
        if ($this->bookingsHasRouteId === null) {
            $this->bookingsHasRouteId = Schema::hasColumn('bookings', 'route_id');
        }

        return $this->bookingsHasRouteId;
    }

    private function tripAssignmentsHasArmadaId(): bool
    {
        if ($this->tripAssignmentsHasArmadaId === null) {
            $this->tripAssignmentsHasArmadaId = Schema::hasTable('trip_assignments')
                && Schema::hasColumn('trip_assignments', 'armada_id');
        }

        return $this->tripAssignmentsHasArmadaId;
    }

    private function tripAssignmentsHasArmadaNopol(): bool
    {
        if ($this->tripAssignmentsHasArmadaNopol === null) {
            $this->tripAssignmentsHasArmadaNopol = Schema::hasTable('trip_assignments')
                && Schema::hasColumn('trip_assignments', 'armada_nopol');
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

    private function applyLuggagePoolScope(Builder $query): void
    {
        PoolScope::applyPoolOrRouteScope(
            $query,
            Schema::hasColumn('luggages', 'pool_id') ? 'pool_id' : '',
            Schema::hasColumn('luggages', 'rute_id') ? 'rute_id' : '',
            'rute',
        );
    }

    private function findScheduleForDeparture(string $rute, string $tanggal, string $jam): ?object
    {
        if (! Schema::hasTable('schedules')) {
            return null;
        }

        $dow = Carbon::createFromFormat('Y-m-d', $tanggal)->dayOfWeek;
        $targetRoute = $this->normalizeRouteName($rute);
        $select = ['id', 'rute', 'dow', 'jam', 'units'];
        $select[] = $this->schedulesHasBopColumn() ? 'bop' : DB::raw('0 as bop');

        $query = DB::table('schedules')
            ->where('dow', $dow)
            ->where('jam', $this->normalizeTime($jam))
            ->orderBy('id');
        PoolScope::applyRouteScope(
            $query,
            Schema::hasColumn('schedules', 'route_id') ? 'route_id' : '',
            'rute',
        );

        $rows = $query->get($select);

        return $rows->first(fn ($row) => $this->normalizeRouteName((string) ($row->rute ?? '')) === $targetRoute);
    }

    private function findTripAssignment(string $rute, string $tanggal, string $jam, int $unit): ?object
    {
        $targetRoute = $this->normalizeRouteName($rute);
        $select = [
            't.id',
            't.rute',
            't.tanggal',
            't.jam',
            't.unit',
            DB::raw('d.nama as driver_name'),
            $this->tripAssignmentsHasStatus() ? 't.status' : DB::raw("'active' as status"),
        ];

        if ($this->tripAssignmentsHasArmadaNopol()) {
            $select[] = 't.armada_nopol';
        }

        if ($this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas')) {
            $select[] = DB::raw('a.nopol as armada_nopol_fallback');
        }

        $query = DB::table('trip_assignments as t')
            ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id')
            ->when(
                $this->tripAssignmentsHasArmadaId() && Schema::hasTable('armadas'),
                static function ($query) {
                    $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
                },
            )
            ->where('tanggal', $tanggal)
            ->where('t.jam', $this->normalizeTime($jam))
            ->where('t.unit', $unit);
        PoolScope::applyRouteScope($query, '', 't.rute');

        $rows = $query->get($select);

        return $rows->first(fn ($row) => $this->normalizeRouteName((string) ($row->rute ?? '')) === $targetRoute);
    }

    private function matchingTripAssignmentIds(string $rute, string $tanggal, string $jam, int $unit, int $preferredId = 0): array
    {
        $ids = $preferredId > 0 ? [$preferredId] : [];

        if (! Schema::hasTable('trip_assignments')) {
            return $ids;
        }

        $targetRoute = $this->normalizeRouteName($rute);
        $query = DB::table('trip_assignments')
            ->where('tanggal', $tanggal)
            ->where('jam', $this->normalizeTime($jam))
            ->where('unit', $unit);
        PoolScope::applyRouteScope($query, '', 'rute');

        $rows = $query->get(['id', 'rute']);

        foreach ($rows as $row) {
            if ($this->normalizeRouteName((string) ($row->rute ?? '')) === $targetRoute) {
                $ids[] = (int) ($row->id ?? 0);
            }
        }

        return array_values(array_unique(array_filter($ids, static fn (int $id) => $id > 0)));
    }

    private function hasActiveBookingForDeparture(string $rute, string $tanggal, string $jam, int $unit): bool
    {
        if (! Schema::hasTable('bookings')) {
            return false;
        }

        $query = DB::table('bookings as b')
            ->where('b.tanggal', $tanggal)
            ->where('b.jam', $this->normalizeTime($jam))
            ->where('b.unit', $unit);

        if (Schema::hasColumn('bookings', 'status')) {
            $query->where(function (Builder $statusQuery): void {
                $statusQuery
                    ->whereNull('b.status')
                    ->orWhereNotIn('b.status', ['canceled', 'cancelled']);
            });
        }

        PoolScope::applyRouteIdentity($query, $rute, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');
        PoolScope::applyRouteScope($query, $this->bookingsHasRouteId() ? 'b.route_id' : '', 'b.rute');

        return $query->exists();
    }

    private function ensureTripAssignmentForDeparture(string $rute, string $tanggal, string $jam, int $unit): object
    {
        if (! Schema::hasTable('trip_assignments')) {
            throw ValidationException::withMessages([
                'rute' => 'Tabel keberangkatan belum tersedia.',
            ]);
        }

        $schedule = $this->findScheduleForDeparture($rute, $tanggal, $jam);
        $routeName = (string) ($schedule->rute ?? $rute);
        if (! PoolScope::canAccessRouteName($routeName)) {
            throw ValidationException::withMessages([
                'rute' => 'Anda tidak memiliki akses ke rute ini.',
            ]);
        }
        $existing = $this->findTripAssignment($routeName, $tanggal, $jam, $unit);
        $existingStatus = strtolower(trim((string) ($existing->status ?? 'active')));
        if ($existingStatus === 'canceled') {
            throw ValidationException::withMessages([
                'rute' => 'Keberangkatan yang dibatalkan tidak bisa dipakai untuk mapping bagasi.',
            ]);
        }

        if ($existing) {
            return $existing;
        }

        $payload = [
            'rute' => $routeName,
            'tanggal' => $tanggal,
            'jam' => $this->normalizeTime($jam),
            'unit' => max(1, $unit),
        ];

        if ($this->tripAssignmentsHasStatus()) {
            $payload['status'] = 'active';
        }

        $id = (int) DB::table('trip_assignments')->insertGetId(array_merge($payload, ['created_at' => now()]));

        return (object) array_merge($payload, [
            'id' => $id,
            'status' => (string) ($payload['status'] ?? 'active'),
        ]);
    }

    private function departureIdentityLabel(string $rute, string $tanggal, string $jam, int $unit): string
    {
        return trim($rute).' | '.$tanggal.' | '.substr($jam, 0, 5).' | Unit '.$unit;
    }

    private function departureCanBeMarkedArrived(string $tanggal, string $jam): bool
    {
        $datePart = substr(trim($tanggal), 0, 10);

        try {
            $today = now()->startOfDay();
            $departureDate = Carbon::createFromFormat('Y-m-d', $datePart)->startOfDay();

            return $departureDate->lte($today);
        } catch (\Throwable) {
            return false;
        }
    }

    private function departureHasRequiredAssignmentMeta(?object $assignment): bool
    {
        if (! $assignment) {
            return false;
        }

        $driverName = trim((string) ($assignment->driver_name ?? ''));
        $armadaNopol = trim((string) ($assignment->armada_nopol ?? ''));

        if ($armadaNopol === '') {
            $armadaNopol = trim((string) ($assignment->armada_nopol_fallback ?? ''));
        }

        return $this->hasMeaningfulAssignmentValue($driverName)
            && $this->hasMeaningfulAssignmentValue($armadaNopol);
    }

    private function hasMeaningfulAssignmentValue(string $value): bool
    {
        return ! in_array(
            strtolower(trim($value)),
            [
                '',
                '-',
                'null',
                'undefined',
                'n/a',
                'na',
                'belum diisi',
                'belum ada',
                'belum dipilih',
            ],
            true,
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
            default => [$normalized],
        };
    }

    private function luggageReceivedStatuses(): array
    {
        return $this->luggageStatusAliases($this->luggageReceivedStatus());
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

    private function appendLuggageTrackingLog(string $resi, string $status, string $notes, string $actor): void
    {
        if ($resi === '' || ! Schema::hasTable('bagasi_logs')) {
            return;
        }

        DB::table('bagasi_logs')->insert([
            'kode_resi' => $resi,
            'status' => $status,
            'notes' => $notes,
            'created_by_username' => $actor,
            'created_at' => now(),
        ]);
    }

    private function markMappedLuggagesArrived(
        int $assignmentId,
        string $rute,
        string $tanggal,
        string $jam,
        int $unit,
        string $actor,
    ): int {
        if ($assignmentId <= 0 || ! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return 0;
        }

        $assignmentIds = $this->matchingTripAssignmentIds($rute, $tanggal, $jam, $unit, $assignmentId);

        if ($assignmentIds === []) {
            return 0;
        }

        $rows = DB::table('luggages')
            ->whereIn('trip_assignment_id', $assignmentIds)
            ->orderBy('id')
            ->get([
                'id',
                'kode_resi',
                'sender_name',
                'receiver_name',
                'status',
            ]);

        if ($rows->isEmpty()) {
            return 0;
        }

        $arrivedStatus = $this->luggageArrivedStatus();
        $arrivedStatusKey = strtolower($arrivedStatus);
        $departureLabel = $this->departureIdentityLabel($rute, $tanggal, $jam, $unit);
        $updatedCount = 0;
        $updatedResi = [];

        foreach ($rows as $row) {
            $currentStatus = strtolower($this->normalizeLuggageStatus($row->status ?? null));

            if ($currentStatus === 'canceled' || $currentStatus === $arrivedStatusKey) {
                continue;
            }

            DB::table('luggages')
                ->where('id', (int) ($row->id ?? 0))
                ->update(['status' => $arrivedStatus]);

            $updatedCount++;

            $resi = trim((string) ($row->kode_resi ?? ''));
            if ($resi !== '') {
                $updatedResi[] = $resi;
            }

            $this->appendLuggageTrackingLog(
                $resi,
                $arrivedStatus,
                'Bagasi tiba bersama keberangkatan '.$departureLabel,
                $actor,
            );
        }

        if ($updatedCount > 0) {
            $previewResi = implode(', ', array_slice($updatedResi, 0, 5));
            $remaining = max(0, count($updatedResi) - 5);
            $resiDetail = $previewResi !== '' ? ' | Resi: '.$previewResi.($remaining > 0 ? ' (+'.$remaining.' lagi)' : '') : '';

            ActivityLog::write(
                'BOOKING',
                $updatedCount.' bagasi pada '.$departureLabel.' ditandai sudah tiba',
                'Status bagasi: '.$this->luggagePickedUpStatus().' -> '.$arrivedStatus.$resiDetail,
                $actor,
                [
                    'trip_assignment_id' => $assignmentId,
                    'trip_assignment_ids' => $assignmentIds,
                    'luggage_arrived_count' => $updatedCount,
                    'rute' => $rute,
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                    'unit' => $unit,
                ],
            );
        }

        return $updatedCount;
    }

    private function normalizeRouteName(string $value): string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
        $normalized = str_replace([' => ', ' -> ', ' - '], ' TO ', $normalized);
        $normalized = str_replace(['=>', '->'], ' TO ', $normalized);
        $normalized = preg_replace('/\s*-\s*/', ' TO ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function schedulesHasSeatsColumn(): bool
    {
        if ($this->schedulesHasSeatsColumn === null) {
            $this->schedulesHasSeatsColumn = Schema::hasColumn('schedules', 'seats');
        }

        return $this->schedulesHasSeatsColumn;
    }

    private function schedulesHasBopColumn(): bool
    {
        if ($this->schedulesHasBopColumn === null) {
            $this->schedulesHasBopColumn = Schema::hasColumn('schedules', 'bop');
        }

        return $this->schedulesHasBopColumn;
    }

    private function upsertCustomer(string $name, string $phone, string $pickupPoint, string $address = '', int $routeId = 0): void
    {
        $customer = [
            'name' => $name,
            'phone' => $phone,
            'pickup_point' => $pickupPoint,
            'gmaps' => $address,
            'created_at' => now(),
        ];
        $poolId = 0;

        if (Schema::hasColumn('customers', 'pool_id')) {
            $poolId = PoolScope::customerPoolId($routeId);
            $customer['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        DB::table('customers')->upsert([$customer], ['phone'], ['name', 'pickup_point', 'gmaps']);

        if ($poolId > 0) {
            DB::table('customers')
                ->where('phone', $phone)
                ->whereNull('pool_id')
                ->update(['pool_id' => $poolId]);
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone) ?? '';
        if (str_starts_with($phone, '62')) {
            $phone = '0'.substr($phone, 2);
        }
        if (str_starts_with($phone, '8')) {
            $phone = '0'.$phone;
        }
        if (strlen($phone) > 13) {
            $phone = substr($phone, 0, 13);
        }

        return $phone;
    }

    private function normalizeSeat(string $seat): string
    {
        return strtoupper(trim($seat));
    }

    private function normalizePayment(string $payment): string
    {
        $allowed = ['Belum Lunas', 'DP', 'Lunas', 'Refund', 'Redbus', 'Traveloka', 'QRIS', 'Transfer', 'Transfer BJU', 'Tunai'];

        return in_array($payment, $allowed, true) ? $payment : 'Belum Lunas';
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time.':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        throw ValidationException::withMessages([
            'jam' => 'Format jam tidak valid.',
        ]);
    }

    private function segmentPrice(int $segmentId): float
    {
        if ($segmentId <= 0) {
            return 0.0;
        }

        $price = DB::table('segments')->where('id', $segmentId)->value('harga');

        return (float) ($price ?? 0);
    }

    private function countSeatsFromLayout(array $layout): int
    {
        $count = 0;
        foreach ($layout as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $cell) {
                if (is_array($cell) && (($cell['type'] ?? '') === 'seat' || ($cell['kind'] ?? '') === 'seat')) {
                    $count += 1;

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

    private function decodeLayout(mixed $layoutRaw): array
    {
        if (is_array($layoutRaw)) {
            return $layoutRaw;
        }

        if (is_string($layoutRaw) && trim($layoutRaw) !== '') {
            $decoded = json_decode($layoutRaw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function extractSeatTokensFromLayoutData(array $layout, int $fallbackCount = 0): array
    {
        $tokens = [];

        foreach ($layout as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $cell) {
                $seatToken = $this->extractSeatTokenFromCell($cell);
                if ($seatToken !== '') {
                    $tokens[] = $seatToken;
                }
            }
        }

        $tokens = array_values(array_unique(array_filter($tokens, static fn ($seat) => $seat !== '')));
        if (! empty($tokens)) {
            usort($tokens, [$this, 'compareSeatTokens']);

            return $tokens;
        }

        if ($fallbackCount <= 0) {
            return [];
        }

        $fallback = [];
        for ($i = 1; $i <= $fallbackCount; $i += 1) {
            $fallback[] = (string) $i;
        }

        return $fallback;
    }

    private function extractSeatTokenFromCell(mixed $cell): string
    {
        if (is_array($cell)) {
            $type = strtolower(trim((string) ($cell['type'] ?? $cell['kind'] ?? '')));
            if ($type === 'driver' || $type === 'aisle' || $type === 'empty' || $type === 'bagasi' || $type === 'bagasi-custom') {
                return '';
            }

            if ($type === 'seat') {
                $seatValue = $cell['label'] ?? $cell['seatNumber'] ?? $cell['seat'] ?? $cell['code'] ?? $cell['value'] ?? $cell['number'] ?? $cell['no'] ?? $cell['id'] ?? null;

                return is_string($seatValue) || is_numeric($seatValue)
                    ? $this->normalizeSeat((string) $seatValue)
                    : '';
            }

            $seatValue = $cell['seat'] ?? $cell['label'] ?? $cell['code'] ?? $cell['value'] ?? $cell['number'] ?? $cell['no'] ?? $cell['id'] ?? null;

            return is_string($seatValue) || is_numeric($seatValue)
                ? $this->normalizeSeat((string) $seatValue)
                : '';
        }

        if (! is_string($cell) && ! is_numeric($cell)) {
            return '';
        }

        $token = $this->normalizeSeat((string) $cell);
        if ($token === '' || in_array($token, ['DRIVER', 'AISLE', '-', '_'], true)) {
            return '';
        }

        return $token;
    }

    private function compareSeatTokens(string $a, string $b): int
    {
        $aNum = is_numeric($a) ? (int) $a : null;
        $bNum = is_numeric($b) ? (int) $b : null;

        if ($aNum !== null && $bNum !== null) {
            return $aNum <=> $bNum;
        }

        return strcmp($a, $b);
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
