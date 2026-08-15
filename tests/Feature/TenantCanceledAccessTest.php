<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantCanceledAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_from_canceled_tenant_cannot_login(): void
    {
        [$user, $tenantId] = $this->createCanceledTenantUser();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Tenant sudah diarsipkan dan tidak dapat digunakan. Hubungi superadmin jika perlu dipulihkan.');
        $this->assertGuest();
        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'status' => 'canceled']);
    }

    public function test_canceled_tenant_cannot_create_a_renewal_checkout(): void
    {
        [$user, $tenantId] = $this->createCanceledTenantUser();
        $subscriptionCount = DB::table('subscriptions')->where('tenant_id', $tenantId)->count();

        $response = $this->actingAs($user)->post(route('subscription.checkout'), [
            'plan_slug' => 'pro',
            'billing_interval' => 'monthly',
        ]);

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Tenant sudah diarsipkan dan tidak dapat digunakan. Hubungi superadmin jika perlu dipulihkan.');
        $this->assertGuest();
        $this->assertSame($subscriptionCount, DB::table('subscriptions')->where('tenant_id', $tenantId)->count());
        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'status' => 'canceled']);
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function createCanceledTenantUser(): array
    {
        $planId = (int) DB::table('plans')->where('slug', 'pro')->value('id');
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Canceled Travel',
            'slug' => 'canceled-travel-'.uniqid(),
            'status' => 'canceled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'canceled',
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
            'billing_interval' => 'monthly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        DB::table('users')->where('id', $user->id)->update(['tenant_id' => $tenantId]);

        return [$user->fresh(), $tenantId];
    }
}
