<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOpsMasterPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_super_admin' => true]));
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
}
