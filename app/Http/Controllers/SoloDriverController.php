<?php

namespace App\Http\Controllers;

use App\Support\PoolScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class SoloDriverController extends Controller
{
    /**
     * Solo Driver dashboard — simplified mobile-first view for single-driver users.
     */
    public function dashboard(): Response
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $stats = $this->computeSoloStats($today, $monthStart);
        $todayBookings = $this->todayBookings($today);
        $upcomingCharters = $this->upcomingCharters($today);

        return Inertia::render('SoloDashboard', [
            'stats' => $stats,
            'todayBookings' => $todayBookings,
            'upcomingCharters' => $upcomingCharters,
            'todayLabel' => strtoupper($today->translatedFormat('l, d F Y')),
        ]);
    }

    private function computeSoloStats(Carbon $today, Carbon $monthStart): array
    {
        $scope = PoolScope::forCurrentUser();
        $poolIds = $scope['all'] ? [] : $scope['pool_ids'];
        $routeIds = $scope['all'] ? [] : $scope['route_ids'];
        $routeNames = $scope['all'] ? [] : $scope['route_names'];

        $bookingRevenueToday = 0.0;
        $bookingRevenueMonth = 0.0;
        $totalBookingsToday = 0;

        if (Schema::hasTable('bookings')) {
            $bq = DB::table('bookings')->where('status', '!=', 'canceled');
            PoolScope::applyPoolOrRouteScope($bq, '', Schema::hasColumn('bookings', 'route_id') ? 'bookings.route_id' : '', 'bookings.rute');

            $bookingRevenueToday = (float) (clone $bq)
                ->where('tanggal', $today->toDateString())
                ->sum(DB::raw('COALESCE(price, 0) - COALESCE(discount, 0)'));

            $bookingRevenueMonth = (float) (clone $bq)
                ->whereBetween('tanggal', [$monthStart->toDateString(), $today->toDateString()])
                ->sum(DB::raw('COALESCE(price, 0) - COALESCE(discount, 0)'));

            $totalBookingsToday = (int) (clone $bq)->where('tanggal', $today->toDateString())->count();
        }

        $charterRevenueMonth = 0.0;
        $luggageRevenueMonth = 0.0;

        if (Schema::hasTable('charters')) {
            $cq = DB::table('charters');
            PoolScope::applyCharterScope($cq);
            $charterRevenueMonth = (float) (clone $cq)
                ->whereBetween('start_date', [$monthStart->toDateString(), $today->toDateString()])
                ->whereNotIn('status', ['canceled', 'done'])
                ->sum('price');
        }

        if (Schema::hasTable('luggages')) {
            $lq = DB::table('luggages');
            PoolScope::applyPoolOrRouteScope($lq, Schema::hasColumn('luggages', 'pool_id') ? 'luggages.pool_id' : '', Schema::hasColumn('luggages', 'rute_id') ? 'luggages.rute_id' : '', 'luggages.rute');
            $luggageRevenueMonth = (float) (clone $lq)
                ->whereBetween('tanggal', [$monthStart->toDateString(), $today->toDateString()])
                ->where('payment_status', '!=', 'Belum Bayar')
                ->sum('price');
        }

        $unpaidTotal = 0.0;
        if (Schema::hasTable('bookings')) {
            $ubq = DB::table('bookings')->where('status', '!=', 'canceled');
            PoolScope::applyPoolOrRouteScope($ubq, '', Schema::hasColumn('bookings', 'route_id') ? 'bookings.route_id' : '', 'bookings.rute');
            $ubq->whereNotIn('pembayaran', ['Lunas', 'Paid', 'Transfer', 'QRIS', 'Redbus', 'Traveloka']);
            $unpaidTotal += (float) (clone $ubq)->sum(DB::raw('COALESCE(price, 0) - COALESCE(discount, 0)'));
        }

        return [
            'revenue_today' => $bookingRevenueToday,
            'revenue_month' => $bookingRevenueMonth + $charterRevenueMonth + $luggageRevenueMonth,
            'bookings_today' => $totalBookingsToday,
            'unpaid_total' => $unpaidTotal,
        ];
    }

    private function todayBookings(Carbon $today): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $q = DB::table('bookings')
            ->where('status', '!=', 'canceled')
            ->where('tanggal', $today->toDateString())
            ->orderBy('jam')
            ->orderBy('seat')
            ->select('name', 'phone', 'seat', 'rute', 'jam', 'pickup_point', 'pembayaran', 'price');

        PoolScope::applyPoolOrRouteScope($q, '', Schema::hasColumn('bookings', 'route_id') ? 'bookings.route_id' : '', 'bookings.rute');

        return $q->limit(50)->get()->map(function ($b) {
            return [
                'name' => (string) $b->name,
                'phone' => (string) $b->phone,
                'seat' => (string) $b->seat,
                'rute' => (string) $b->rute,
                'jam' => (string) $b->jam,
                'pickup_point' => (string) ($b->pickup_point ?? ''),
                'pembayaran' => (string) ($b->pembayaran ?? ''),
                'price' => (float) ($b->price ?? 0),
            ];
        })->all();
    }

    private function upcomingCharters(Carbon $today): array
    {
        if (! Schema::hasTable('charters')) {
            return [];
        }

        $q = DB::table('charters')
            ->whereIn('status', ['active'])
            ->whereBetween('start_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()])
            ->orderBy('start_date')
            ->select('name', 'phone', 'start_date', 'end_date', 'departure_time', 'pickup_point', 'drop_point', 'payment_status', 'price');

        PoolScope::applyCharterScope($q);

        return $q->limit(10)->get()->map(function ($c) {
            return [
                'name' => (string) $c->name,
                'phone' => (string) ($c->phone ?? ''),
                'start_date' => (string) $c->start_date,
                'end_date' => (string) $c->end_date,
                'departure_time' => (string) ($c->departure_time ?? ''),
                'pickup_point' => (string) ($c->pickup_point ?? ''),
                'drop_point' => (string) ($c->drop_point ?? ''),
                'payment_status' => (string) ($c->payment_status ?? ''),
                'price' => (float) ($c->price ?? 0),
            ];
        })->all();
    }
}
