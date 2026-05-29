<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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
        $user = User::factory()->create();
        $this->actingAs($user);

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

    public function test_legacy_tab_urls_redirect_to_new_per_menu_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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
