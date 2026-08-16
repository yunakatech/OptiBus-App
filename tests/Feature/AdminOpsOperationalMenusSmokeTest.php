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
            'report.index',
            'reports.index',
            'settings.customers',
            'settings.master.customer-bagasi',
            'settings.master.customer-charter',
            'settings.master.rute-carter',
            'settings.routes',
            'settings.schedules',
            'settings.drivers',
            'settings.services',
            'settings.segments',
            'settings.units',
            'settings.armadas',
            'settings.pools',
            'settings.users',
            'settings.logs',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_legacy_admin_segments_redirects_to_the_new_segments_page(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/segments')
            ->assertRedirect(route('settings.segments'));
    }

    public function test_legacy_admin_menu_paths_redirect_to_new_operational_routes(): void
    {
        $this->actingAsSuperAdmin();

        $redirects = [
            '/admin/charters' => route('charters.index'),
            '/admin/luggages' => route('luggages.index'),
            '/admin/luggage-services' => route('settings.services'),
            '/admin/customers' => route('settings.customers'),
            '/admin/admin-ops/customers' => route('settings.customers'),
            '/admin/admin-ops/admin-ops/customers' => route('settings.customers'),
            '/admin/customer-bagasi' => route('settings.master.customer-bagasi'),
            '/admin/customer-charter' => route('settings.master.customer-charter'),
            '/admin/rute-carter' => route('settings.master.rute-carter'),
            '/admin/logs' => route('settings.logs'),
            '/admin/master' => route('settings.master'),
            '/admin/admin-ops/pool' => route('settings.pools'),
            '/admin/admin-ops/admin-ops/pool' => route('settings.pools'),
            '/admin/admin-ops/admin-ops/reports' => route('settings.reports'),
        ];

        foreach ($redirects as $legacyPath => $target) {
            $this->get($legacyPath)->assertRedirect($target);
        }

        $this->get('/admin/cancellations')->assertNotFound();
        $this->get('/admin-ops/cancellations')->assertNotFound();
    }

    public function test_legacy_admin_report_api_paths_still_work_for_ajax_requests(): void
    {
        $this->actingAsSuperAdmin();

        $headers = [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];

        $this->get('/admin/pools', $headers)
            ->assertOk()
            ->assertJsonStructure(['pools']);

        $this->get('/admin/reports/summary?from='.now()->toDateString().'&to='.now()->toDateString().'&type=booking', $headers)
            ->assertOk()
            ->assertJsonStructure([
                'summary' => ['type', 'total_rows', 'revenue_total'],
                'rows',
                'pagination',
            ]);

        $this->get(route('admin/activity-logs', [], false).'?limit=1', $headers)
            ->assertOk()
            ->assertJsonStructure(['logs']);
    }
}
