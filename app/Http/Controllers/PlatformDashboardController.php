<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class PlatformDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();

        return Inertia::render('PlatformDashboard', [
            'metrics' => $this->platformMetrics($today),
            'mrrTrend' => $this->mrrTrend($today),
            'tenants' => $this->tenantsList(),
            'recentSignups' => $this->recentSignups($monthStart),
            'expiringSoon' => $this->expiringSoon($today),
            'paymentMetrics' => $this->paymentMetrics($today),
            'paymentWatchlist' => $this->paymentWatchlist($today),
        ]);
    }

    /**
     * Core SaaS platform metrics.
     */
    private function platformMetrics(Carbon $today): array
    {
        $monthStart = $today->copy()->startOfMonth();
        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        // MRR: Sum of plan prices for active + trialing subscriptions
        $mrr = $this->computeMrr();
        $previousMrr = $this->computeMrrAt($previousMonthEnd);

        // Active tenants (trial + active status)
        $activeTenants = $this->countActiveTenants();
        $previousActiveTenants = $this->countActiveTenantsAt($previousMonthEnd);

        // Total Processed Volume (TPV): sum of all booking/charter/luggage revenue
        $tpvMonth = $this->computeTpv($monthStart, $today);
        $tpvPreviousMonth = $this->computeTpv($previousMonthStart, $previousMonthEnd);

        // Trial conversion: trials that converted to active this month
        $trialsConverted = $this->countTrialsConverted($monthStart, $today);
        $trialsStarted = $this->countTrialsStarted($monthStart, $today);

        // Churn: canceled this month
        $churnedThisMonth = $this->countChurned($monthStart, $today);

        $churnRate = $previousActiveTenants > 0
            ? round(($churnedThisMonth / $previousActiveTenants) * 100, 1)
            : 0.0;

        $trialConversionRate = $trialsStarted > 0
            ? round(($trialsConverted / $trialsStarted) * 100, 1)
            : 0.0;

        $arpu = $activeTenants > 0
            ? round($mrr / $activeTenants, 0)
            : 0.0;

        $arr = $mrr * 12;

        return [
            'mrr' => $mrr,
            'previous_mrr' => $previousMrr,
            'arr' => $arr,
            'active_tenants' => $activeTenants,
            'previous_active_tenants' => $previousActiveTenants,
            'churn_rate' => $churnRate,
            'trial_conversion_rate' => $trialConversionRate,
            'arpu' => $arpu,
            'tpv_month' => $tpvMonth,
            'tpv_previous_month' => $tpvPreviousMonth,
            'month_label' => $monthStart->translatedFormat('F Y'),
            'previous_month_label' => $previousMonthStart->translatedFormat('F Y'),
        ];
    }

    /**
     * MRR trend: monthly subscription revenue over the past 12 months.
     */
    private function mrrTrend(Carbon $today): array
    {
        if (! FeatureGate::ready()) {
            return [];
        }

        $rows = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $today->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            // MRR for that month: active subscriptions × plan price at month end
            $mrr = $this->computeMrrAt($monthEnd);

            $rows[] = [
                'label' => strtoupper($monthStart->translatedFormat('M')),
                'name' => $monthStart->translatedFormat('F Y'),
                'value' => $mrr,
            ];
        }

        return $rows;
    }

    /**
     * List of tenants with subscription info.
     */
    private function tenantsList(): array
    {
        if (! FeatureGate::ready()) {
            return [];
        }

        $query = DB::table('tenants')
            ->leftJoin('subscriptions', function ($join) {
                $join->on('tenants.id', '=', 'subscriptions.tenant_id')
                    ->whereRaw('subscriptions.id = (SELECT id FROM subscriptions s2 WHERE s2.tenant_id = tenants.id ORDER BY s2.created_at DESC LIMIT 1)');
            })
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id');

        $hasUsersTenant = Schema::hasColumn('users', 'tenant_id');
        $hasPoolsTenant = Schema::hasColumn('pools', 'tenant_id');

        if ($hasUsersTenant) {
            $query->leftJoinSub(
                DB::table('users')
                    ->select('tenant_id', DB::raw('COUNT(*) as user_count'))
                    ->groupBy('tenant_id'),
                'tenant_user_counts',
                'tenant_user_counts.tenant_id',
                '=',
                'tenants.id',
            );
        }

        if ($hasPoolsTenant) {
            $query->leftJoinSub(
                DB::table('pools')
                    ->select('tenant_id', DB::raw('COUNT(*) as pool_count'))
                    ->groupBy('tenant_id'),
                'tenant_pool_counts',
                'tenant_pool_counts.tenant_id',
                '=',
                'tenants.id',
            );
        }

        $tenants = $query
            ->select(
                'tenants.id',
                'tenants.name',
                'tenants.slug',
                'tenants.status as tenant_status',
                'tenants.created_at',
                'subscriptions.status as subscription_status',
                'subscriptions.ends_at',
                'plans.name as plan_name',
                'plans.slug as plan_slug',
                $hasUsersTenant ? DB::raw('COALESCE(tenant_user_counts.user_count, 0) as user_count') : DB::raw('0 as user_count'),
                $hasPoolsTenant ? DB::raw('COALESCE(tenant_pool_counts.pool_count, 0) as pool_count') : DB::raw('0 as pool_count'),
            )
            ->orderBy('tenants.created_at', 'desc')
            ->limit(50)
            ->get();

        return $tenants->map(function ($t) {
            return [
                'id' => (int) $t->id,
                'name' => (string) $t->name,
                'slug' => (string) $t->slug,
                'tenant_status' => (string) $t->tenant_status,
                'subscription_status' => (string) ($t->subscription_status ?? 'inactive'),
                'plan_name' => (string) ($t->plan_name ?? '—'),
                'plan_slug' => (string) ($t->plan_slug ?? ''),
                'ends_at' => $t->ends_at,
                'user_count' => (int) ($t->user_count ?? 0),
                'pool_count' => (int) ($t->pool_count ?? 0),
                'created_at' => $t->created_at,
            ];
        })->all();
    }

    /**
     * Recently signed up tenants this month.
     */
    private function recentSignups(Carbon $monthStart): array
    {
        if (! Schema::hasTable('tenants')) {
            return [];
        }

        return DB::table('tenants')
            ->where('created_at', '>=', $monthStart)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'created_at'])
            ->map(function ($t) {
                return [
                    'id' => (int) $t->id,
                    'name' => (string) $t->name,
                    'slug' => (string) $t->slug,
                    'created_at' => $t->created_at,
                ];
            })
            ->all();
    }

    /**
     * Subscriptions expiring within the next 7 days.
     */
    private function expiringSoon(Carbon $today): array
    {
        if (! FeatureGate::ready()) {
            return [];
        }

        $expiryStart = $today->copy();
        $expiryEnd = $today->copy()->addDays(7);

        $subs = DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('subscriptions.status', ['active', 'trial', 'past_due'])
            ->whereBetween('subscriptions.ends_at', [$expiryStart->toDateString(), $expiryEnd->toDateString()])
            ->select(
                'subscriptions.id',
                'subscriptions.tenant_id',
                'tenants.name as tenant_name',
                'plans.name as plan_name',
                'subscriptions.status',
                'subscriptions.ends_at',
            )
            ->orderBy('subscriptions.ends_at')
            ->limit(10)
            ->get();

        return $subs->map(function ($s) use ($today) {
            $endsAt = $s->ends_at ? Carbon::parse($s->ends_at) : null;
            $daysLeft = $endsAt ? (int) $today->diffInDays($endsAt, false) : 0;

            return [
                'id' => (int) $s->id,
                'tenant_id' => (int) $s->tenant_id,
                'tenant_name' => (string) $s->tenant_name,
                'plan_name' => (string) $s->plan_name,
                'status' => (string) $s->status,
                'ends_at' => $s->ends_at,
                'days_left' => $daysLeft,
            ];
        })->all();
    }

    // ─── Query Helpers ─────────────────────────────────────────────

    private function paymentMetrics(Carbon $today): array
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return [
                'pending_count' => 0,
                'overdue_count' => 0,
                'paid_month_count' => 0,
                'paid_month_amount' => 0.0,
                'pending_amount' => 0.0,
            ];
        }

        $hasDueDate = Schema::hasColumn('invoice_subscriptions', 'due_date');
        $hasPaidAt = Schema::hasColumn('invoice_subscriptions', 'paid_at');

        $pending = DB::table('invoice_subscriptions')->where('status', 'pending');

        $overdue = DB::table('invoice_subscriptions')->where('status', 'overdue');
        if ($hasDueDate) {
            $overdue->orWhere(function ($query) use ($today): void {
                $query
                    ->where('status', 'pending')
                    ->whereDate('due_date', '<', $today->toDateString());
            });
        }

        $paidMonth = DB::table('invoice_subscriptions')->where('status', 'paid');
        if ($hasPaidAt) {
            $paidMonth->whereBetween('paid_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
        }

        return [
            'pending_count' => (int) $pending->count(),
            'overdue_count' => (int) $overdue->count(),
            'paid_month_count' => (int) (clone $paidMonth)->count(),
            'paid_month_amount' => (float) (clone $paidMonth)->sum('amount'),
            'pending_amount' => (float) DB::table('invoice_subscriptions')
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount'),
        ];
    }

    private function paymentWatchlist(Carbon $today): array
    {
        if (! Schema::hasTable('invoice_subscriptions') || ! Schema::hasTable('tenants')) {
            return [];
        }

        $hasDueDate = Schema::hasColumn('invoice_subscriptions', 'due_date');
        $hasGatewayStatus = Schema::hasColumn('invoice_subscriptions', 'gateway_status');

        $query = DB::table('invoice_subscriptions')
            ->join('tenants', 'invoice_subscriptions.tenant_id', '=', 'tenants.id')
            ->leftJoin('subscriptions', 'invoice_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('invoice_subscriptions.status', ['pending', 'overdue'])
            ->select(
                'invoice_subscriptions.id',
                'invoice_subscriptions.invoice_number',
                'invoice_subscriptions.amount',
                'invoice_subscriptions.status',
                Schema::hasColumn('invoice_subscriptions', 'due_date') ? 'invoice_subscriptions.due_date' : DB::raw('NULL as due_date'),
                $hasGatewayStatus ? 'invoice_subscriptions.gateway_status' : DB::raw('NULL as gateway_status'),
                'tenants.name as tenant_name',
                'plans.name as plan_name',
            )
            ->orderByRaw("CASE WHEN invoice_subscriptions.status = 'overdue' THEN 0 ELSE 1 END");

        if ($hasDueDate) {
            $query->orderBy('invoice_subscriptions.due_date');
        } else {
            $query->orderBy('invoice_subscriptions.created_at', 'desc');
        }

        return $query
            ->limit(8)
            ->get()
            ->map(function ($invoice) use ($today): array {
                $dueDate = $invoice->due_date ? Carbon::parse((string) $invoice->due_date) : null;

                return [
                    'id' => (int) $invoice->id,
                    'invoice_number' => (string) $invoice->invoice_number,
                    'tenant_name' => (string) $invoice->tenant_name,
                    'plan_name' => (string) ($invoice->plan_name ?? '-'),
                    'amount' => (float) $invoice->amount,
                    'status' => (string) $invoice->status,
                    'gateway_status' => (string) ($invoice->gateway_status ?? ''),
                    'due_date' => $invoice->due_date,
                    'days_overdue' => $dueDate ? max(0, (int) $dueDate->diffInDays($today, false)) : 0,
                ];
            })
            ->all();
    }

    private function computeMrr(): float
    {
        if (! FeatureGate::ready()) {
            return 0.0;
        }

        return (float) DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('subscriptions.status', ['trial', 'active'])
            ->sum('plans.price_monthly');
    }

    private function computeMrrAt(Carbon $date): float
    {
        if (! FeatureGate::ready()) {
            return 0.0;
        }

        return (float) DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('subscriptions.status', ['trial', 'active'])
            ->where('subscriptions.starts_at', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('subscriptions.ends_at')
                  ->orWhere('subscriptions.ends_at', '>=', $date->toDateString());
            })
            ->sum('plans.price_monthly');
    }

    private function countActiveTenants(): int
    {
        if (! FeatureGate::ready()) {
            return 0;
        }

        return (int) DB::table('subscriptions')
            ->whereIn('status', ['trial', 'active'])
            ->distinct('tenant_id')
            ->count('tenant_id');
    }

    private function countActiveTenantsAt(Carbon $date): int
    {
        if (! FeatureGate::ready()) {
            return 0;
        }

        return (int) DB::table('subscriptions')
            ->whereIn('status', ['trial', 'active'])
            ->where('starts_at', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $date->toDateString());
            })
            ->distinct('tenant_id')
            ->count('tenant_id');
    }

    private function computeTpv(Carbon $start, Carbon $end): float
    {
        $tpv = 0.0;

        if (Schema::hasTable('bookings')) {
            $tpv += (float) DB::table('bookings')
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                ->sum(DB::raw('COALESCE(price, 0) - COALESCE(discount, 0)'));
        }

        if (Schema::hasTable('charters')) {
            $tpv += (float) DB::table('charters')
                ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                ->sum('price');
        }

        if (Schema::hasTable('luggages')) {
            $tpv += (float) DB::table('luggages')
                ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                ->sum('price');
        }

        return $tpv;
    }

    private function countTrialsConverted(Carbon $start, Carbon $end): int
    {
        if (! FeatureGate::ready()) {
            return 0;
        }

        return (int) DB::table('subscriptions')
            ->where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('updated_at', [$start, $end])
            ->count();
    }

    private function countTrialsStarted(Carbon $start, Carbon $end): int
    {
        if (! FeatureGate::ready()) {
            return 0;
        }

        return (int) DB::table('subscriptions')
            ->where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function countChurned(Carbon $start, Carbon $end): int
    {
        if (! FeatureGate::ready()) {
            return 0;
        }

        return (int) DB::table('subscriptions')
            ->where('status', 'canceled')
            ->whereBetween('canceled_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
            ->count();
    }
}
