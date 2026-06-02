<?php

namespace App\Http\Controllers;

use App\Support\ActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();
        $latestActivityDate = $this->latestActivityDate();
        $trendAnchor = $latestActivityDate && $latestActivityDate->lt($today->copy()->subDays(6))
            ? $latestActivityDate->copy()
            : $today->copy();
        // Summary cards must stay on the current running period, even when future trips are added early.
        $periodAnchor = $today->copy();
        $trendYearAnchor = $periodAnchor->copy();
        $monthStart = $periodAnchor->copy()->startOfMonth();
        $monthEnd = $periodAnchor->copy()->endOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();
        $yearStart = $periodAnchor->copy()->startOfYear();
        $yearEnd = $periodAnchor->copy()->endOfYear();
        $yesterday = $today->copy()->subDay();
        $previousYearStart = $yearStart->copy()->subYear()->startOfYear();
        $previousYearEnd = $previousYearStart->copy()->endOfYear();

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
            'top_route' => '-',
            'top_route_count' => 0,
        ];
        $statsComparison = [
            'total_bookings' => 0,
            'revenue_booking_month' => 0.0,
            'revenue_charter_month' => 0.0,
            'revenue_luggage_month' => 0.0,
        ];
        $summaryStatsByScope = [
            'day' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
            'month' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
            'year' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
        ];
        $summaryComparisonByScope = [
            'day' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
            'month' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
            'year' => [
                'total_bookings' => 0,
                'revenue_booking' => 0.0,
                'revenue_charter' => 0.0,
                'revenue_luggage' => 0.0,
            ],
        ];

        if (Schema::hasTable('bookings')) {
            $activeBookings = DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', '>=', $today->toDateString());

            $futureCount = (clone $activeBookings)->count();
            if ($futureCount === 0) {
                $activeBookings = DB::table('bookings')->where('status', '!=', 'canceled');
            }

            $stats['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->count();
            $stats['pending'] = (clone $activeBookings)
                ->where(function ($query) {
                    $query->whereNull('pembayaran')->orWhere('pembayaran', 'Belum Lunas');
                })
                ->count();
            $stats['confirmed'] = (clone $activeBookings)
                ->whereIn('pembayaran', ['Lunas', 'Redbus', 'Traveloka'])
                ->count();
            $stats['canceled'] = DB::table('bookings')
                ->where('status', 'canceled')
                ->count();

            $fleetDate = $futureCount === 0
                ? DB::table('bookings')->where('status', '!=', 'canceled')->max('tanggal')
                : $today->toDateString();
            if ($fleetDate) {
                $stats['live_fleet'] = DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', $fleetDate)
                ->select(['rute', 'jam', 'unit'])
                ->distinct()
                ->get()
                ->count();
            }

            $bookingRevenueToday = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', $today->toDateString())
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
            $stats['revenue_today'] = $bookingRevenueToday;
            if ($stats['revenue_today'] <= 0 && $fleetDate) {
                $stats['revenue_today'] = (float) DB::table('bookings')
                    ->where('status', '!=', 'canceled')
                    ->whereDate('tanggal', $fleetDate)
                    ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                    ->value('total');
            }

            $stats['revenue_booking_month'] = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
            $statsComparison['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
                ->count();
            $statsComparison['revenue_booking_month'] = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
            $summaryStatsByScope['day']['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', $today->toDateString())
                ->count();
            $summaryStatsByScope['month']['total_bookings'] = (int) $stats['total_bookings'];
            $summaryStatsByScope['year']['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$yearStart->toDateString(), $yearEnd->toDateString()])
                ->count();
            $summaryComparisonByScope['day']['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', $yesterday->toDateString())
                ->count();
            $summaryComparisonByScope['month']['total_bookings'] = (int) $statsComparison['total_bookings'];
            $summaryComparisonByScope['year']['total_bookings'] = (int) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$previousYearStart->toDateString(), $previousYearEnd->toDateString()])
                ->count();
            $summaryStatsByScope['day']['revenue_booking'] = (float) $bookingRevenueToday;
            $summaryStatsByScope['month']['revenue_booking'] = (float) $stats['revenue_booking_month'];
            $summaryStatsByScope['year']['revenue_booking'] = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$yearStart->toDateString(), $yearEnd->toDateString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
            $summaryComparisonByScope['day']['revenue_booking'] = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereDate('tanggal', $yesterday->toDateString())
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
            $summaryComparisonByScope['month']['revenue_booking'] = (float) $statsComparison['revenue_booking_month'];
            $summaryComparisonByScope['year']['revenue_booking'] = (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$previousYearStart->toDateString(), $previousYearEnd->toDateString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');

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
            $charterRevenueQuery = DB::table('charters')
                ->whereBetween('start_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

            if (Schema::hasColumn('charters', 'status')) {
                $charterRevenueQuery->where('status', '!=', 'canceled');
            } else {
                $charterRevenueQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
            }

            $stats['revenue_charter_month'] = (float) $charterRevenueQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');

            $previousCharterRevenueQuery = DB::table('charters')
                ->whereBetween('start_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()]);

            if (Schema::hasColumn('charters', 'status')) {
                $previousCharterRevenueQuery->where('status', '!=', 'canceled');
            } else {
                $previousCharterRevenueQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
            }

            $statsComparison['revenue_charter_month'] = (float) $previousCharterRevenueQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');

            $charterRevenueTodayQuery = DB::table('charters')
                ->whereDate('start_date', $today->toDateString());
            $charterRevenueYearQuery = DB::table('charters')
                ->whereBetween('start_date', [$yearStart->toDateString(), $yearEnd->toDateString()]);

            if (Schema::hasColumn('charters', 'status')) {
                $charterRevenueTodayQuery->where('status', '!=', 'canceled');
                $charterRevenueYearQuery->where('status', '!=', 'canceled');
            } else {
                $charterRevenueTodayQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
                $charterRevenueYearQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
            }

            $stats['revenue_total_today'] += (float) $charterRevenueTodayQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $stats['revenue_total_year'] += (float) $charterRevenueYearQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryStatsByScope['day']['revenue_charter'] = (float) (clone $charterRevenueTodayQuery)
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryStatsByScope['month']['revenue_charter'] = (float) $stats['revenue_charter_month'];
            $summaryStatsByScope['year']['revenue_charter'] = (float) (clone $charterRevenueYearQuery)
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $charterRevenueYesterdayQuery = DB::table('charters')
                ->whereDate('start_date', $yesterday->toDateString());
            $charterRevenuePreviousYearQuery = DB::table('charters')
                ->whereBetween('start_date', [$previousYearStart->toDateString(), $previousYearEnd->toDateString()]);
            if (Schema::hasColumn('charters', 'status')) {
                $charterRevenueYesterdayQuery->where('status', '!=', 'canceled');
                $charterRevenuePreviousYearQuery->where('status', '!=', 'canceled');
            } else {
                $charterRevenueYesterdayQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
                $charterRevenuePreviousYearQuery->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'Canceled');
                });
            }
            $summaryComparisonByScope['day']['revenue_charter'] = (float) $charterRevenueYesterdayQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryComparisonByScope['month']['revenue_charter'] = (float) $statsComparison['revenue_charter_month'];
            $summaryComparisonByScope['year']['revenue_charter'] = (float) $charterRevenuePreviousYearQuery
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
        }

        if (Schema::hasTable('luggages')) {
            $stats['revenue_luggage_month'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereBetween('created_at', [$monthStart->toDateTimeString(), $monthEnd->copy()->endOfDay()->toDateTimeString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $statsComparison['revenue_luggage_month'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereBetween('created_at', [$previousMonthStart->toDateTimeString(), $previousMonthEnd->copy()->endOfDay()->toDateTimeString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');

            $stats['revenue_total_today'] += (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereDate('created_at', $today->toDateString())
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $stats['revenue_total_year'] += (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereBetween('created_at', [$yearStart->toDateTimeString(), $yearEnd->copy()->endOfDay()->toDateTimeString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryStatsByScope['day']['revenue_luggage'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereDate('created_at', $today->toDateString())
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryStatsByScope['month']['revenue_luggage'] = (float) $stats['revenue_luggage_month'];
            $summaryStatsByScope['year']['revenue_luggage'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereBetween('created_at', [$yearStart->toDateTimeString(), $yearEnd->copy()->endOfDay()->toDateTimeString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryComparisonByScope['day']['revenue_luggage'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereDate('created_at', $yesterday->toDateString())
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
            $summaryComparisonByScope['month']['revenue_luggage'] = (float) $statsComparison['revenue_luggage_month'];
            $summaryComparisonByScope['year']['revenue_luggage'] = (float) DB::table('luggages')
                ->where('status', '!=', 'canceled')
                ->where('payment_status', 'Lunas')
                ->whereBetween('created_at', [$previousYearStart->toDateTimeString(), $previousYearEnd->copy()->endOfDay()->toDateTimeString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0)), 0) AS total')
                ->value('total');
        }

        $stats['revenue_total_today'] += isset($bookingRevenueToday) ? (float) $bookingRevenueToday : 0.0;
        $stats['revenue_total_month'] = (float) $stats['revenue_booking_month']
            + (float) $stats['revenue_charter_month']
            + (float) $stats['revenue_luggage_month'];
        if (Schema::hasTable('bookings')) {
            $stats['revenue_total_year'] += (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$yearStart->toDateString(), $yearEnd->toDateString()])
                ->selectRaw('COALESCE(SUM(COALESCE(price, 0) - COALESCE(discount, 0)), 0) AS total')
                ->value('total');
        }

        $dailyTrend = $this->dailyTrend($trendAnchor);
        $monthlyTrend = $this->monthlyTrend($trendYearAnchor);
        $recentActivity = $this->recentActivity();
        $departuresToday = $this->departuresToday($today);
        $upcomingCharterReminder = $this->upcomingCharterReminder($today);

        return Inertia::render('Dashboard', [
            'todayLabel' => strtoupper($trendAnchor->translatedFormat('l, d F Y')),
            'stats' => $stats,
            'statsComparison' => $statsComparison,
            'statsPeriod' => [
                'current_label' => $monthStart->translatedFormat('F Y'),
                'previous_label' => $previousMonthStart->translatedFormat('F Y'),
            ],
            'summaryStatsByScope' => $summaryStatsByScope,
            'summaryComparisonByScope' => $summaryComparisonByScope,
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
            'dailyTrend' => $dailyTrend,
            'monthlyTrend' => $monthlyTrend,
            'recentActivity' => $recentActivity['items'],
            'recentActivityTotal' => $recentActivity['total'],
            'recentActivityVisibleCount' => $recentActivity['visible_count'],
            'departuresToday' => $departuresToday,
            'upcomingCharterReminder' => $upcomingCharterReminder,
        ]);
    }

    private function departuresToday(Carbon $today): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $groupRows = DB::table('bookings')
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

        $bookingRows = DB::table('bookings as b')
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
                $assignmentRows = DB::table('trip_assignments as t')
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
        $query = DB::table('charters')
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
            ->orderBy('start_date')
            ->orderByRaw('departure_time IS NULL')
            ->orderBy('departure_time')
            ->orderBy('id')
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

    private function dailyTrend(Carbon $anchorDate): array
    {
        return Cache::remember(
            'dashboard:daily-trend:'.$anchorDate->toDateString(),
            now()->addMinutes(2),
            function () use ($anchorDate): array {
                $rows = [];
                $start = $anchorDate->copy()->subDays(6)->startOfDay();
                $bookingRevenueByDate = $this->bookingRevenueByDateRange($start, $anchorDate);
                $charterRevenueByDate = $this->charterRevenueByDateRange($start, $anchorDate);
                $luggageRevenueByDate = $this->luggageRevenueByDateRange($start, $anchorDate);

                for ($i = 6; $i >= 0; $i--) {
                    $day = $anchorDate->copy()->subDays($i);
                    $dayKey = $day->toDateString();
                    $bookingRevenue = $bookingRevenueByDate[$dayKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByDate[$dayKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByDate[$dayKey] ?? 0.0;

                    $rows[] = [
                        'label' => strtoupper($day->translatedFormat('D')),
                        'date' => $day->translatedFormat('d M'),
                        'revenue' => $bookingRevenue + $charterRevenue + $luggageRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
                    ];
                }

                return $rows;
            },
        );
    }

    private function monthlyTrend(Carbon $yearAnchor): array
    {
        return Cache::remember(
            'dashboard:monthly-trend:'.$yearAnchor->format('Y-m'),
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

                for ($month = 1; $month <= $endMonth; $month++) {
                    $start = Carbon::create($year, $month, 1)->startOfMonth();
                    $monthKey = (string) $month;
                    $bookingRevenue = $bookingRevenueByMonth[$monthKey] ?? 0.0;
                    $charterRevenue = $charterRevenueByMonth[$monthKey] ?? 0.0;
                    $luggageRevenue = $luggageRevenueByMonth[$monthKey] ?? 0.0;

                    $rows[] = [
                        'label' => strtoupper($start->translatedFormat('M')),
                        'name' => $start->translatedFormat('F Y'),
                        'revenue' => $bookingRevenue + $charterRevenue + $luggageRevenue,
                        'booking_revenue' => $bookingRevenue,
                        'charter_revenue' => $charterRevenue,
                        'luggage_revenue' => $luggageRevenue,
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

        return DB::table('bookings')
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

        $query = DB::table('charters')
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

        return DB::table('luggages')
            ->where('status', '!=', 'canceled')
            ->where('payment_status', 'Lunas')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
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

        return DB::table('bookings')
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
        $query = DB::table('charters')
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
    private function luggageRevenueByMonthRange(Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('luggages')) {
            return [];
        }

        $monthExpression = $this->monthExpression('created_at');

        return DB::table('luggages')
            ->where('status', '!=', 'canceled')
            ->where('payment_status', 'Lunas')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->copy()->endOfDay()->toDateTimeString()])
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
            $value = DB::table('bookings')->max('tanggal');
            if ($value) {
                $dates[] = Carbon::parse((string) $value)->startOfDay();
            }
        }
        if (Schema::hasTable('charters')) {
            $value = DB::table('charters')->max('start_date');
            if ($value) {
                $dates[] = Carbon::parse((string) $value)->startOfDay();
            }
        }
        if (Schema::hasTable('luggages')) {
            $value = DB::table('luggages')->max('created_at');
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
        $items = collect(ActivityLog::recent($visibleCount))
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
            'total' => ActivityLog::count(),
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
}


