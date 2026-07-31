<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Services\TenantInvitationService;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TenantInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_owner_creates_invitation_with_role_and_pool(): void
    {
        Notification::fake();
        [$owner, $tenantId, $poolId, $roleId] = $this->tenantOwnerContext();

        $this->actingAs($owner)
            ->postJson('/api/admin/users/invitations', [
                'name' => 'Google Operator',
                'email' => 'operator@example.com',
                'role_ids' => [$roleId],
                'pool_ids' => [$poolId],
            ])
            ->assertCreated()
            ->assertJsonPath('invitations.0.email', 'operator@example.com')
            ->assertJsonPath('invitations.0.status', 'pending');

        $this->assertDatabaseHas('tenant_invitations', [
            'tenant_id' => $tenantId,
            'email' => 'operator@example.com',
            'accepted_at' => null,
            'revoked_at' => null,
        ]);
        Notification::assertSentOnDemand(TenantInvitationNotification::class);
    }

    public function test_invitation_requires_user_manage_and_active_tenant(): void
    {
        [$owner, $tenantId] = $this->tenantOwnerContext();
        $plainUser = User::factory()->create([
            'tenant_id' => $tenantId,
            'is_super_admin' => false,
        ]);

        $this->actingAs($plainUser)
            ->postJson('/api/admin/users/invitations', ['email' => 'blocked@example.com'])
            ->assertForbidden();

        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $this->actingAs($superAdmin)
            ->getJson('/api/admin/users/invitations')
            ->assertStatus(409);

        $this->actingAs($owner)
            ->getJson('/api/admin/users/invitations')
            ->assertOk();
    }

    public function test_google_invitation_consume_creates_user_and_assigns_access(): void
    {
        Notification::fake();
        [$owner, $tenantId, $poolId, $roleId] = $this->tenantOwnerContext();
        $service = app(TenantInvitationService::class);
        $service->create($tenantId, 'new-google@example.com', 'New Google', [$roleId], [$poolId], (int) $owner->id);

        $result = $service->consumeForGoogle('new-google@example.com', 'New Google', 'https://avatar.test/a.png');

        $this->assertSame('consumed', $result['status']);
        $user = $result['user'];
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'id' => (int) $user->id,
            'email' => 'new-google@example.com',
            'tenant_id' => $tenantId,
        ]);
        $this->assertDatabaseHas('user_role', ['user_id' => (int) $user->id, 'role_id' => $roleId]);
        $this->assertDatabaseHas('pool_user', ['user_id' => (int) $user->id, 'pool_id' => $poolId]);
        $this->assertNotNull(DB::table('tenant_invitations')->where('email', 'new-google@example.com')->value('accepted_at'));
    }

    public function test_google_invitation_consume_existing_user_without_tenant(): void
    {
        Notification::fake();
        [$owner, $tenantId, $poolId, $roleId] = $this->tenantOwnerContext();
        $user = User::factory()->create([
            'email' => 'floating@example.com',
            'tenant_id' => null,
            'is_super_admin' => false,
        ]);

        app(TenantInvitationService::class)->create($tenantId, 'floating@example.com', 'Floating User', [$roleId], [$poolId], (int) $owner->id);
        $result = app(TenantInvitationService::class)->consumeForGoogle('floating@example.com', 'Floating User', null);

        $this->assertSame('consumed', $result['status']);
        $this->assertDatabaseHas('users', ['id' => (int) $user->id, 'tenant_id' => $tenantId]);
        $this->assertDatabaseHas('user_role', ['user_id' => (int) $user->id, 'role_id' => $roleId]);
        $this->assertDatabaseHas('pool_user', ['user_id' => (int) $user->id, 'pool_id' => $poolId]);
    }

    public function test_google_invitation_rejects_existing_user_from_other_tenant(): void
    {
        Notification::fake();
        [$owner, $tenantId, $poolId, $roleId] = $this->tenantOwnerContext();
        $otherTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'email' => 'conflict@example.com',
            'tenant_id' => $otherTenantId,
            'is_super_admin' => false,
        ]);

        app(TenantInvitationService::class)->create($tenantId, 'conflict@example.com', 'Conflict User', [$roleId], [$poolId], (int) $owner->id);
        $result = app(TenantInvitationService::class)->consumeForGoogle('conflict@example.com', 'Conflict User', null);

        $this->assertSame('tenant_conflict', $result['status']);
        $this->assertNull(DB::table('tenant_invitations')->where('email', 'conflict@example.com')->value('accepted_at'));
    }

    public function test_revoked_and_expired_invitations_are_not_consumed(): void
    {
        Notification::fake();
        [$owner, $tenantId, $poolId, $roleId] = $this->tenantOwnerContext();
        $service = app(TenantInvitationService::class);

        $revoked = $service->create($tenantId, 'revoked@example.com', 'Revoked', [$roleId], [$poolId], (int) $owner->id);
        $service->revoke((int) $revoked['invitation']->id, $tenantId);
        $this->assertSame('none', $service->consumeForGoogle('revoked@example.com', 'Revoked', null)['status']);

        $expired = $service->create($tenantId, 'expired@example.com', 'Expired', [$roleId], [$poolId], (int) $owner->id);
        DB::table('tenant_invitations')->where('id', (int) $expired['invitation']->id)->update(['expires_at' => now()->subMinute()]);
        $this->assertSame('none', $service->consumeForGoogle('expired@example.com', 'Expired', null)['status']);
    }

    /**
     * @return array{0: User, 1: int, 2: int, 3: int}
     */
    private function tenantOwnerContext(): array
    {
        AccessControl::syncDefaults();
        $tenantId = $this->defaultTestTenantId();
        $poolId = (int) (DB::table('pools')->where('tenant_id', $tenantId)->value('id') ?? 0);
        if ($poolId <= 0) {
            $poolId = (int) DB::table('pools')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Default Pool',
                'code' => 'DEFAULT',
                'status' => 'active',
                'target_revenue' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleId = (int) DB::table('roles')->where('slug', 'admin-pool')->value('id');
        $ownerRoleId = (int) DB::table('roles')->where('slug', 'tenant-owner')->value('id');
        $owner = User::factory()->create([
            'tenant_id' => $tenantId,
            'is_super_admin' => false,
        ]);
        DB::table('user_role')->insert([
            'user_id' => (int) $owner->id,
            'role_id' => $ownerRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$owner, $tenantId, $poolId, $roleId];
    }
}
