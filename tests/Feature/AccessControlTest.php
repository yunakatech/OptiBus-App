<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_owner_can_manage_pool_but_not_platform(): void
    {
        AccessControl::syncDefaults();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $roleId = (int) DB::table('roles')->where('slug', 'tenant-owner')->value('id');

        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(AccessControl::can((int) $user->id, 'pool.manage'));
        $this->assertTrue(AccessControl::can((int) $user->id, 'user.manage'));
        $this->assertFalse(AccessControl::can((int) $user->id, 'platform.manage'));
    }

    public function test_repair_tenant_access_command_maps_user_to_tenant_pool_and_owner_role(): void
    {
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Repair Travel',
            'slug' => 'repair-travel-'.uniqid(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->artisan('qbus:repair-tenant-access', [
            '--user' => $user->id,
            '--tenant' => $tenantId,
        ])->assertExitCode(0);

        $roleId = (int) DB::table('roles')->where('slug', 'tenant-owner')->value('id');
        $poolId = (int) DB::table('pools')->where('tenant_id', $tenantId)->value('id');

        $this->assertGreaterThan(0, $poolId);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tenant_id' => $tenantId,
        ]);
        $this->assertDatabaseHas('pool_user', [
            'user_id' => $user->id,
            'pool_id' => $poolId,
        ]);
        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $roleId,
        ]);
    }
}
