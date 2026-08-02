<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlatformDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private const PAID_BOOKING_STATUSES = ['lunas', 'redbus', 'traveloka', 'qris', 'transfer', 'transfer bju', 'tunai'];

    public function test_platform_dashboard_ignores_trial_tenant_revenue_from_metrics(): void
    {
        Carbon::setTestNow('2026-06-05 10:00:00');
        $this->actingAsSuperAdminWithTenantContext($this->defaultTestTenantId());

        try {
            $activeTenantId = $this->tenantIdBySlug('platform-active');
            $trialTenantId = $this->tenantIdBySlug('platform-trial');

            $this->updateSubscription($activeTenantId, 'active', 250000);
            $this->updateSubscription(
                $trialTenantId,
                'trial',
                999999,
                now()->addDays(14)->toDateString(),
            );

            DB::table('bookings')->insert([
                [
                    'tenant_id' => $activeTenantId,
                    'rute' => 'PLATFORM ACTIVE ROUTE',
                    'tanggal' => '2026-06-05',
                    'jam' => '09:00:00',
                    'unit' => 1,
                    'seat' => 'A1',
                    'name' => 'AKTIF DASHBOARD',
                    'phone' => '081200000001',
                    'pickup_point' => 'Terminal',
                    'pembayaran' => 'Lunas',
                    'status' => 'active',
                    'price' => 120000,
                    'discount' => 30000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $activeTenantId,
                    'rute' => 'PLATFORM ACTIVE ROUTE',
                    'tanggal' => '2026-06-05',
                    'jam' => '09:15:00',
                    'unit' => 1,
                    'seat' => 'A3',
                    'name' => 'AKTIF DASHBOARD UNPAID',
                    'phone' => '081200000003',
                    'pickup_point' => 'Terminal',
                    'pembayaran' => 'Belum Lunas',
                    'status' => 'active',
                    'price' => 500000,
                    'discount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $trialTenantId,
                    'rute' => 'PLATFORM TRIAL ROUTE',
                    'tanggal' => '2026-06-05',
                    'jam' => '09:30:00',
                    'unit' => 1,
                    'seat' => 'A2',
                    'name' => 'TRIAL DASHBOARD',
                    'phone' => '081200000002',
                    'pickup_point' => 'Terminal',
                    'pembayaran' => 'Lunas',
                    'status' => 'active',
                    'price' => 1200000,
                    'discount' => 200000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('charters')->insert([
                [
                    'tenant_id' => $activeTenantId,
                    'name' => 'CARTER AKTIF PLATFORM',
                    'start_date' => '2026-06-05',
                    'end_date' => '2026-06-05',
                    'price' => 200000,
                    'down_payment' => 0,
                    'payment_status' => 'Lunas',
                    'status' => 'active',
                    'created_at' => now(),
                ],
                [
                    'tenant_id' => $activeTenantId,
                    'name' => 'CARTER AKTIF PLATFORM DP',
                    'start_date' => '2026-06-05',
                    'end_date' => '2026-06-05',
                    'price' => 600000,
                    'down_payment' => 150000,
                    'payment_status' => 'DP',
                    'status' => 'active',
                    'created_at' => now(),
                ],
                [
                    'tenant_id' => $trialTenantId,
                    'name' => 'CARTER TRIAL PLATFORM',
                    'start_date' => '2026-06-05',
                    'end_date' => '2026-06-05',
                    'price' => 800000,
                    'down_payment' => 0,
                    'payment_status' => 'Lunas',
                    'status' => 'active',
                    'created_at' => now(),
                ],
            ]);

            DB::table('luggages')->insert([
                [
                    'tenant_id' => $activeTenantId,
                    'sender_name' => 'PENGIRIM AKTIF',
                    'sender_phone' => '081300000001',
                    'receiver_name' => 'PENERIMA AKTIF',
                    'receiver_phone' => '081400000001',
                    'price' => 300000,
                    'status' => 'Diterima',
                    'payment_status' => 'Lunas',
                    'created_at' => now(),
                ],
                [
                    'tenant_id' => $activeTenantId,
                    'sender_name' => 'PENGIRIM AKTIF UNPAID',
                    'sender_phone' => '081300000003',
                    'receiver_name' => 'PENERIMA AKTIF UNPAID',
                    'receiver_phone' => '081400000003',
                    'price' => 900000,
                    'status' => 'Diterima',
                    'payment_status' => 'Belum Bayar',
                    'created_at' => now(),
                ],
                [
                    'tenant_id' => $trialTenantId,
                    'sender_name' => 'PENGIRIM TRIAL',
                    'sender_phone' => '081300000002',
                    'receiver_name' => 'PENERIMA TRIAL',
                    'receiver_phone' => '081400000002',
                    'price' => 700000,
                    'status' => 'Diterima',
                    'payment_status' => 'Lunas',
                    'created_at' => now(),
                ],
            ]);

            $expectedMrr = $this->activeSubscriptionRevenue();
            $expectedActiveTenants = $this->activeSubscriptionTenantCount();
            $expectedTpv = $this->activeOperationalRevenue();

            $this->get(route('platform.dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('PlatformDashboard')
                    ->where('metrics.mrr', (int) round($expectedMrr))
                    ->where('metrics.active_tenants', $expectedActiveTenants)
                    ->where('metrics.arr', (int) round($expectedMrr * 12))
                    ->where('metrics.arpu', (int) round($expectedActiveTenants > 0 ? ($expectedMrr / $expectedActiveTenants) : 0, 0))
                    ->where('metrics.tpv_month', (int) round($expectedTpv)));
        } finally {
            Carbon::setTestNow();
        }
    }

    private function tenantIdBySlug(string $slug): int
    {
        $existing = (int) (DB::table('tenants')->where('slug', $slug)->value('id') ?? 0);
        if ($existing > 0) {
            $this->ensureTenantSubscription($existing);

            return $existing;
        }

        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => strtoupper(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->ensureTenantSubscription($tenantId);

        return $tenantId;
    }

    private function ensureTenantSubscription(int $tenantId): void
    {
        if (DB::table('subscriptions')->where('tenant_id', $tenantId)->exists()) {
            return;
        }

        $planId = (int) DB::table('plans')->where('slug', 'starter')->value('id');
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function updateSubscription(
        int $tenantId,
        string $status,
        int $customPriceMonthly,
        ?string $trialEndsAt = null,
    ): void {
        $subscription = DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->first();

        if (! $subscription) {
            $this->ensureTenantSubscription($tenantId);

            $subscription = DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->first();
        }

        DB::table('subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => $status,
                'custom_price_monthly' => $customPriceMonthly,
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => now()->toDateString(),
                'ends_at' => $trialEndsAt ?? now()->addMonth()->toDateString(),
                'updated_at' => now(),
            ]);
    }

    private function activeSubscriptionTenantCount(): int
    {
        return (int) DB::table('subscriptions')
            ->where('status', 'active')
            ->distinct('tenant_id')
            ->count('tenant_id');
    }

    private function activeSubscriptionRevenue(): float
    {
        return (float) DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->sum(DB::raw('COALESCE(subscriptions.custom_price_monthly, plans.price_monthly)'));
    }

    private function activeOperationalRevenue(): float
    {
        $tenantIds = DB::table('subscriptions')
            ->select('tenant_id')
            ->where('status', 'active')
            ->distinct();

        $total = 0.0;

        if (DB::table('bookings')->exists()) {
            $total += (float) DB::table('bookings')
                ->whereIn('tenant_id', $tenantIds)
                ->where('status', '!=', 'canceled')
                ->whereBetween('tanggal', ['2026-06-01', '2026-06-30'])
                ->sum(DB::raw(
                    "CASE
                        WHEN LOWER(COALESCE(pembayaran, '')) IN ('".implode("','", self::PAID_BOOKING_STATUSES)."')
                        THEN COALESCE(price, 0) - COALESCE(discount, 0)
                        ELSE 0
                    END",
                ));
        }

        if (DB::table('charters')->exists()) {
            $total += (float) DB::table('charters')
                ->whereIn('tenant_id', DB::table('subscriptions')
                    ->select('tenant_id')
                    ->where('status', 'active')
                    ->distinct())
                ->whereBetween('start_date', ['2026-06-01', '2026-06-30'])
                ->sum(DB::raw(
                    "CASE
                        WHEN LOWER(COALESCE(payment_status, '')) = 'lunas' THEN COALESCE(price, 0)
                        WHEN LOWER(COALESCE(payment_status, '')) = 'dp' THEN COALESCE(down_payment, 0)
                        ELSE 0
                    END",
                ));
        }

        if (DB::table('luggages')->exists()) {
            $total += (float) DB::table('luggages')
                ->whereIn('tenant_id', DB::table('subscriptions')
                    ->select('tenant_id')
                    ->where('status', 'active')
                    ->distinct())
                ->whereBetween('tanggal', ['2026-06-01', '2026-06-30'])
                ->sum(DB::raw(
                    "CASE
                        WHEN LOWER(COALESCE(payment_status, '')) = 'lunas' THEN COALESCE(price, 0)
                        ELSE 0
                    END",
                ));
        }

        return $total;
    }
}
