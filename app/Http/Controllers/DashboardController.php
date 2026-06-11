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
        $trendAnchor = null;
        $resolveTrendAnchor = function () use ($today, &$trendAnchor): Carbon {
            if ($trendAnchor instanceof Carbon) {
                return $trendAnchor;
            }

            $latestActivityDate = $this->latestActivityDate();
            $trendAnchor = $latestActivityDate && $latestActivityDate->lt($today->copy()->subDays(6))
                ? $latestActivityDate->copy()
                : $today->copy();

            return $trendAnchor;
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
            'dailyTrend' => Inertia::defer(function () use ($resolveTrendAnchor, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->dailyTrend($resolveTrendAnchor());
            }, 'dashboard-data'),
            'monthlyTrend' => Inertia::defer(function () use ($today, $deferredPoolId): array {
                $this->activePoolId = $deferredPoolId;
                return $this->monthlyTrend($today);
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

        // Booking BOP estimate from route-level BOP
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
        $stats['target_revenue_month'] = $this->targetRevenueForPeriod();
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

        $bookingRows = $this->scopedBookingQuery('bookings as b', 'b.rute')
            ->leftJoin('customers as c', 'c.phone', '=', 'b.phone')
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
            'dashboard:daily-trend:'.$anchorDate->toDateString().':'.PoolScope::cacheKey(),
            now()->addMinutes(2),
            function () use ($anchorDate): array {
                $rows = [];
                $start = $anchorDate->copy()->subDays(6)->startOfDay();
                $bookingRevenueByDate = $this->bookingRevenueByDateRange($start, $anchorDate);
                $charterRevenueByDate = $this->charterRevenueByDateRange($start, $anchorDate);
                $luggageRevenueByDate = $this->luggageRevenueByDateRange($start, $anchorDate);
                $charterBopByDate = $this->charterBopByDateRange($start, $anchorDate);

                for ($i = 6; $i >= 0; $i--) {
                    $day = $anchorDate->copy()->subDays($i);
                    $dayKey = $day->toDateString();
                    $bookingRevenue = $bookingRevenueByDate[$dayKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByDate[$dayKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByDate[$dayKey] ?? 0.0;
                    $charterBop = $charterBopByDate[$dayKey] ?? 0.0;
                    $totalRevenue = $bookingRevenue + $charterRevenue + $luggageRevenue;

                    $rows[] = [
                        'label' => strtoupper($day->translatedFormat('D')),
                        'date' => $day->translatedFormat('d M'),
                        'revenue' => $totalRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
                        'charter_bop' => $charterBop,
                        'margin' => $totalRevenue - $charterBop,
                    ];
                }

                return $rows;
            },
        );
    }

    private function monthlyTrend(Carbon $yearAnchor): array
    {
        return Cache::remember(
            'dashboard:monthly-trend:'.$yearAnchor->format('Y-m').':'.PoolScope::cacheKey(),
            now()->addMinutes(2),
            function () use ($yearAnchor): array {
                $rows = [];
                $year = (int) $yearAnchor->format('Y');
                $endMonth = (int) $yearAnchor->format('n');
                $yearStart = Carbon::create($year, 1, 1)->startOfMonth();
                $yearEnd = $yearAnchor->copy()->endOfMonth();
                $bookingRevenueByMonth = $this->bookingRevenueByMonthRange($yearStart, $yearEnd);
                $charterRevenueByMonth = $this->charterRevenueByMonthRange($yearStart, $yearEnd);
                $luggageRevenueByMonth = $this->luggageRevenueByMonthRange($yearStart, $yearEnd);
                $charterBopByMonth = $this->charterBopByMonthRange($yearStart, $yearEnd);

                for ($month = 1; $month <= $endMonth; $month++) {
                    $start = Carbon::create($year, $month, 1)->startOfMonth();
                    $monthKey = (string) $month;
                    $bookingRevenue = $bookingRevenueByMonth[$monthKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByMonth[$monthKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByMonth[$monthKey] ?? 0.0;
                    $charterBop = $charterBopByMonth[$monthKey] ?? 0.0;
                    $totalRevenue = $bookingRevenue + $charterRevenue + $luggageRevenue;

                    $rows[] = [
                        'label' => strtoupper($start->translatedFormat('M')),
                        'name' => $start->translatedFormat('F Y'),
                        'revenue' => $totalRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
                        'charter_bop' => $charterBop,
                        'margin' => $totalRevenue - $charterBop,
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
        $prefix = $alias !== '' ? $alias.'.' : '';

        return Schema::hasColumn(trim($table), 'tenant_id') ? $prefix.'tenant_id' : '';
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
            'total' => ActivityLog::count($this->activePoolId),
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
            }
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Estimate booking BOP from route-level BOP data, proportional to booking volume.
     */
    private function estimateBookingBop(string $dateStart, string $dateEnd, array $scopeStats, string $scopeKey = 'month'): float
    {
        if (! Schema::hasTable('routes') || ! Schema::hasColumn('routes', 'bop')) {
            return 0.0;
        }

        if (! Schema::hasTable('bookings')) {
            return 0.0;
        }

        $bookingRoutes = $this->scopedBookingQuery('bookings', 'rute', $this->activePoolId)
            ->where('status', '!=', 'canceled')
            ->whereBetween('tanggal', [$dateStart, $dateEnd])
            ->select('rute')
            ->distinct()
            ->pluck('rute')
            ->all();

        if (empty($bookingRoutes)) {
            return 0.0;
        }

        $routeBopSum = (float) DB::table('routes')
            ->whereIn('name', $bookingRoutes)
            ->sum('bop');

        // Scale daily BOP proportionally relative to monthly
        if ($scopeKey === 'day' && ($scopeStats['month']['total_bookings'] ?? 1) > 0) {
            $dayCount = (int) ($scopeStats['day']['total_bookings'] ?? 0);
            $monthCount = max(1, (int) ($scopeStats['month']['total_bookings'] ?? 1));
            $routeBopSum = $routeBopSum * ($dayCount / $monthCount);
        }

        return $routeBopSum;
    }

    /**
     * Sum target revenue from routes and pools.
     */
    private function targetRevenueForPeriod(): float
    {
        $target = 0.0;

        // Pool-level target takes priority when a specific pool is selected
        if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'target_revenue')) {
            $poolQuery = DB::table('pools')->where('status', 'active');
            if ($this->activePoolId > 0) {
                $poolQuery->where('id', $this->activePoolId);
            }
            if (Schema::hasColumn('pools', 'tenant_id')) {
                PoolScope::applyTenantScope($poolQuery, 'tenant_id');
            }
            $target = (float) $poolQuery->sum('target_revenue');
        }

        // Fallback to route-level targets
        if ($target <= 0 && Schema::hasTable('routes') && Schema::hasColumn('routes', 'target_revenue')) {
            $routeQuery = DB::table('routes');
            if ($this->activePoolId > 0 && Schema::hasTable('pool_route')) {
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
}
