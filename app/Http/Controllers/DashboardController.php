<?php

namespace App\Http\Controllers;

use App\Support\ActivityLog;
use App\Support\PoolScope;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private int $activePoolId = 0;

    public function __invoke(\Illuminate\Http\Request $request): Response
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $yearStart = $today->copy()->startOfYear();
        $previousYearStart = $yearStart->copy()->subYear()->startOfYear();
        $this->activePoolId = (int) $request->query('pool_id', 0);
        $dashboardSummary = null;
        $resolveDashboardSummary = function () use ($today, &$dashboardSummary): array {
            return $dashboardSummary ??= $this->dashboardSummary($today);
        };
        $recentActivity = null;
        $resolveRecentActivity = function () use (&$recentActivity): array {
            return $recentActivity ??= $this->recentActivity();
        };

        $pools = $this->loadPoolsForSwitcher();
        $selectedPoolName = $this->activePoolId > 0
            ? ((string) ($pools->firstWhere('id', $this->activePoolId)?->name ?? 'Pool'))
            : 'Semua Pool';
        // Capture for deferred closures — Inertia::defer() runs in separate request
        // that may not include original query params, so we bind poolId into closure scope.
        $deferredPoolId = $this->activePoolId;

        return Inertia::render('Dashboard', [
            'todayLabel' => strtoupper($today->translatedFormat('l, d F Y')),
            'pools' => $pools->values(),
            'selectedPoolId' => $this->activePoolId,
            'selectedPoolName' => $selectedPoolName,
            'stats' => fn (): array => $resolveDashboardSummary()['stats'],
            'statsComparison' => fn (): array => $resolveDashboardSummary()['statsComparison'],
            'statsPeriod' => [
                'current_label' => $monthStart->translatedFormat('F Y'),
                'previous_label' => $previousMonthStart->translatedFormat('F Y'),
            ],
            'summaryStatsByScope' => fn (): array => $resolveDashboardSummary()['summaryStatsByScope'],
            'summaryComparisonByScope' => fn (): array => $resolveDashboardSummary()['summaryComparisonByScope'],
            'summaryPeriodByScope' => [
                'day' => [
                    'current_label' => 'Hari Ini',
                    'previous_label' => 'Kemarin',
                    'subtitle_label' => 'hari ini',
                ],
                'month' => [
                    'current_label' => $monthStart->translatedFormat('F Y'),
                    'previous_label' => $previousMonthStart->translatedFormat('F Y'),
                    'subtitle_label' => 'bulan ini',
                ],
                'year' => [
                    'current_label' => $yearStart->translatedFormat('Y'),
                    'previous_label' => $previousYearStart->translatedFormat('Y'),
                    'subtitle_label' => 'tahun ini',
                ],
            ],
            'dailyTrend' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->dailyTrend($today);
            }, 'dashboard-data'),
            'monthlyTrend' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->monthlyTrend($today);
            }, 'dashboard-data'),
            'yearlyHeatmap' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->yearlyHeatmap($today);
            }, 'dashboard-data'),
            'recentActivity' => Inertia::defer(function () use ($resolveRecentActivity, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $resolveRecentActivity()['items'];
            }, 'dashboard-data'),
            'recentActivityTotal' => Inertia::defer(function () use ($resolveRecentActivity, $deferredPoolId): int {
                $this->activePoolId = $deferredPoolId;
                return (int) $resolveRecentActivity()['total'];
            }, 'dashboard-data'),
            'recentActivityVisibleCount' => Inertia::defer(function () use ($resolveRecentActivity, $deferredPoolId): int {
                $this->activePoolId = $deferredPoolId;
                return (int) $resolveRecentActivity()['visible_count'];
            }, 'dashboard-data'),
            'departuresToday' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->departuresToday($today);
            }, 'dashboard-data'),
            'upcomingCharterReminder' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->upcomingCharterReminder($today);
            }, 'dashboard-data'),
            'topDrivers' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->topDriversByRevenue($today);
            }, 'dashboard-data'),
            'topArmadas' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->topArmadasByRevenue($today);
            }, 'dashboard-data'),
        ]);
    }

    private function dashboardSummary(Carbon $today): array
    {
        $stats = [
            'total_bookings' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'canceled' => 0,
            'live_fleet' => 0,
            'revenue_today' => 0.0,
            'revenue_booking_month' => 0.0,
            'revenue_charter_month' => 0.0,
            'revenue_luggage_month' => 0.0,
            'revenue_total_today' => 0.0,
            'revenue_total_month' => 0.0,
            'revenue_total_year' => 0.0,
            'bop_charter_month' => 0.0,
            'bop_booking_month' => 0.0,
            'margin_charter_month' => 0.0,
            'margin_booking_month' => 0.0,
            'margin_total_month' => 0.0,
            'target_revenue_month' => 0.0,
            'achievement_percent' => 0.0,
            'top_route' => '-',
            'top_route_count' => 0,
        ];
        $statsComparison = [
            'total_bookings' => 0,
            'revenue_booking_month' => 0.0,
            'revenue_charter_month' => 0.0,
            'revenue_luggage_month' => 0.0,
            'bop_charter_month' => 0.0,
            'bop_booking_month' => 0.0,
        ];
        $summaryStatsByScope = $this->emptySummaryScopes();
        $summaryComparisonByScope = $this->emptySummaryScopes();
        $datePeriods = $this->dashboardPeriods($today, false);
        $dateTimePeriods = $this->dashboardPeriods($today, true);

        if (Schema::hasTable('bookings')) {
            $bookingSummary = $this->periodSummary(
                $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)->where('status', '!=', 'canceled'),
                'tanggal',
                'COALESCE(price, 0) - COALESCE(discount, 0)',
                $datePeriods,
                true,
            );
            $bookingValue = static fn (string $key): float => (float) ($bookingSummary->{$key} ?? 0);

            $stats['total_bookings'] = (int) $bookingValue('month_count');
            $stats['revenue_today'] = $bookingValue('day_revenue');
            $stats['revenue_booking_month'] = $bookingValue('month_revenue');
            $statsComparison['total_bookings'] = (int) $bookingValue('previous_month_count');
            $statsComparison['revenue_booking_month'] = $bookingValue('previous_month_revenue');

            $summaryStatsByScope['day']['total_bookings'] = (int) $bookingValue('day_count');
            $summaryStatsByScope['month']['total_bookings'] = (int) $bookingValue('month_count');
            $summaryStatsByScope['year']['total_bookings'] = (int) $bookingValue('year_count');
            $summaryComparisonByScope['day']['total_bookings'] = (int) $bookingValue('previous_day_count');
            $summaryComparisonByScope['month']['total_bookings'] = (int) $bookingValue('previous_month_count');
            $summaryComparisonByScope['year']['total_bookings'] = (int) $bookingValue('previous_year_count');
            $summaryStatsByScope['day']['revenue_booking'] = $bookingValue('day_revenue');
            $summaryStatsByScope['month']['revenue_booking'] = $bookingValue('month_revenue');
            $summaryStatsByScope['year']['revenue_booking'] = $bookingValue('year_revenue');
            $summaryComparisonByScope['day']['revenue_booking'] = $bookingValue('previous_day_revenue');
            $summaryComparisonByScope['month']['revenue_booking'] = $bookingValue('previous_month_revenue');
            $summaryComparisonByScope['year']['revenue_booking'] = $bookingValue('previous_year_revenue');

            $activeBookings = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', '>=', $today->toDateString());
            $activeSnapshot = $this->activeBookingSnapshot($activeBookings);
            $futureCount = (int) ($activeSnapshot->total ?? 0);

            if ($futureCount === 0) {
                $activeBookings = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)->where('status', '!=', 'canceled');
                $activeSnapshot = $this->activeBookingSnapshot($activeBookings);
            }

            $stats['pending'] = (int) ($activeSnapshot->pending ?? 0);
            $stats['confirmed'] = (int) ($activeSnapshot->confirmed ?? 0);
            $stats['canceled'] = (int) $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)->where('status', 'canceled')->count();

            $fleetDate = $futureCount === 0
                ? (clone $activeBookings)->max('tanggal')
                : $today->toDateString();

            if ($fleetDate) {
                $stats['live_fleet'] = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
                    ->where('status', '!=', 'canceled')
                    ->whereDate('tanggal', $fleetDate)
                    ->select(['rute', 'jam', 'unit'])
                    ->distinct()
                    ->get()
                    ->count();
            }

            if ($stats['revenue_today'] <= 0 && $fleetDate) {
                $stats['revenue_today'] = (float) $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
                    ->where('status', '!=', 'canceled')
                    ->whereDate('tanggal', $fleetDate)
                    ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                    ->value('total');
            }

            $topRouteRow = (clone $activeBookings)
                ->selectRaw('rute, COUNT(*) as total')
                ->groupBy('rute')
                ->orderByDesc('total')
                ->first();

            if ($topRouteRow) {
                $stats['top_route'] = (string) $topRouteRow->rute;
                $stats['top_route_count'] = (int) $topRouteRow->total;
            }
        }

        if (Schema::hasTable('charters')) {
            $charterQuery = $this->scopedCharterQuery('charters', '', $this->activePoolId);
            $this->applyActiveCharterFilter($charterQuery);
            $charterSummary = $this->periodSummary(
                $charterQuery,
                'start_date',
                'COALESCE(price, 0)',
                $datePeriods,
            );
            $charterValue = static fn (string $key): float => (float) ($charterSummary->{$key} ?? 0);

            $stats['revenue_charter_month'] = $charterValue('month_revenue');
            $statsComparison['revenue_charter_month'] = $charterValue('previous_month_revenue');
            $summaryStatsByScope['day']['revenue_charter'] = $charterValue('day_revenue');
            $summaryStatsByScope['month']['revenue_charter'] = $charterValue('month_revenue');
            $summaryStatsByScope['year']['revenue_charter'] = $charterValue('year_revenue');
            $summaryComparisonByScope['day']['revenue_charter'] = $charterValue('previous_day_revenue');
            $summaryComparisonByScope['month']['revenue_charter'] = $charterValue('previous_month_revenue');
            $summaryComparisonByScope['year']['revenue_charter'] = $charterValue('previous_year_revenue');

            // BOP for charters
            $charterBopQuery = $this->scopedCharterQuery('charters', '', $this->activePoolId);
            $this->applyActiveCharterFilter($charterBopQuery);
            $charterBopSummary = $this->periodSummary(
                $charterBopQuery,
                'start_date',
                'COALESCE(bop_price, 0)',
                $datePeriods,
            );
            $charterBopValue = static fn (string $key): float => (float) ($charterBopSummary->{$key} ?? 0);

            $stats['bop_charter_month'] = $charterBopValue('month_revenue');
            $statsComparison['bop_charter_month'] = $charterBopValue('previous_month_revenue');
            $summaryStatsByScope['day']['bop_charter'] = $charterBopValue('day_revenue');
            $summaryStatsByScope['month']['bop_charter'] = $charterBopValue('month_revenue');
            $summaryStatsByScope['year']['bop_charter'] = $charterBopValue('year_revenue');
            $summaryComparisonByScope['day']['bop_charter'] = $charterBopValue('previous_day_revenue');
            $summaryComparisonByScope['month']['bop_charter'] = $charterBopValue('previous_month_revenue');
            $summaryComparisonByScope['year']['bop_charter'] = $charterBopValue('previous_year_revenue');
        }

        if (Schema::hasTable('luggages')) {
            $luggageQuery = $this->scopedLuggageQuery('luggages', '', $this->activePoolId);
            $this->applyActiveLuggageFilter($luggageQuery);

            $luggageSummary = $this->periodSummary(
                $luggageQuery,
                'created_at',
                'COALESCE(price, 0)',
                $dateTimePeriods,
            );
            $luggageValue = static fn (string $key): float => (float) ($luggageSummary->{$key} ?? 0);

            $stats['revenue_luggage_month'] = $luggageValue('month_revenue');
            $statsComparison['revenue_luggage_month'] = $luggageValue('previous_month_revenue');
            $summaryStatsByScope['day']['revenue_luggage'] = $luggageValue('day_revenue');
            $summaryStatsByScope['month']['revenue_luggage'] = $luggageValue('month_revenue');
            $summaryStatsByScope['year']['revenue_luggage'] = $luggageValue('year_revenue');
            $summaryComparisonByScope['day']['revenue_luggage'] = $luggageValue('previous_day_revenue');
            $summaryComparisonByScope['month']['revenue_luggage'] = $luggageValue('previous_month_revenue');
            $summaryComparisonByScope['year']['revenue_luggage'] = $luggageValue('previous_year_revenue');
        }

        $stats['revenue_total_today'] = (float) $summaryStatsByScope['day']['revenue_booking']
            + (float) $summaryStatsByScope['day']['revenue_charter']
            + (float) $summaryStatsByScope['day']['revenue_luggage'];
        $stats['revenue_total_month'] = (float) $summaryStatsByScope['month']['revenue_booking']
            + (float) $summaryStatsByScope['month']['revenue_charter']
            + (float) $summaryStatsByScope['month']['revenue_luggage'];
        $stats['revenue_total_year'] = (float) $summaryStatsByScope['year']['revenue_booking']
            + (float) $summaryStatsByScope['year']['revenue_charter']
            + (float) $summaryStatsByScope['year']['revenue_luggage'];

        // Booking BOP from unique departures, using schedule BOP before route fallback.
        $stats['bop_booking_month'] = $this->estimateBookingBop(
            $datePeriods['month'][0], $datePeriods['month'][1], $summaryStatsByScope,
        );
        $statsComparison['bop_booking_month'] = $this->estimateBookingBop(
            $datePeriods['previous_month'][0], $datePeriods['previous_month'][1], $summaryComparisonByScope,
        );
        $summaryStatsByScope['day']['bop_booking'] = $this->estimateBookingBop(
            $datePeriods['day'][0], $datePeriods['day'][1], $summaryStatsByScope,
            'day',
        );
        $summaryStatsByScope['month']['bop_booking'] = $stats['bop_booking_month'];
        $summaryStatsByScope['year']['bop_booking'] = $this->estimateBookingBop(
            $datePeriods['year'][0], $datePeriods['year'][1], $summaryStatsByScope,
        );
        $summaryComparisonByScope['day']['bop_booking'] = $this->estimateBookingBop(
            $datePeriods['previous_day'][0], $datePeriods['previous_day'][1], $summaryComparisonByScope,
            'day',
        );
        $summaryComparisonByScope['month']['bop_booking'] = $statsComparison['bop_booking_month'];
        $summaryComparisonByScope['year']['bop_booking'] = $this->estimateBookingBop(
            $datePeriods['previous_year'][0], $datePeriods['previous_year'][1], $summaryComparisonByScope,
        );

        // Target revenue from routes & pools
        $stats['target_revenue_month'] = $this->targetRevenueForPeriod($datePeriods['month'][0]);
        foreach (['day', 'month', 'year'] as $scope) {
            $summaryStatsByScope[$scope]['target_revenue'] = $stats['target_revenue_month'];
        }

        // Margin calculations
        $stats['margin_booking_month'] = $stats['revenue_booking_month'] - $stats['bop_booking_month'];
        $stats['margin_charter_month'] = $stats['revenue_charter_month'] - $stats['bop_charter_month'];
        $stats['margin_total_month'] = $stats['revenue_total_month'] - $stats['bop_booking_month'] - $stats['bop_charter_month'];
        $stats['achievement_percent'] = $stats['target_revenue_month'] > 0
            ? round(($stats['revenue_total_month'] / $stats['target_revenue_month']) * 100, 1)
            : 0;

        foreach (['day', 'month', 'year'] as $scope) {
            $rb = (float) ($summaryStatsByScope[$scope]['revenue_booking'] ?? 0);
            $rc = (float) ($summaryStatsByScope[$scope]['revenue_charter'] ?? 0);
            $bb = (float) ($summaryStatsByScope[$scope]['bop_booking'] ?? 0);
            $bc = (float) ($summaryStatsByScope[$scope]['bop_charter'] ?? 0);
            $target = (float) ($summaryStatsByScope[$scope]['target_revenue'] ?? 0);
            $totalRevenue = $rb + $rc + (float) ($summaryStatsByScope[$scope]['revenue_luggage'] ?? 0);
            $totalBop = $bb + $bc;

            $summaryStatsByScope[$scope]['margin_booking'] = $rb - $bb;
            $summaryStatsByScope[$scope]['margin_charter'] = $rc - $bc;
            $summaryStatsByScope[$scope]['achievement_percent'] = $target > 0
                ? round(($totalRevenue / $target) * 100, 1)
                : 0;
        }

        return compact('stats', 'statsComparison', 'summaryStatsByScope', 'summaryComparisonByScope');
    }

    private function emptySummaryScopes(): array
    {
        return [
            'day' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0, 'revenue_charter' => 0.0, 'revenue_luggage' => 0.0,
                'bop_booking' => 0.0, 'bop_charter' => 0.0,
                'margin_booking' => 0.0, 'margin_charter' => 0.0,
                'target_revenue' => 0.0, 'achievement_percent' => 0.0,
            ],
            'month' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0, 'revenue_charter' => 0.0, 'revenue_luggage' => 0.0,
                'bop_booking' => 0.0, 'bop_charter' => 0.0,
                'margin_booking' => 0.0, 'margin_charter' => 0.0,
                'target_revenue' => 0.0, 'achievement_percent' => 0.0,
            ],
            'year' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0, 'revenue_charter' => 0.0, 'revenue_luggage' => 0.0,
                'bop_booking' => 0.0, 'bop_charter' => 0.0,
                'margin_booking' => 0.0, 'margin_charter' => 0.0,
                'target_revenue' => 0.0, 'achievement_percent' => 0.0,
            ],
        ];
    }

    private function dashboardPeriods(Carbon $today, bool $dateTime): array
    {
        $range = static function (Carbon $start, Carbon $end) use ($dateTime): array {
            return $dateTime
                ? [$start->copy()->startOfDay()->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()]
                : [$start->toDateString(), $end->toDateString()];
        };

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();
        $yearStart = $today->copy()->startOfYear();
        $yearEnd = $today->copy()->endOfYear();
        $previousYearStart = $yearStart->copy()->subYear()->startOfYear();
        $previousYearEnd = $previousYearStart->copy()->endOfYear();

        return [
            'day' => $range($today, $today),
            'month' => $range($monthStart, $monthEnd),
            'year' => $range($yearStart, $yearEnd),
            'previous_day' => $range($today->copy()->subDay(), $today->copy()->subDay()),
            'previous_month' => $range($previousMonthStart, $previousMonthEnd),
            'previous_year' => $range($previousYearStart, $previousYearEnd),
        ];
    }

    private function periodSummary(
        Builder $query,
        string $dateColumn,
        string $revenueExpression,
        array $periods,
        bool $includeCount = false,
    ): object {
        $selects = [];
        $bindings = [];

        foreach ($periods as $key => [$start, $end]) {
            if ($includeCount) {
                $selects[] = "COALESCE(SUM(CASE WHEN {$dateColumn} BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS {$key}_count";
                array_push($bindings, $start, $end);
            }

            $selects[] = "COALESCE(SUM(CASE WHEN {$dateColumn} BETWEEN ? AND ? THEN {$revenueExpression} ELSE 0 END), 0) AS {$key}_revenue";
            array_push($bindings, $start, $end);
        }

        $firstStart = $periods['previous_year'][0];
        $lastEnd = $periods['year'][1];

        return $query
            ->whereBetween($dateColumn, [$firstStart, $lastEnd])
            ->selectRaw(implode(', ', $selects), $bindings)
            ->first() ?? (object) [];
    }

    private function activeBookingSnapshot(Builder $query): object
    {
        return (clone $query)
            ->selectRaw(
                "COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN pembayaran IS NULL OR pembayaran = 'Belum Lunas' THEN 1 ELSE 0 END), 0) AS pending,
                COALESCE(SUM(CASE WHEN pembayaran IN ('Lunas', 'Redbus', 'Traveloka') THEN 1 ELSE 0 END), 0) AS confirmed",
            )
            ->first() ?? (object) [];
    }

    private function departuresToday(Carbon $today): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $groupRows = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereDate('tanggal', $today->toDateString())
            ->selectRaw('rute, jam, unit, COUNT(*) as total_bookings')
            ->groupBy('rute', 'jam', 'unit')
            ->orderBy('jam')
            ->orderBy('rute')
            ->orderBy('unit')
            ->limit(8)
            ->get();

        if ($groupRows->isEmpty()) {
            return [];
        }

        $groups = [];
        foreach ($groupRows as $row) {
            $jam = substr((string) ($row->jam ?? ''), 0, 5);
            $unit = (int) ($row->unit ?? 0);
            $key = $this->departureGroupKey((string) ($row->rute ?? '-'), $jam, $unit);
            $groups[$key] = [
                'rute' => (string) ($row->rute ?? '-'),
                'tanggal' => $today->toDateString(),
                'jam' => $jam !== '' ? $jam : '-',
                'unit' => $unit,
                'total_bookings' => (int) ($row->total_bookings ?? 0),
                'driver_name' => '-',
                'bookings' => [],
            ];
        }

        $customerScope = PoolScope::forCurrentUser($this->activePoolId);
        $bookingRows = $this->scopedBookingQuery('bookings as b', 'b.rute')
            ->leftJoin('customers as c', function ($join) use ($customerScope): void {
                $join->on('c.phone', '=', 'b.phone');

                if (! Schema::hasColumn('customers', 'pool_id')) {
                    return;
                }

                if (! ($customerScope['all'] ?? true)) {
                    $poolIds = $customerScope['pool_ids'] ?? [];
                    if ($poolIds === []) {
                        $join->whereRaw('1 = 0');

                        return;
                    }

                    $join->whereIn('c.pool_id', $poolIds);
                }
            })
            ->where('b.status', '!=', 'canceled')
            ->whereDate('b.tanggal', $today->toDateString())
            ->select([
                'b.rute',
                'b.jam',
                'b.unit',
                'b.seat',
                'b.name',
                'b.phone',
                'b.pickup_point',
                'b.status',
                'b.pembayaran',
                DB::raw('c.gmaps as gmaps'),
            ])
            ->orderBy('b.jam')
            ->orderBy('b.rute')
            ->orderBy('b.unit')
            ->orderBy('b.seat')
            ->get();

        foreach ($bookingRows as $row) {
            $jam = substr((string) ($row->jam ?? ''), 0, 5);
            $unit = (int) ($row->unit ?? 0);
            $key = $this->departureGroupKey((string) ($row->rute ?? '-'), $jam, $unit);
            if (! array_key_exists($key, $groups)) {
                continue;
            }

            $groups[$key]['bookings'][] = [
                'seat' => (string) ($row->seat ?? '-'),
                'name' => (string) ($row->name ?? '-'),
                'phone' => (string) ($row->phone ?? '-'),
                'pickup_point' => (string) ($row->pickup_point ?? '-'),
                'gmaps' => (string) ($row->gmaps ?? ''),
                'status' => (string) ($row->status ?? ''),
                'pembayaran' => (string) ($row->pembayaran ?? '-'),
            ];
        }

        if (Schema::hasTable('trip_assignments') && Schema::hasTable('drivers')) {
            $routes = array_values(array_unique(array_map(
                fn (array $item) => (string) ($item['rute'] ?? ''),
                $groups,
            )));

            if ($routes !== []) {
                $assignmentRows = $this->scopedBookingQuery('trip_assignments as t', 't.rute')
                    ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id')
                    ->whereDate('t.tanggal', $today->toDateString())
                    ->whereIn('t.rute', $routes)
                    ->select([
                        't.rute',
                        't.jam',
                        't.unit',
                        DB::raw('d.nama as driver_name'),
                    ])
                    ->get();

                foreach ($assignmentRows as $row) {
                    $jam = substr((string) ($row->jam ?? ''), 0, 5);
                    $unit = (int) ($row->unit ?? 0);
                    $key = $this->departureGroupKey((string) ($row->rute ?? '-'), $jam, $unit);
                    if (! array_key_exists($key, $groups)) {
                        continue;
                    }

                    $driverName = trim((string) ($row->driver_name ?? ''));
                    $groups[$key]['driver_name'] = $driverName !== '' ? $driverName : '-';
                }
            }
        }

        return array_values($groups);
    }

    private function departureGroupKey(string $rute, string $jam, int $unit): string
    {
        return implode('|', [$rute, $jam, (string) $unit]);
    }

    private function upcomingCharterReminder(Carbon $today): array
    {
        if (! Schema::hasTable('charters')) {
            return [
                'total' => 0,
                'visible_count' => 0,
                'items' => [],
            ];
        }

        $endDate = $today->copy()->addDays(7)->toDateString();
        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$today->toDateString(), $endDate]);

        if (Schema::hasColumn('charters', 'status')) {
            $query->whereNotIn('status', ['done', 'canceled']);
        } else {
            $query->where(function ($builder) {
                $builder->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
            });
            $query->where(function ($builder) {
                $builder->whereNull('bop_status')->orWhere('bop_status', '!=', 'done');
            });
        }

        $visibleCount = 4;
        $total = (clone $query)->count();

        $this->orderChartersByNearestDeparture($query, $today);

        $items = (clone $query)
            ->select([
                'id',
                'name',
                'company_name',
                'phone',
                'start_date',
                'end_date',
                'departure_time',
                'pickup_point',
                'drop_point',
                'driver_name',
                'payment_status',
                'layanan',
                'status',
            ])
            ->limit($visibleCount)
            ->get()
            ->map(function ($row) use ($today) {
                $startDate = Carbon::parse((string) $row->start_date)->startOfDay();

                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? '-'),
                    'company_name' => $row->company_name ? (string) $row->company_name : null,
                    'phone' => $row->phone ? (string) $row->phone : null,
                    'start_date' => (string) ($row->start_date ?? ''),
                    'end_date' => (string) ($row->end_date ?? ''),
                    'departure_time' => $row->departure_time ? substr((string) $row->departure_time, 0, 5) : null,
                    'pickup_point' => (string) ($row->pickup_point ?? ''),
                    'drop_point' => (string) ($row->drop_point ?? ''),
                    'driver_name' => $row->driver_name ? (string) $row->driver_name : null,
                    'payment_status' => $row->payment_status ? (string) $row->payment_status : null,
                    'layanan' => $row->layanan ? (string) $row->layanan : null,
                    'status' => $row->status ? (string) $row->status : 'active',
                    'day_diff' => $today->diffInDays($startDate, false),
                    'date_label' => $startDate->translatedFormat('l, d F Y'),
                ];
            })
            ->values()
            ->all();

        return [
            'total' => $total,
            'visible_count' => count($items),
            'items' => $items,
        ];
    }

    private function orderChartersByNearestDeparture(Builder $query, Carbon $today): void
    {
        $todayValue = $today->toDateString();
        $driver = DB::connection()->getDriverName();

        $distanceExpression = match ($driver) {
            'pgsql' => 'ABS(start_date::date - ?::date)',
            'sqlite' => 'ABS(julianday(start_date) - julianday(?))',
            'mysql', 'mariadb' => 'ABS(DATEDIFF(start_date, ?))',
            default => null,
        };

        if ($distanceExpression !== null) {
            $query
                ->orderByRaw($distanceExpression.' ASC', [$todayValue])
                ->orderByRaw('CASE WHEN start_date >= ? THEN 0 ELSE 1 END', [$todayValue])
                ->orderBy('start_date');
        } else {
            $query
                ->orderByRaw('CASE WHEN start_date >= ? THEN 0 ELSE 1 END', [$todayValue])
                ->orderByRaw('CASE WHEN start_date >= ? THEN start_date ELSE NULL END ASC', [$todayValue])
                ->orderByRaw('CASE WHEN start_date < ? THEN start_date ELSE NULL END DESC', [$todayValue]);
        }

        $query
            ->orderByRaw('departure_time IS NULL')
            ->orderBy('departure_time')
            ->orderBy('id');

    }

    private function dailyTrend(Carbon $anchorDate): array
    {
        return Cache::remember(
            'dashboard:daily-trend:'.$anchorDate->toDateString().':'.PoolScope::cacheKey($this->activePoolId),
            now()->addMinutes(2),
            function () use ($anchorDate): array {
                $rows = [];
                $start = $anchorDate->copy()->subDays(29)->startOfDay();
                $bookingRevenueByDate = $this->bookingRevenueByDateRange($start, $anchorDate);
                $charterRevenueByDate = $this->charterRevenueByDateRange($start, $anchorDate);
                $luggageRevenueByDate = $this->luggageRevenueByDateRange($start, $anchorDate);
                $bookingCountByDate = $this->bookingCountByDateRange($start, $anchorDate);
                $charterCountByDate = $this->charterCountByDateRange($start, $anchorDate);
                $luggageCountByDate = $this->luggageCountByDateRange($start, $anchorDate);
                $bookingBopByDate = $this->bookingBopByDateRange($start, $anchorDate);
                $charterBopByDate = $this->charterBopByDateRange($start, $anchorDate);

                for ($i = 29; $i >= 0; $i--) {
                    $day = $anchorDate->copy()->subDays($i);
                    $dayKey = $day->toDateString();
                    $bookingRevenue = $bookingRevenueByDate[$dayKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByDate[$dayKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByDate[$dayKey] ?? 0.0;
                    $transactionCount = (int) ($bookingCountByDate[$dayKey] ?? 0)
                        + (int) ($charterCountByDate[$dayKey] ?? 0)
                        + (int) ($luggageCountByDate[$dayKey] ?? 0);
                    $bookingBop = $bookingBopByDate[$dayKey] ?? 0.0;
                    $charterBop = $charterBopByDate[$dayKey] ?? 0.0;
                    $totalRevenue = $bookingRevenue + $charterRevenue + $luggageRevenue;

                    $rows[] = [
                        'label' => $day->translatedFormat('d M'),
                        'date' => $day->translatedFormat('d M'),
                        'transaction_count' => $transactionCount,
                        'revenue' => $totalRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
                        'booking_bop' => $bookingBop,
                        'charter_bop' => $charterBop,
                        'margin' => $totalRevenue - $bookingBop - $charterBop,
                    ];
                }

                return $rows;
            },
        );
    }

    private function monthlyTrend(Carbon $yearAnchor): array
    {
        return Cache::remember(
            'dashboard:monthly-trend:'.$yearAnchor->format('Y').':'.PoolScope::cacheKey($this->activePoolId),
            now()->addMinutes(2),
            function () use ($yearAnchor): array {
                $year = (int) $yearAnchor->format('Y');
                $yearStart = Carbon::create($year, 1, 1)->startOfDay();
                $queryEnd = $yearAnchor->copy()->endOfDay();

                $bookingRevenueByDate = $this->bookingRevenueByDateRange($yearStart, $queryEnd);
                $charterRevenueByDate = $this->charterRevenueByDateRange($yearStart, $queryEnd);
                $luggageRevenueByDate = $this->luggageRevenueByDateRange($yearStart, $queryEnd);
                $bookingCountByDate = $this->bookingCountByDateRange($yearStart, $queryEnd);
                $charterCountByDate = $this->charterCountByDateRange($yearStart, $queryEnd);
                $luggageCountByDate = $this->luggageCountByDateRange($yearStart, $queryEnd);
                $bookingBopByDate = $this->bookingBopByDateRange($yearStart, $queryEnd);
                $charterBopByDate = $this->charterBopByDateRange($yearStart, $queryEnd);

                $months = [];

                for ($month = 1; $month <= 12; $month++) {
                    $monthDate = Carbon::create($year, $month, 1)->startOfMonth();
                    $monthKey = $monthDate->format('Y-m');

                    $months[$monthKey] = [
                        'label' => strtoupper($monthDate->translatedFormat('M')),
                        'name' => $monthDate->translatedFormat('F Y'),
                        'month_key' => $monthKey,
                        'transaction_count' => 0,
                        'revenue' => 0.0,
                        'booking_revenue' => 0.0,
                        'charter_revenue' => 0.0,
                        'luggage_revenue' => 0.0,
                        'booking_bop' => 0.0,
                        'charter_bop' => 0.0,
                        'margin' => 0.0,
                        'target_revenue' => $this->targetRevenueForPeriod($monthDate->toDateString()),
                    ];
                }

                $accumulate = function (array $values, string $field) use (&$months): void {
                    foreach ($values as $period => $value) {
                        $monthKey = substr((string) $period, 0, 7);

                        if (! isset($months[$monthKey])) {
                            continue;
                        }

                        $months[$monthKey][$field] += (float) $value;
                    }
                };

                $accumulate($bookingRevenueByDate, 'booking_revenue');
                $accumulate($charterRevenueByDate, 'charter_revenue');
                $accumulate($luggageRevenueByDate, 'luggage_revenue');
                $accumulate($bookingBopByDate, 'booking_bop');
                $accumulate($charterBopByDate, 'charter_bop');

                foreach ($bookingCountByDate as $period => $value) {
                    $monthKey = substr((string) $period, 0, 7);

                    if (isset($months[$monthKey])) {
                        $months[$monthKey]['transaction_count'] += (int) $value;
                    }
                }

                foreach ($charterCountByDate as $period => $value) {
                    $monthKey = substr((string) $period, 0, 7);

                    if (isset($months[$monthKey])) {
                        $months[$monthKey]['transaction_count'] += (int) $value;
                    }
                }

                foreach ($luggageCountByDate as $period => $value) {
                    $monthKey = substr((string) $period, 0, 7);

                    if (isset($months[$monthKey])) {
                        $months[$monthKey]['transaction_count'] += (int) $value;
                    }
                }

                return array_values(array_map(static function (array $row): array {
                    $row['revenue'] = (float) $row['booking_revenue']
                        + (float) $row['charter_revenue']
                        + (float) $row['luggage_revenue'];
                    $row['margin'] = (float) $row['revenue']
                        - (float) $row['booking_bop']
                        - (float) $row['charter_bop'];

                    return $row;
                }, $months));
            },
        );
    }

    private function yearlyHeatmap(Carbon $yearAnchor): array
    {
        return Cache::remember(
            'dashboard:yearly-heatmap:'.$yearAnchor->format('Y').':'.PoolScope::cacheKey($this->activePoolId),
            now()->addMinutes(2),
            function () use ($yearAnchor): array {
                $rows = [];
                $year = (int) $yearAnchor->format('Y');
                $yearStart = Carbon::create($year, 1, 1)->startOfMonth();
                $yearEnd = $yearAnchor->copy()->endOfYear();
                $queryEnd = $yearAnchor->copy()->endOfDay();
                $bookingRevenueByDate = $this->bookingRevenueByDateRange($yearStart, $queryEnd);
                $charterRevenueByDate = $this->charterRevenueByDateRange($yearStart, $queryEnd);
                $luggageRevenueByDate = $this->luggageRevenueByDateRange($yearStart, $queryEnd);
                $bookingCountByDate = $this->bookingCountByDateRange($yearStart, $queryEnd);
                $charterCountByDate = $this->charterCountByDateRange($yearStart, $queryEnd);
                $luggageCountByDate = $this->luggageCountByDateRange($yearStart, $queryEnd);
                $bookingBopByDate = $this->bookingBopByDateRange($yearStart, $queryEnd);
                $charterBopByDate = $this->charterBopByDateRange($yearStart, $queryEnd);

                for ($day = $yearStart->copy(); $day->lte($yearEnd); $day->addDay()) {
                    $dayKey = $day->toDateString();
                    $bookingRevenue = $bookingRevenueByDate[$dayKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByDate[$dayKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByDate[$dayKey] ?? 0.0;
                    $bookingBop = $bookingBopByDate[$dayKey] ?? 0.0;
                    $charterBop = $charterBopByDate[$dayKey] ?? 0.0;
                    $transactionCount = (int) ($bookingCountByDate[$dayKey] ?? 0)
                        + (int) ($charterCountByDate[$dayKey] ?? 0)
                        + (int) ($luggageCountByDate[$dayKey] ?? 0);
                    $totalRevenue = $bookingRevenue + $charterRevenue + $luggageRevenue;

                    $rows[] = [
                        'label' => $day->translatedFormat('d M'),
                        'name' => $day->translatedFormat('l, d F Y'),
                        'date' => $dayKey,
                        'month_label' => $day->translatedFormat('F'),
                        'month_short' => strtoupper($day->translatedFormat('M')),
                        'day_number' => (int) $day->format('j'),
                        'day_of_week' => (int) $day->dayOfWeek,
                        'transaction_count' => $transactionCount,
                        'is_future' => $day->gt($yearAnchor),
                        'revenue' => $totalRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
                        'booking_bop' => $bookingBop,
                        'charter_bop' => $charterBop,
                        'margin' => $totalRevenue - $bookingBop - $charterBop,
                    ];
                }

                return $rows;
            },
        );
    }

    /**
     * @return array<string, float>
     */
    private function bookingRevenueByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        return $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('tanggal as period, COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'period')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function charterRevenueByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('start_date as period, COALESCE(SUM(COALESCE(price, 0)), 0) as total')
            ->groupBy('start_date');

        $this->applyActiveCharterFilter($query);

        return $query->pluck('total', 'period')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function luggageRevenueByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('luggages')) {
            return [];
        }

        $dateExpression = $this->dateExpression('created_at');
        $query = $this->scopedLuggageQuery('luggages', '', $this->activePoolId)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()]);

        $this->applyActiveLuggageFilter($query);

        return $query
            ->selectRaw("{$dateExpression} as period, COALESCE(SUM(COALESCE(price, 0)), 0) as total")
            ->groupBy(DB::raw($dateExpression))
            ->pluck('total', 'period')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function bookingCountByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        return $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('tanggal as period, COUNT(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'period')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function charterCountByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('start_date as period, COUNT(*) as total')
            ->groupBy('start_date');

        $this->applyActiveCharterFilter($query);

        return $query->pluck('total', 'period')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function luggageCountByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('luggages')) {
            return [];
        }

        $dateExpression = $this->dateExpression('created_at');
        $query = $this->scopedLuggageQuery('luggages', '', $this->activePoolId)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()]);

        $this->applyActiveLuggageFilter($query);

        return $query
            ->selectRaw("{$dateExpression} as period, COUNT(*) as total")
            ->groupBy(DB::raw($dateExpression))
            ->pluck('total', 'period')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function bookingRevenueByMonthRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $monthExpression = $this->monthExpression('tanggal');

        return $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$monthExpression} as period, COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) as total")
            ->groupBy(DB::raw($monthExpression))
            ->pluck('total', 'period')
            ->mapWithKeys(static fn ($value, $key): array => [(string) $key => (float) $value])
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function bookingBopByDateRange(Carbon $start, Carbon $end): array
    {
        $departures = $this->bookingDeparturesForBop($start->toDateString(), $end->toDateString());
        $lookup = $this->bookingBopLookup($departures);
        $totals = [];

        foreach ($departures as $departure) {
            $date = trim((string) ($departure->tanggal ?? ''));
            if ($date === '') {
                continue;
            }

            $totals[$date] = ($totals[$date] ?? 0.0) + $this->bookingDepartureBop($departure, $lookup);
        }

        return $totals;
    }

    /**
     * @return array<string, float>
     */
    private function bookingBopByMonthRange(Carbon $start, Carbon $end): array
    {
        $departures = $this->bookingDeparturesForBop($start->toDateString(), $end->toDateString());
        $lookup = $this->bookingBopLookup($departures);
        $totals = [];

        foreach ($departures as $departure) {
            $date = trim((string) ($departure->tanggal ?? ''));
            if ($date === '') {
                continue;
            }

            try {
                $month = (string) Carbon::parse($date)->format('n');
            } catch (\Throwable) {
                continue;
            }

            $totals[$month] = ($totals[$month] ?? 0.0) + $this->bookingDepartureBop($departure, $lookup);
        }

        return $totals;
    }

    /**
     * @return array<string, float>
     */
    private function charterRevenueByMonthRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $monthExpression = $this->monthExpression('start_date');
        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$monthExpression} as period, COALESCE(SUM(COALESCE(price, 0)), 0) as total")
            ->groupBy(DB::raw($monthExpression));

        $this->applyActiveCharterFilter($query);

        return $query->pluck('total', 'period')
            ->mapWithKeys(static fn ($value, $key): array => [(string) $key => (float) $value])
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function charterBopByDateRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('start_date as period, COALESCE(SUM(COALESCE(bop_price, 0)), 0) as total')
            ->groupBy('start_date');

        $this->applyActiveCharterFilter($query);

        return $query->pluck('total', 'period')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function charterBopByMonthRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $monthExpression = $this->monthExpression('start_date');
        $query = $this->scopedCharterQuery('charters', '', $this->activePoolId)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("{$monthExpression} as period, COALESCE(SUM(COALESCE(bop_price, 0)), 0) as total")
            ->groupBy(DB::raw($monthExpression));

        $this->applyActiveCharterFilter($query);

        return $query->pluck('total', 'period')
            ->mapWithKeys(static fn ($value, $key): array => [(string) $key => (float) $value])
            ->all();
    }

    /**
     * @return array<string, float>
     */
    private function luggageRevenueByMonthRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('luggages')) {
            return [];
        }

        $monthExpression = $this->monthExpression('created_at');
        $query = $this->scopedLuggageQuery('luggages', '', $this->activePoolId)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()]);

        $this->applyActiveLuggageFilter($query);

        return $query
            ->selectRaw("{$monthExpression} as period, COALESCE(SUM(COALESCE(price, 0)), 0) as total")
            ->groupBy(DB::raw($monthExpression))
            ->pluck('total', 'period')
            ->mapWithKeys(static fn ($value, $key): array => [(string) $key => (float) $value])
            ->all();
    }

    private function applyActiveCharterFilter(Builder $query): void
    {
        if (Schema::hasColumn('charters', 'status')) {
            $query->where('status', '!=', 'canceled');

            return;
        }

        $query->where(function ($builder) {
            $builder->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
        });
    }

    private function applyActiveLuggageFilter(Builder $query, string $alias = ''): void
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        if (Schema::hasColumn('luggages', 'status')) {
            $query->where(function (Builder $builder) use ($prefix): void {
                $builder
                    ->whereNull($prefix.'status')
                    ->orWhereRaw('LOWER('.$prefix.'status) <> ?', ['canceled']);
            });
        }

        if (Schema::hasColumn('luggages', 'payment_status')) {
            $query->where(function (Builder $builder) use ($prefix): void {
                $builder
                    ->whereNull($prefix.'payment_status')
                    ->orWhereRaw('LOWER('.$prefix.'payment_status) <> ?', ['canceled']);
            });
        }
    }

    private function scopedBookingQuery(string $table = 'bookings', string $routeNameColumn = 'rute', int $poolId = 0): Builder
    {
        $query = DB::table($table);
        PoolScope::applyRouteScope($query, $this->bookingRouteIdColumn($table), $routeNameColumn, $poolId);
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

    private function scopedCharterQuery(string $table = 'charters', string $alias = '', int $poolId = 0): Builder
    {
        $query = DB::table($table);
        PoolScope::applyCharterScope($query, $alias, $poolId);
        $this->applyTenantScopeIfExists($query, $table, $alias);

        return $query;
    }

    private function scopedLuggageQuery(string $table = 'luggages', string $alias = '', int $poolId = 0): Builder
    {
        $query = DB::table($table);
        $prefix = $alias !== '' ? $alias.'.' : '';

        PoolScope::applyPoolOrRouteScope(
            $query,
            Schema::hasColumn('luggages', 'pool_id') ? $prefix.'pool_id' : '',
            Schema::hasColumn('luggages', 'rute_id') ? $prefix.'rute_id' : '',
            $prefix.'rute',
            $poolId,
        );
        $this->applyTenantScopeIfExists($query, $table, $alias);

        return $query;
    }

    private function tenantIdColumn(string $table, string $alias = ''): string
    {
        [$baseTable, $tableAlias] = $this->parseTableAlias($table);
        $effectiveAlias = $alias !== '' ? $alias : $tableAlias;
        $prefix = $effectiveAlias !== '' ? $effectiveAlias.'.' : '';

        return Schema::hasColumn($baseTable, 'tenant_id') ? $prefix.'tenant_id' : '';
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

    private function applyTenantScopeIfExists(Builder $query, string $table, string $alias = ''): void
    {
        $column = $this->tenantIdColumn($table, $alias);
        if ($column !== '') {
            PoolScope::applyTenantScope($query, $column);
        }
    }

    private function dateExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "date({$column})"
            : "DATE({$column})";
    }

    private function monthExpression(string $column): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            default => "MONTH({$column})",
        };
    }

    private function latestActivityDate(): ?Carbon
    {
        $dates = [];

        if (Schema::hasTable('bookings')) {
            $value = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)->max('tanggal');
            if ($value) {
                $dates[] = Carbon::parse((string) $value)->startOfDay();
            }
        }
        if (Schema::hasTable('charters')) {
            $value = $this->scopedCharterQuery('charters', '', $this->activePoolId)->max('start_date');
            if ($value) {
                $dates[] = Carbon::parse((string) $value)->startOfDay();
            }
        }
        if (Schema::hasTable('luggages')) {
            $value = $this->scopedLuggageQuery('luggages', '', $this->activePoolId)->max('created_at');
            if ($value) {
                $dates[] = Carbon::parse((string) $value)->startOfDay();
            }
        }

        if ($dates === []) {
            return null;
        }

        usort($dates, fn (Carbon $a, Carbon $b) => $a->timestamp <=> $b->timestamp);
        return end($dates) ?: null;
    }

    private function recentActivity(): array
    {
        $visibleCount = 4;
        $items = collect(ActivityLog::recent($visibleCount, 0, $this->activePoolId))
            ->map(function (array $item) {
                return [
                    'title' => $item['title'],
                    'meta' => $item['meta'] !== '' ? $item['meta'] : '-',
                    'tag' => $item['tag'],
                    'time' => $this->relativeTime($item['created_at']),
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
            'total' => ActivityLog::count($this->activePoolId, $visibleCount + 1),
            'visible_count' => count($items),
        ];
    }

    private function relativeTime(string $dateTime): string
    {
        if ($dateTime === '') {
            return '-';
        }
        try {
            return Carbon::parse($dateTime)->diffForHumans();
        } catch (\Throwable) {
            return '-';
        }
    }

    /**
     * Load pools available for the dashboard switcher.
     */
    private function loadPoolsForSwitcher(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('pools')) {
            return collect([]);
        }

        $scope = PoolScope::forCurrentUser();
        $query = DB::table('pools')
            ->where('status', 'active')
            ->select(['id', 'name', 'code']);

        if (! ($scope['all'] ?? true)) {
            $poolIds = $scope['pool_ids'] ?? [];
            if (! empty($poolIds)) {
                $query->whereIn('id', $poolIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('name')->get();
    }

    private function estimateBookingBop(string $dateStart, string $dateEnd, array $scopeStats, string $scopeKey = 'month'): float
    {
        $departures = $this->bookingDeparturesForBop($dateStart, $dateEnd);
        $lookup = $this->bookingBopLookup($departures);

        return (float) $departures->sum(fn ($departure): float => $this->bookingDepartureBop($departure, $lookup));
    }

    private function bookingDeparturesForBop(string $dateStart, string $dateEnd): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('bookings')) {
            return collect();
        }

        return $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereBetween('tanggal', [$dateStart, $dateEnd])
            ->distinct()
            ->get([
                Schema::hasColumn('bookings', 'route_id') ? 'route_id' : DB::raw('NULL as route_id'),
                'rute',
                'tanggal',
                'jam',
                'unit',
            ]);
    }

    /**
     * @return array{schedule_route: array<string, float>, schedule_name: array<string, float>, route_id: array<int, float>, route_name: array<string, float>}
     */
    private function bookingBopLookup(\Illuminate\Support\Collection $departures): array
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

        $scope = PoolScope::forCurrentUser($this->activePoolId);
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

            if (! ($scope['all'] ?? true)) {
                $scopedRouteIds = $scope['route_ids'] ?? [];
                if ($scopedRouteIds === []) {
                    $routeQuery->whereRaw('1 = 0');
                } else {
                    $routeQuery->whereIn('id', $scopedRouteIds);
                }
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

                foreach ($this->routeNameCandidates($route) as $candidate) {
                    $key = $this->normalizeRouteKey($candidate);
                    if ($key !== '') {
                        $lookup['route_name'][$key] = $bop;
                    }
                }
            }
        }

        if (Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'bop')) {
            $scheduleQuery = DB::table('schedules')->where('bop', '>', 0);
            $this->applyTenantScopeIfExists($scheduleQuery, 'schedules');

            $scheduleHasRouteId = Schema::hasColumn('schedules', 'route_id');
            if (! ($scope['all'] ?? true)) {
                $scopedRouteIds = $scope['route_ids'] ?? [];
                $scopedRouteNames = $scope['route_names'] ?? [];
                if ($scheduleHasRouteId && $scopedRouteIds !== []) {
                    $scheduleQuery->whereIn('route_id', $scopedRouteIds);
                } elseif ($scopedRouteNames !== []) {
                    $scheduleQuery->whereIn('rute', $scopedRouteNames);
                } else {
                    $scheduleQuery->whereRaw('1 = 0');
                }
            }

            $scheduleSelect = [
                $scheduleHasRouteId ? 'route_id' : DB::raw('NULL as route_id'),
                'rute',
                'dow',
                'jam',
                'bop',
            ];

            foreach ($scheduleQuery->get($scheduleSelect) as $schedule) {
                $dow = (int) ($schedule->dow ?? -1);
                $time = $this->normalizeTimeKey((string) ($schedule->jam ?? ''));
                $bop = (float) ($schedule->bop ?? 0);

                if ($dow < 0 || $time === '' || $bop <= 0) {
                    continue;
                }

                $routeId = (int) ($schedule->route_id ?? 0);
                if ($routeId > 0) {
                    $lookup['schedule_route'][$routeId.'|'.$dow.'|'.$time] = $bop;
                }

                $routeKey = $this->normalizeRouteKey((string) ($schedule->rute ?? ''));
                if ($routeKey !== '') {
                    $lookup['schedule_name'][$routeKey.'|'.$dow.'|'.$time] = $bop;
                }
            }
        }

        return $lookup;
    }

    /**
     * @param array{schedule_route: array<string, float>, schedule_name: array<string, float>, route_id: array<int, float>, route_name: array<string, float>} $lookup
     */
    private function bookingDepartureBop(object $departure, array $lookup): float
    {
        $routeId = (int) ($departure->route_id ?? 0);
        $routeKey = $this->normalizeRouteKey((string) ($departure->rute ?? ''));
        $time = $this->normalizeTimeKey((string) ($departure->jam ?? ''));

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
    private function routeNameCandidates(object $route): array
    {
        $candidates = [(string) ($route->name ?? '')];
        $origin = trim((string) ($route->origin ?? ''));
        $destination = trim((string) ($route->destination ?? ''));

        if ($origin !== '' && $destination !== '') {
            $candidates[] = $origin.' - '.$destination;
        }

        return $candidates;
    }

    private function normalizeRouteKey(string $route): string
    {
        $normalized = mb_strtoupper(trim($route));
        $normalized = str_replace(['=>', '->'], '-', $normalized);

        return preg_replace('/\s+/', '', $normalized) ?? $normalized;
    }

    private function normalizeTimeKey(string $time): string
    {
        return substr(trim($time), 0, 5);
    }

    /**
     * Sum target revenue from routes and pools.
     */
    private function targetRevenueForPeriod(string $monthStart): float
    {
        $target = 0.0;
        $scope = PoolScope::forCurrentUser($this->activePoolId);
        $isScoped = ! ($scope['all'] ?? true);

        // Pool-level target takes priority when a specific pool is selected.
        if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'target_revenue')) {
            $poolQuery = DB::table('pools')
                ->where('status', 'active');

            if ($isScoped) {
                $poolIds = $scope['pool_ids'] ?? [];
                if ($poolIds === []) {
                    return 0.0;
                }

                $poolQuery->whereIn('id', $poolIds);
            } elseif ($this->activePoolId > 0) {
                $poolQuery->where('id', $this->activePoolId);
            }

            if (Schema::hasColumn('pools', 'tenant_id')) {
                PoolScope::applyTenantScope($poolQuery, 'tenant_id');
            }

            $pools = $poolQuery->get(['id', 'target_revenue']);
            $poolIds = $pools->pluck('id')->map(static fn ($value): int => (int) $value)->values()->all();
            $monthlyTargetsByPool = [];

            if ($poolIds !== [] && Schema::hasTable('pool_monthly_targets')) {
                $monthlyRows = DB::table('pool_monthly_targets')
                    ->whereIn('pool_id', $poolIds)
                    ->where('target_month', $monthStart)
                    ->get([
                        'pool_id',
                        'booking_target',
                        'bagasi_target',
                        'carter_target',
                    ]);

                foreach ($monthlyRows as $row) {
                    $poolId = (int) ($row->pool_id ?? 0);
                    if ($poolId <= 0) {
                        continue;
                    }

                    $monthlyTargetsByPool[$poolId] = (float) ($row->booking_target ?? 0)
                        + (float) ($row->bagasi_target ?? 0)
                        + (float) ($row->carter_target ?? 0);
                }
            }

            foreach ($pools as $pool) {
                $poolId = (int) ($pool->id ?? 0);
                if ($poolId <= 0) {
                    continue;
                }

                $target += (float) ($monthlyTargetsByPool[$poolId] ?? $pool->target_revenue ?? 0);
            }
        }

        // Fallback to route-level targets
        if ($target <= 0 && Schema::hasTable('routes') && Schema::hasColumn('routes', 'target_revenue')) {
            $routeQuery = DB::table('routes');
            if ($isScoped) {
                $routeIds = $scope['route_ids'] ?? [];
                if ($routeIds === []) {
                    return 0.0;
                }

                $routeQuery->whereIn('id', $routeIds);
            } elseif ($this->activePoolId > 0 && Schema::hasTable('pool_route')) {
                $routeIds = DB::table('pool_route')
                    ->where('pool_id', $this->activePoolId)
                    ->pluck('route_id')
                    ->all();
                if (! empty($routeIds)) {
                    $routeQuery->whereIn('id', $routeIds);
                }
            }
            $this->applyTenantScopeIfExists($routeQuery, 'routes');
            $target = (float) $routeQuery->sum('target_revenue');
        }

        return $target;
    }

    private function topDriversByRevenue(Carbon $today): array
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasTable('drivers')) {
            return ['Minibus' => [], 'Mediumbus' => [], 'Bigbus' => []];
        }

        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd   = $today->copy()->endOfMonth()->toDateString();
        $hasArmada = Schema::hasColumn('trip_assignments', 'armada_id') && Schema::hasTable('armadas');

        try {
            $query = DB::table('trip_assignments as t')
                ->join('drivers as d', 't.driver_id', '=', 'd.id')
                ->join('bookings as b', function ($join) {
                    $join->on('b.rute',  '=', 't.rute')
                         ->on('b.tanggal', '=', 't.tanggal')
                         ->on('b.unit',  '=', 't.unit')
                         ->where('b.status', '!=', 'canceled');
                })
                ->whereBetween('t.tanggal', [$monthStart, $monthEnd]);

            if ($hasArmada) {
                $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id')
                    ->selectRaw(
                        'd.id as driver_id,
                         d.nama as driver_name,
                         a.kategori as category,
                         COUNT(DISTINCT CONCAT(t.rute, \'|\', t.tanggal, \'|\', t.unit)) as trip_count,
                         COALESCE(SUM(COALESCE(b.price,0) - COALESCE(b.discount,0)), 0) as revenue,
                         MAX(t.rute) as top_route'
                    )
                    ->groupBy('d.id', 'd.nama', 'a.kategori');
            } else {
                $query->selectRaw(
                        'd.id as driver_id,
                         d.nama as driver_name,
                         \'Minibus\' as category,
                         COUNT(DISTINCT CONCAT(t.rute, \'|\', t.tanggal, \'|\', t.unit)) as trip_count,
                         COALESCE(SUM(COALESCE(b.price,0) - COALESCE(b.discount,0)), 0) as revenue,
                         MAX(t.rute) as top_route'
                    )
                    ->groupBy('d.id', 'd.nama');
            }

            $rows = $query->orderByDesc('revenue')->get();
        } catch (\Throwable) {
            return ['Minibus' => [], 'Mediumbus' => [], 'Bigbus' => []];
        }

        $drivers = $rows->values()->map(static function ($row): array {
            $cat = strtolower(preg_replace('/\s+/', '', trim((string) ($row->category ?? ''))));
            $normalizedCategory = match ($cat) {
                'mediumbus', 'medium bus', 'medium' => 'Mediumbus',
                'bigbus', 'bigbun', 'big bus', 'big' => 'Bigbus',
                default => 'Minibus',
            };

            return [
                'name'       => (string) ($row->driver_name ?? '-'),
                'trip_count' => (int) ($row->trip_count ?? 0),
                'revenue'    => (float) ($row->revenue ?? 0),
                'route'      => $row->top_route ? (string) $row->top_route : null,
                'category'   => $normalizedCategory,
            ];
        });

        $categories = [
            'Minibus' => [],
            'Mediumbus' => [],
            'Bigbus' => [],
        ];

        foreach ($drivers as $driver) {
            $cat = $driver['category'];
            if (!isset($categories[$cat])) {
                $cat = 'Minibus';
            }
            $categories[$cat][] = $driver;
        }

        foreach ($categories as $category => $list) {
            usort($list, static fn (array $left, array $right): int => $right['revenue'] <=> $left['revenue']);
            $list = array_slice($list, 0, 5);

            foreach ($list as $index => &$d) {
                $d['rank'] = $index + 1;
            }
            unset($d);

            $categories[$category] = $list;
        }

        return $categories;
    }

    private function topArmadasByRevenue(Carbon $today): array
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasTable('armadas') || ! Schema::hasTable('bookings')) {
            return [];
        }

        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();
        $hasPool = Schema::hasColumn('armadas', 'pool_id') && Schema::hasTable('pools');

        try {
            $query = DB::table('trip_assignments as t')
                ->join('armadas as a', 't.armada_id', '=', 'a.id')
                ->join('bookings as b', function ($join) {
                    $join->on('b.rute', '=', 't.rute')
                        ->on('b.tanggal', '=', 't.tanggal')
                        ->on('b.unit', '=', 't.unit')
                        ->where('b.status', '!=', 'canceled');
                })
                ->whereBetween('t.tanggal', [$monthStart, $monthEnd]);

            if ($hasPool) {
                $query->leftJoin('pools as p', 'a.pool_id', '=', 'p.id')
                    ->selectRaw(
                        'a.id as armada_id,
                         a.nopol as armada_nopol,
                         a.kategori as category,
                         p.name as pool_name,
                         COUNT(DISTINCT CONCAT(t.rute, \'|\', t.tanggal, \'|\', t.unit)) as trip_count,
                         COALESCE(SUM(COALESCE(b.price,0) - COALESCE(b.discount,0)), 0) as revenue,
                         MAX(t.rute) as top_route'
                    )
                    ->groupBy('a.id', 'a.nopol', 'a.kategori', 'p.name');
            } else {
                $query->selectRaw(
                        'a.id as armada_id,
                         a.nopol as armada_nopol,
                         a.kategori as category,
                         NULL as pool_name,
                         COUNT(DISTINCT CONCAT(t.rute, \'|\', t.tanggal, \'|\', t.unit)) as trip_count,
                         COALESCE(SUM(COALESCE(b.price,0) - COALESCE(b.discount,0)), 0) as revenue,
                         MAX(t.rute) as top_route'
                    )
                    ->groupBy('a.id', 'a.nopol', 'a.kategori');
            }

            $rows = $query->orderByDesc('revenue')->limit(5)->get();
        } catch (\Throwable) {
            return [];
        }

        return $rows->values()->map(static function ($row, int $index): array {
            return [
                'rank' => $index + 1,
                'nopol' => (string) ($row->armada_nopol ?? '-'),
                'trip_count' => (int) ($row->trip_count ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
                'pool_name' => $row->pool_name ? (string) $row->pool_name : null,
                'category' => $row->category ? (string) $row->category : null,
            ];
        })->all();
    }
}
