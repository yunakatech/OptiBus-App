<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminOpsOperationalMenusSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_operational_and_master_menu_pages_return_success(): void
    {
        $this->actingAsSuperAdmin();

        $routes = [
            'charters.index',
            'luggages.index',
            'admin-ops.customers',
            'admin-ops.master.customer-bagasi',
            'admin-ops.master.customer-charter',
            'admin-ops.master.rute-carter',
            'admin-ops.routes',
            'admin-ops.schedules',
            'admin-ops.drivers',
            'admin-ops.services',
            'admin-ops.segments',
            'admin-ops.units',
            'admin-ops.armadas',
            'admin-ops.pools',
            'admin-ops.users',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_legacy_admin_segments_redirects_to_the_new_segments_page(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/segments')
            ->assertRedirect(route('admin-ops.segments'));
    }

    public function test_legacy_admin_menu_paths_redirect_to_new_operational_routes(): void
    {
        $this->actingAsSuperAdmin();

        $redirects = [
            '/admin/charters' => route('charters.index'),
            '/admin/luggages' => route('luggages.index'),
            '/admin/luggage-services' => route('admin-ops.services'),
            '/admin/customers' => route('admin-ops.customers'),
            '/admin/admin-ops/customers' => route('admin-ops.customers'),
            '/admin/customer-bagasi' => route('admin-ops.master.customer-bagasi'),
            '/admin/customer-charter' => route('admin-ops.master.customer-charter'),
            '/admin/rute-carter' => route('admin-ops.master.rute-carter'),
            '/admin/master' => route('admin-ops.master'),
            '/admin/admin-ops/pool' => route('admin-ops.pools'),
        ];

        foreach ($redirects as $legacyPath => $target) {
            $this->get($legacyPath)->assertRedirect($target);
        }
    }
}
