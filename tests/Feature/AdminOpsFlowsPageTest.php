<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOpsFlowsPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_super_admin' => true]));
    }

    public function test_charter_page_exposes_deferred_flow_data_and_masters(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('charters.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminOpsFlows')
                ->where('initialTab', 'charters')
                ->missingAll(['flowData', 'flowMasters'])
                ->loadDeferredProps('flow-data', fn (Assert $reload) => $reload
                    ->where('flowData.tab', 'charters')
                    ->has('flowData.charters')
                    ->has('flowData.pagination'))
                ->loadDeferredProps('flow-masters', fn (Assert $reload) => $reload
                    ->where('flowMasters.tab', 'charters')
                    ->has('flowMasters.units')
                    ->has('flowMasters.drivers')
                    ->has('flowMasters.charterRoutes')
                    ->has('flowMasters.pools')),
            );
    }

    public function test_luggage_page_exposes_deferred_flow_data_and_masters(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('luggages.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Luggages')
                ->where('initialTab', 'luggages')
                ->missingAll(['flowData', 'flowMasters'])
                ->loadDeferredProps('flow-data', fn (Assert $reload) => $reload
                    ->where('flowData.tab', 'luggages')
                    ->has('flowData.luggages')
                    ->has('flowData.pagination'))
                ->loadDeferredProps('flow-masters', fn (Assert $reload) => $reload
                    ->where('flowMasters.tab', 'luggages')
                    ->has('flowMasters.services')
                    ->has('flowMasters.pools')
                    ->has('flowMasters.routes')),
            );
    }
}
