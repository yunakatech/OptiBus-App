<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOpsMasterPageTest extends TestCase
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

    public function test_customer_bagasi_page_exposes_deferred_master_data(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin-ops.master.customer-bagasi'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('CustomerBagasi')
                ->where('initialTab', 'customer-bagasi')
                ->missing('masterData')
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'customer-bagasi')
                    ->has('masterData.customers')
                    ->has('masterData.pagination')),
            );
    }

    public function test_customer_charter_page_exposes_deferred_master_data(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin-ops.master.customer-charter'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('CustomerCarter')
                ->where('initialTab', 'customer-charter')
                ->missing('masterData')
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'customer-charter')
                    ->has('masterData.customers')
                    ->has('masterData.pagination')),
            );
    }

    public function test_carter_route_page_exposes_deferred_master_data(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin-ops.master.rute-carter'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('PengaturanRuteCarter')
                ->where('initialTab', 'rute-carter')
                ->missing('masterData')
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'rute-carter')
                    ->has('masterData.routes')
                    ->has('masterData.pagination')),
            );
    }

    public function test_master_pages_deferred_props_tolerate_missing_legacy_tables(): void
    {
        $this->actingAsSuperAdmin();

        Schema::dropIfExists('customer_bagasi');
        Schema::dropIfExists('customer_charter');
        Schema::dropIfExists('master_carter');

        $this->get(route('admin-ops.master.customer-bagasi'))
            ->assertInertia(fn (Assert $page) => $page
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'customer-bagasi')
                    ->where('masterData.customers', [])
                    ->where('masterData.pagination.total', 0)),
            );

        $this->get(route('admin-ops.master.customer-charter'))
            ->assertInertia(fn (Assert $page) => $page
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'customer-charter')
                    ->where('masterData.customers', [])
                    ->where('masterData.pagination.total', 0)),
            );

        $this->get(route('admin-ops.master.rute-carter'))
            ->assertInertia(fn (Assert $page) => $page
                ->loadDeferredProps('master-data', fn (Assert $reload) => $reload
                    ->where('masterData.tab', 'rute-carter')
                    ->where('masterData.routes', [])
                    ->where('masterData.pagination.total', 0)),
            );
    }
}
