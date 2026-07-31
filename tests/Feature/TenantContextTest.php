<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TenantWriteContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-01 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_super_admin_without_tenant_can_read_tenant_operational_lists(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);

        $this->actingAs($admin)
            ->getJson(route('api.admin.users.index'))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_super_admin_without_tenant_cannot_write_operational_data(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);

        $this->actingAs($admin)
            ->postJson(route('api.bookings.submit'), [])
            ->assertStatus(409)
            ->assertJsonPath('error', TenantWriteContext::MESSAGE)
            ->assertJsonPath('action_required', TenantWriteContext::ACTION_REQUIRED);

        $this->actingAs($admin)
            ->postJson(route('api.ops.luggages.submit'), [])
            ->assertStatus(409)
            ->assertJsonPath('error', TenantWriteContext::MESSAGE)
            ->assertJsonPath('action_required', TenantWriteContext::ACTION_REQUIRED);

        $this->actingAs($admin)
            ->postJson(route('api.ops.charters.submit'), [])
            ->assertStatus(409)
            ->assertJsonPath('error', TenantWriteContext::MESSAGE)
            ->assertJsonPath('action_required', TenantWriteContext::ACTION_REQUIRED);
    }

    public function test_super_admin_with_active_tenant_writes_to_selected_tenant(): void
    {
        $tenantId = $this->defaultTestTenantId();
        $admin = User::factory()->create(['is_super_admin' => true]);
        $date = '2026-05-15';
        $routeName = 'PINRANG - MAKASSAR';
        $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $routeName,
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => $routeName,
            'dow' => $dow,
            'jam' => '09:00:00',
            'units' => 1,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['active_tenant_id' => $tenantId])
            ->postJson(route('api.bookings.empty-departure'), [
                'rute' => $routeName,
                'tanggal' => $date,
                'jam' => '09:00',
                'unit' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('trip_assignments', [
            'tenant_id' => $tenantId,
            'rute' => $routeName,
            'tanggal' => $date,
            'unit' => 1,
        ]);
    }
}
