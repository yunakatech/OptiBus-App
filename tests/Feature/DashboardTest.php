<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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

            $this->get(route('dashboard'))
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Dashboard')
                    ->where('stats.revenue_total_today', 590000)
                    ->where('summaryStatsByScope.day.total_bookings', 1)
                    ->where('summaryStatsByScope.day.revenue_booking', 90000)
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
}
