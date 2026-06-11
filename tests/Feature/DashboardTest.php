<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $console = $this->get(route('booking-console.index'));
        $console->assertRedirect(route('login'));

        $ops = $this->get(route('admin-ops.index'));
        $ops->assertRedirect(route('login'));

        $opsFlows = $this->get(route('admin-ops.flows'));
        $opsFlows->assertRedirect(route('login'));

        $opsMaster = $this->get(route('admin-ops.master'));
        $opsMaster->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAsSuperAdmin();

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        $console = $this->get(route('booking-console.index'));
        $console->assertOk();

        $ops = $this->get(route('admin-ops.index'));
        $ops->assertOk();

        $opsFlows = $this->get(route('admin-ops.flows'));
        $opsFlows->assertOk();

        $opsMaster = $this->get(route('admin-ops.master'));
        $opsMaster->assertOk();
    }

    public function test_dashboard_exposes_aggregated_summary_and_one_deferred_data_group(): void
    {
        Carbon::setTestNow('2026-06-05 10:00:00');
        $this->actingAsSuperAdmin();

        try {
            DB::table('bookings')->insert([
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-06-05',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'PENUMPANG DASHBOARD',
                'phone' => '081200000099',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 100000,
                'discount' => 10000,
                'created_at' => now(),
            ]);
            DB::table('charters')->insert([
                'name' => 'CARTER DASHBOARD',
                'start_date' => '2026-06-05',
                'end_date' => '2026-06-05',
                'price' => 200000,
                'status' => 'active',
                'created_at' => now(),
            ]);
            DB::table('luggages')->insert([
                'sender_name' => 'PENGIRIM DASHBOARD',
                'sender_phone' => '081300000099',
                'receiver_name' => 'PENERIMA DASHBOARD',
                'receiver_phone' => '081400000099',
                'price' => 300000,
                'status' => 'Diterima',
                'payment_status' => 'Lunas',
                'created_at' => now(),
            ]);
            DB::table('luggages')->insert([
                'sender_name' => 'PENGIRIM BELUM BAYAR DASHBOARD',
                'sender_phone' => '081300000100',
                'receiver_name' => 'PENERIMA BELUM BAYAR DASHBOARD',
                'receiver_phone' => '081400000100',
                'price' => 50000,
                'status' => 'Dalam Perjalanan',
                'payment_status' => 'Belum Bayar',
                'created_at' => now(),
            ]);
            DB::table('luggages')->insert([
                'sender_name' => 'PENGIRIM CANCELED DASHBOARD',
                'sender_phone' => '081300000101',
                'receiver_name' => 'PENERIMA CANCELED DASHBOARD',
                'receiver_phone' => '081400000101',
                'price' => 70000,
                'status' => 'canceled',
                'payment_status' => 'Belum Bayar',
                'created_at' => now(),
            ]);

            $this->get(route('dashboard'))
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Dashboard')
                    ->where('stats.revenue_total_today', 640000)
                    ->where('stats.revenue_luggage_month', 350000)
                    ->where('summaryStatsByScope.day.total_bookings', 1)
                    ->where('summaryStatsByScope.day.revenue_booking', 90000)
                    ->where('summaryStatsByScope.day.revenue_luggage', 350000)
                    ->missingAll([
                        'dailyTrend',
                        'monthlyTrend',
                        'recentActivity',
                        'recentActivityTotal',
                        'recentActivityVisibleCount',
                        'departuresToday',
                        'upcomingCharterReminder',
                    ])
                    ->loadDeferredProps('dashboard-data', fn (Assert $reload) => $reload
                        ->has('dailyTrend')
                        ->has('monthlyTrend')
                        ->has('recentActivity')
                        ->has('recentActivityTotal')
                        ->has('recentActivityVisibleCount')
                        ->has('departuresToday')
                        ->has('upcomingCharterReminder')),
                );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sidebar_pool_switcher_keeps_all_accessible_pools_when_one_pool_is_active(): void
    {
        [$user, $poolA, $poolB] = $this->tenantUserWithPools();

        $this->actingAs($user)
            ->withSession(['active_pool_id' => $poolA])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.pools', 2)
                ->where('auth.active_pool.id', $poolA)
                ->where('auth.pools.0.id', $poolA)
                ->where('auth.pools.1.id', $poolB));
    }

    public function test_dashboard_target_and_activity_are_isolated_between_tenants_with_same_route_name(): void
    {
        Carbon::setTestNow('2026-06-05 10:00:00');

        try {
            [$user, $tenantPool] = $this->tenantUserWithSinglePool('tenant-a', 1000000);
            [, $otherPool] = $this->tenantWithSinglePool('tenant-b', 9000000);

            $routeName = 'PINRANG - MAKASSAR';
            $this->createRouteForPool('tenant-a', $tenantPool, $routeName, 1000000);
            $this->createRouteForPool('tenant-b', $otherPool, $routeName, 9000000);

            DB::table('activity_logs')->insert([
                [
                    'tenant_id' => $this->tenantIdBySlug('tenant-b'),
                    'tag' => 'BOOKING',
                    'title' => 'Log tenant B',
                    'meta' => $routeName,
                    'actor' => 'tenant-b@example.test',
                    'extra' => json_encode(['rute' => $routeName, 'tenant_id' => $this->tenantIdBySlug('tenant-b')]),
                    'created_at' => now()->subMinute(),
                ],
                [
                    'tenant_id' => $this->tenantIdBySlug('tenant-a'),
                    'tag' => 'BOOKING',
                    'title' => 'Log tenant A',
                    'meta' => $routeName,
                    'actor' => 'tenant-a@example.test',
                    'extra' => json_encode(['rute' => $routeName, 'tenant_id' => $this->tenantIdBySlug('tenant-a')]),
                    'created_at' => now(),
                ],
            ]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('stats.target_revenue_month', 1000000)
                    ->loadDeferredProps('dashboard-data', fn (Assert $reload) => $reload
                        ->where('recentActivityTotal', 1)
                        ->where('recentActivity.0.title', 'Log tenant A')));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_legacy_tab_urls_redirect_to_new_per_menu_routes(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin-ops?tab=units')
            ->assertRedirect(route('admin-ops.units'));

        $this->get('/admin-ops?tab=reports&from=2026-05-01&to=2026-05-15')
            ->assertRedirect(route('report.index', [
                'from' => '2026-05-01',
                'to' => '2026-05-15',
            ]));

        $this->get('/admin-ops/flows?tab=assignments')
            ->assertRedirect(route('admin-ops.flows.assignments'));

        $this->get('/admin-ops/master?tab=rute-carter')
            ->assertRedirect(route('admin-ops.master.rute-carter'));
    }

    /**
     * @return array{0: User, 1: int, 2: int}
     */
    private function tenantUserWithPools(): array
    {
        $tenantId = $this->tenantIdBySlug('switcher-tenant');
        $poolA = $this->createPool($tenantId, 'POOL A', 'switch-a', 100000);
        $poolB = $this->createPool($tenantId, 'POOL B', 'switch-b', 200000);
        $this->createRouteForPool('switcher-tenant', $poolA, 'SWITCH A - MAKASSAR', 100000);
        $this->createRouteForPool('switcher-tenant', $poolB, 'SWITCH B - MAKASSAR', 200000);

        $user = User::factory()->create([
            'tenant_id' => $tenantId,
            'is_super_admin' => false,
        ]);
        $this->assignAdminPoolRole($user);
        foreach ([$poolA, $poolB] as $poolId) {
            DB::table('pool_user')->insert([
                'pool_id' => $poolId,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$user, $poolA, $poolB];
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function tenantUserWithSinglePool(string $slug, int $targetRevenue): array
    {
        [$tenantId, $poolId] = $this->tenantWithSinglePool($slug, $targetRevenue);
        $user = User::factory()->create([
            'tenant_id' => $tenantId,
            'is_super_admin' => false,
        ]);
        $this->assignAdminPoolRole($user);
        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $poolId];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function tenantWithSinglePool(string $slug, int $targetRevenue): array
    {
        $tenantId = $this->tenantIdBySlug($slug);
        $poolId = $this->createPool($tenantId, strtoupper($slug).' POOL', $slug.'-pool', $targetRevenue);

        return [$tenantId, $poolId];
    }

    private function tenantIdBySlug(string $slug): int
    {
        $existing = (int) (DB::table('tenants')->where('slug', $slug)->value('id') ?? 0);
        if ($existing > 0) {
            return $existing;
        }

        return (int) DB::table('tenants')->insertGetId([
            'name' => strtoupper(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPool(int $tenantId, string $name, string $code, int $targetRevenue): int
    {
        return (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => $code,
            'status' => 'active',
            'target_revenue' => $targetRevenue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRouteForPool(string $tenantSlug, int $poolId, string $name, int $targetRevenue): int
    {
        $routeId = (int) DB::table('routes')->insertGetId([
            'tenant_id' => $this->tenantIdBySlug($tenantSlug),
            'name' => $name,
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'target_revenue' => $targetRevenue,
            'created_at' => now(),
        ]);

        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $routeId;
    }

    private function assignAdminPoolRole(User $user): void
    {
        $roleId = (int) (DB::table('roles')->where('slug', 'admin-pool')->value('id') ?? 0);
        if ($roleId <= 0) {
            return;
        }

        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
