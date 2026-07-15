<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AdminOpsApiController;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\TestCase;

class AdminOpsFlowsPageTest extends TestCase
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

    public function test_luggage_page_deferred_props_tolerate_legacy_schema_without_route_id_and_services_table(): void
    {
        $this->actingAsSuperAdmin();

        if (Schema::hasColumn('luggages', 'rute_id')) {
            Schema::table('luggages', function (Blueprint $table): void {
                $table->dropColumn('rute_id');
            });
        }

        Schema::dropIfExists('luggage_services');

        $this->get(route('luggages.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Luggages')
                ->where('initialTab', 'luggages')
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

    public function test_charter_page_deferred_props_tolerate_missing_charter_and_route_master_tables(): void
    {
        $this->actingAsSuperAdmin();

        Schema::dropIfExists('charters');
        Schema::dropIfExists('master_carter');

        $this->get(route('charters.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminOpsFlows')
                ->where('initialTab', 'charters')
                ->loadDeferredProps('flow-data', fn (Assert $reload) => $reload
                    ->where('flowData.tab', 'charters')
                    ->has('flowData.charters')
                    ->where('flowData.charters', [])
                    ->where('flowData.pagination.total', 0))
                ->loadDeferredProps('flow-masters', fn (Assert $reload) => $reload
                    ->where('flowMasters.tab', 'charters')
                    ->where('flowMasters.units', [])
                    ->where('flowMasters.drivers', [])
                    ->where('flowMasters.charterRoutes', [])
                    ->where('flowMasters.pools', [])),
            );
    }

    public function test_luggage_page_deferred_props_tolerate_runtime_errors_in_flow_data(): void
    {
        $this->actingAsSuperAdmin();

        $mock = Mockery::mock(AdminOpsApiController::class);
        $mock->shouldReceive('luggagesIndex')
            ->once()
            ->andThrow(new \RuntimeException('Simulated luggage flow failure.'));
        $this->app->instance(AdminOpsApiController::class, $mock);

        $this->get(route('luggages.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Luggages')
                ->where('initialTab', 'luggages')
                ->loadDeferredProps('flow-data', fn (Assert $reload) => $reload
                    ->where('flowData.tab', 'luggages')
                    ->where('flowData.luggages', [])
                    ->where('flowData.pagination.total', 0)),
            );
    }

    public function test_pool_operator_gets_pool_scope_and_route_labels_on_flow_page(): void
    {
        AccessControl::syncDefaults();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
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

        $operator = User::factory()->create(['is_super_admin' => false, 'tenant_id' => $tenantId]);
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
                    ->where('flowMasters.pools.0.route_names', fn (mixed $routeNames): bool => collect($routeNames)->contains('PINRANG - MAKASSAR'))),
            );
    }
}
