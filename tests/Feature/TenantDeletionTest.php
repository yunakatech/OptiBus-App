<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_superadmin_can_preview_tenant_deletion(): void
    {
        $tenantId = $this->createTenant('tenant-preview');
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => (int) DB::table('plans')->value('id'),
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'billing_interval' => 'monthly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create(['is_super_admin' => false, 'tenant_id' => $tenantId]);

        $this->actingAs($user)
            ->getJson(route('api.admin.tenants.deletion-preview', ['id' => $tenantId]))
            ->assertForbidden();
    }

    public function test_archive_keeps_tenant_data_and_cancels_subscription(): void
    {
        $tenantId = $this->createTenant('tenant-archive');
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Pool Archive',
            'code' => 'ARCHIVE',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $planId = (int) DB::table('plans')->value('id');
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'billing_interval' => 'monthly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsSuperAdminWithTenantContext(null);
        $this->postJson(route('api.admin.tenants.archive', ['id' => $tenantId]))
            ->assertOk();

        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'status' => 'canceled']);
        $this->assertDatabaseHas('pools', ['id' => $poolId, 'tenant_id' => $tenantId]);
        $this->assertDatabaseHas('subscriptions', ['tenant_id' => $tenantId, 'status' => 'canceled']);
    }

    public function test_preview_does_not_mutate_and_purge_removes_only_target_tenant(): void
    {
        $targetTenantId = $this->createTenant('tenant-purge-target');
        $otherTenantId = $this->createTenant('tenant-purge-other');
        $targetPoolId = $this->createPool($targetTenantId, 'Target Pool');
        $otherPoolId = $this->createPool($otherTenantId, 'Other Pool');

        $targetRouteId = DB::table('routes')->insertGetId([
            'tenant_id' => $targetTenantId,
            'name' => 'TARGET ROUTE',
            'origin' => 'A',
            'destination' => 'B',
            'created_at' => now(),
        ]);
        $otherRouteId = DB::table('routes')->insertGetId([
            'tenant_id' => $otherTenantId,
            'name' => 'OTHER ROUTE',
            'origin' => 'C',
            'destination' => 'D',
            'created_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            ['pool_id' => $targetPoolId, 'route_id' => $targetRouteId],
            ['pool_id' => $otherPoolId, 'route_id' => $otherRouteId],
        ]);

        $scheduleId = DB::table('schedules')->insertGetId([
            'tenant_id' => $targetTenantId,
            'route_id' => $targetRouteId,
            'rute' => 'TARGET ROUTE',
            'dow' => 1,
            'jam' => '09:00',
            'units' => 1,
            'unit_label' => 'Unit 1',
            'created_at' => now(),
        ]);
        $segmentId = DB::table('segments')->insertGetId([
            'tenant_id' => $targetTenantId,
            'route_id' => $targetRouteId,
            'rute' => 'TARGET ROUTE',
            'origin' => 'A',
            'destination' => 'B',
            'jam' => '09:00:00',
            'jam_pickups' => json_encode(['09:00']),
            'harga' => 100000,
            'created_at' => now(),
        ]);
        DB::table('schedule_units')->insert([
            'schedule_id' => $scheduleId,
            'unit_no' => 1,
            'label' => 'Unit 1',
            'created_at' => now(),
        ]);
        DB::table('schedule_segment')->insert([
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
            'jam_pickup' => '09:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bookingId = DB::table('bookings')->insertGetId([
            'tenant_id' => $targetTenantId,
            'route_id' => $targetRouteId,
            'rute' => 'TARGET ROUTE',
            'tanggal' => now()->toDateString(),
            'jam' => '09:00',
            'unit' => 1,
            'seat' => '1',
            'name' => 'Target Passenger',
            'phone' => '0800000001',
            'status' => 'active',
            'segment_id' => $segmentId,
            'price' => 100000,
            'discount' => 0,
            'created_at' => now(),
        ]);
        DB::table('cancellations')->insert([
            'booking_id' => $bookingId,
            'admin_user' => 'test',
            'created_at' => now(),
        ]);

        $targetUserId = DB::table('users')->insertGetId([
            'name' => 'Target User',
            'email' => 'target-purge@example.test',
            'password' => bcrypt('password'),
            'tenant_id' => $targetTenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_user')->insert([
            'pool_id' => $targetPoolId,
            'user_id' => $targetUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 1; $i <= 80; $i++) {
            DB::table('activity_logs')->insert([
                'tenant_id' => $targetTenantId,
                'tag' => 'TEST',
                'title' => "Target activity {$i}",
                'created_at' => now(),
            ]);
        }

        $this->actingAsSuperAdminWithTenantContext(null);
        $preview = $this->getJson(route('api.admin.tenants.deletion-preview', ['id' => $targetTenantId]))
            ->assertOk()
            ->json();
        $this->assertSame(1, $preview['counts']['bookings']);
        $this->assertDatabaseHas('tenants', ['id' => $targetTenantId, 'status' => 'active']);

        $job = $this->postJson(route('api.admin.tenants.purge', ['id' => $targetTenantId]), [
            'confirmation' => 'tenant-purge-target',
            'confirm_all' => true,
        ])->assertAccepted()->json('job');

        // Status polling must advance a batch without a background scheduler.
        $this->getJson(route('api.admin.tenant-deletions.status', ['jobId' => $job['id']]))
            ->assertOk()
            ->assertJsonPath('job.id', $job['id'])
            ->assertJsonPath('job.status', 'running');

        $service = app(TenantDeletionService::class);
        for ($i = 0; $i < 100; $i++) {
            $service->processNextBatch((int) $job['id']);
            $status = $service->job((int) $job['id']);
            if (($status->status ?? '') === 'completed') {
                break;
            }
        }

        $this->assertSame('completed', $service->job((int) $job['id'])->status);
        $this->assertDatabaseMissing('tenants', ['id' => $targetTenantId]);
        $this->assertDatabaseMissing('pools', ['id' => $targetPoolId]);
        $this->assertDatabaseMissing('routes', ['id' => $targetRouteId]);
        $this->assertDatabaseMissing('bookings', ['id' => $bookingId]);
        $this->assertDatabaseMissing('users', ['id' => $targetUserId]);
        $this->assertDatabaseHas('tenants', ['id' => $otherTenantId]);
        $this->assertDatabaseHas('pools', ['id' => $otherPoolId, 'tenant_id' => $otherTenantId]);
        $this->assertDatabaseHas('routes', ['id' => $otherRouteId, 'tenant_id' => $otherTenantId]);
        $this->assertDatabaseHas('tenant_deletion_jobs', [
            'id' => $job['id'],
            'status' => 'completed',
        ]);
    }

    public function test_tenant_write_is_blocked_while_tenant_is_deleting(): void
    {
        $tenantId = $this->createTenant('tenant-deleting');
        DB::table('tenants')->where('id', $tenantId)->update(['status' => 'deleting']);
        $this->actingAsSuperAdminWithTenantContext($tenantId);

        $this->postJson(route('api.admin.routes.save'), [
            'name' => 'BLOCKED ROUTE',
            'origin' => 'A',
            'destination' => 'B',
        ])->assertStatus(409)->assertJsonPath('action_required', 'tenant_deleting');
    }

    private function createTenant(string $slug): int
    {
        return (int) DB::table('tenants')->insertGetId([
            'name' => strtoupper(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPool(int $tenantId, string $name): int
    {
        return (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => strtoupper(str_replace(' ', '-', $name)),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
