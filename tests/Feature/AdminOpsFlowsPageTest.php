<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_pool_operator_gets_pool_scope_and_route_labels_on_flow_page(): void
    {
        AccessControl::syncDefaults();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'name' => 'POOL PINRANG',
            'code' => 'PNR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operator = User::factory()->create(['is_super_admin' => false]);
        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => DB::table('roles')->where('slug', 'operator-carter')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($operator);

        $this->get(route('charters.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminOpsFlows')
                ->where('auth.pool_scope.all', false)
                ->where('auth.pool_scope.pool_name', 'POOL PINRANG')
                ->where('auth.pool_scope.pool_ids.0', $poolId)
                ->where('auth.pool_scope.route_ids.0', $routeId)
                ->where('auth.pool_scope.route_names', fn (mixed $routeNames): bool => collect($routeNames)->contains('PINRANG - MAKASSAR'))
                ->loadDeferredProps('flow-masters', fn (Assert $reload) => $reload
                    ->where('flowMasters.pools.0.route_names', fn (mixed $routeNames): bool => collect($routeNames)->contains('PINRANG')
                        && collect($routeNames)->contains('MAKASSAR')
                        && collect($routeNames)->contains('PINRANG - MAKASSAR'))),
            );
    }
}
