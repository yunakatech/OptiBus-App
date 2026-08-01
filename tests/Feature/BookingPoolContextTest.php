<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\BookingPoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingPoolContextTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        return $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_booking_console_requires_specific_pool_when_multiple_pools_are_available(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        $this->createPool($tenantId, 'Cabo Pinrang Pool', 'CPR');
        $this->createPool($tenantId, 'Cabo Makassar Pool', 'CMK');

        $this->get(route('booking-console.index'))
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas('error', BookingPoolContext::MESSAGE);
    }

    public function test_booking_console_uses_valid_default_pool_preference(): void
    {
        $user = $this->actingAsSuperAdmin();
        $poolId = $this->createPool($this->defaultTenantId(), 'Cabo Pinrang Pool', 'CPR');
        $this->createPool($this->defaultTenantId(), 'Cabo Makassar Pool', 'CMK');
        $user->forceFill([
            'ui_preferences' => [
                'defaultPoolId' => $poolId,
            ],
        ])->save();

        $this->get(route('booking-console.index'))
            ->assertOk();

        $this->assertSame($poolId, (int) session('active_pool_id'));
    }

    public function test_user_can_store_default_pool_preference_when_pool_is_accessible(): void
    {
        $user = $this->actingAsSuperAdmin();
        $poolId = $this->createPool($this->defaultTenantId(), 'Cabo Pinrang Pool', 'CPR');

        $this->patchJson(route('user.ui_preferences.update'), [
            'preferences' => [
                'defaultPoolId' => $poolId,
            ],
        ])
            ->assertOk()
            ->assertJsonPath('ui_preferences.defaultPoolId', $poolId);

        $user->refresh();
        $this->assertSame($poolId, (int) ($user->ui_preferences['defaultPoolId'] ?? 0));
        $this->assertSame($poolId, (int) session('active_pool_id'));
    }

    public function test_user_cannot_store_default_pool_preference_from_other_tenant(): void
    {
        $this->actingAsSuperAdmin();
        $otherTenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant-default-pool',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherPoolId = $this->createPool($otherTenantId, 'Other Pool', 'OTH');

        $this->patchJson(route('user.ui_preferences.update'), [
            'preferences' => [
                'defaultPoolId' => $otherPoolId,
            ],
        ])->assertStatus(422);
    }

    public function test_admin_can_set_default_pool_when_saving_user(): void
    {
        Notification::fake();
        $this->actingAsSuperAdmin();
        $poolId = $this->createPool($this->defaultTenantId(), 'Cabo Pinrang Pool', 'CPR');

        $response = $this->postJson(route('api.admin.users.save'), [
            'name' => 'Operator Default Pool',
            'email' => 'operator-default-pool@example.test',
            'password' => 'password123',
            'pool_ids' => [$poolId],
            'default_pool_id' => $poolId,
            'role_ids' => [],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true);

        $userId = (int) $response->json('id');
        $preferences = json_decode((string) DB::table('users')->where('id', $userId)->value('ui_preferences'), true);

        $this->assertSame($poolId, (int) ($preferences['defaultPoolId'] ?? 0));
        $this->getJson(route('api.admin.users.index'))
            ->assertOk()
            ->assertJsonFragment([
                'email' => 'operator-default-pool@example.test',
                'default_pool_id' => $poolId,
            ]);
    }

    public function test_admin_cannot_set_default_pool_outside_user_pool_assignment(): void
    {
        Notification::fake();
        $this->actingAsSuperAdmin();
        $poolA = $this->createPool($this->defaultTenantId(), 'Cabo Pinrang Pool', 'CPR');
        $poolB = $this->createPool($this->defaultTenantId(), 'Cabo Makassar Pool', 'CMK');

        $this->postJson(route('api.admin.users.save'), [
            'name' => 'Operator Pool Conflict',
            'email' => 'operator-pool-conflict@example.test',
            'password' => 'password123',
            'pool_ids' => [$poolA],
            'default_pool_id' => $poolB,
            'role_ids' => [],
        ])->assertStatus(422);
    }

    private function createPool(int $tenantId, string $name, string $code): int
    {
        return (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => $code,
            'status' => 'active',
            'target_revenue' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
