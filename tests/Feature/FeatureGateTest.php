<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use App\Support\TenantBillingAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeatureGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_gate_denies_resource_creation_when_plan_limit_is_reached(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user] = $this->tenantUserWithPlan('starter', 'active');

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertFalse(FeatureGate::canCreate('user.management', 'users', 'tenant_id'));
    }

    public function test_feature_gate_blocks_pending_payment_tenant_when_enabled(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user] = $this->tenantUserWithPlan('pro', 'pending_payment');

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertFalse(FeatureGate::can('dashboard.view'));
    }

    public function test_feature_gate_disabled_allows_access_during_migration(): void
    {
        config(['saas.feature_gating_enabled' => false]);
        [$user] = $this->tenantUserWithPlan('starter', 'pending_payment');

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertTrue(FeatureGate::can('dashboard.view'));
    }

    public function test_private_unlimited_override_allows_armada_creation_beyond_plan_cap(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user, $tenantId] = $this->tenantUserWithPlan('starter', 'active');

        $baseLimit = (int) DB::table('plans')->where('slug', 'starter')->value('max_armadas');
        $this->assertGreaterThan(0, $baseLimit);

        for ($i = 1; $i <= $baseLimit; $i++) {
            $payload = [
                'nopol' => 'OVR-'.uniqid().'-'.$i,
                'kategori' => 'EXECUTIVE',
                'ac_type' => 'AC',
                'created_at' => now(),
            ];

            if (Schema::hasColumn('armadas', 'tenant_id')) {
                $payload['tenant_id'] = $tenantId;
            }

            if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
                $payload['nama_kendaraan'] = 'Armada '.$i;
            }

            DB::table('armadas')->insert($payload);
        }

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertFalse(FeatureGate::canCreate('master.armadas', 'armadas', 'tenant_id'));

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update([
                'custom_max_armadas' => 0,
                'updated_at' => now(),
            ]);

        FeatureGate::flushRequestCache();

        $this->assertTrue(FeatureGate::canCreate('master.armadas', 'armadas', 'tenant_id'));
    }

    public function test_private_unlimited_override_applies_to_all_supported_resources(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user, $tenantId] = $this->tenantUserWithPlan('starter', 'active');

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update([
                'custom_max_pools' => 0,
                'custom_max_users' => 0,
                'custom_max_armadas' => 0,
                'custom_max_routes' => 0,
                'updated_at' => now(),
            ]);

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertTrue(FeatureGate::canCreate('tenant.multiple_pools', 'pools', 'tenant_id'));
        $this->assertTrue(FeatureGate::canCreate('user.management', 'users', 'tenant_id'));
        $this->assertTrue(FeatureGate::canCreate('master.armadas', 'armadas', 'tenant_id'));
        $this->assertTrue(FeatureGate::canCreate('master.routes', 'routes', 'tenant_id'));
    }

    public function test_private_override_uses_the_latest_active_subscription(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user, $tenantId] = $this->tenantUserWithPlan('starter', 'active');
        $planId = (int) DB::table('plans')->where('slug', 'starter')->value('id');

        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'custom_max_armadas' => 0,
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $this->assertTrue(FeatureGate::canCreate('master.armadas', 'armadas', 'tenant_id'));
        $this->assertSame(0, FeatureGate::currentPlan()?->custom_max_armadas);
    }

    public function test_private_pricing_is_exposed_without_losing_the_base_plan_reference(): void
    {
        config(['saas.feature_gating_enabled' => true]);
        [$user, $tenantId] = $this->tenantUserWithPlan('fleet', 'active');

        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->update([
                'custom_max_pools' => 0,
                'custom_max_users' => 0,
                'custom_max_armadas' => 0,
                'custom_max_routes' => 0,
                'updated_at' => now(),
            ]);

        $this->actingAs($user);
        FeatureGate::flushRequestCache();

        $plan = FeatureGate::currentPlan($user->id);
        $tenantSubscription = PoolScope::tenantSubscription($user->id);
        $billingAccess = TenantBillingAccess::forUser($user->id);

        $this->assertTrue((bool) $plan?->is_private_pricing);
        $this->assertSame('Private Pricing', $plan?->plan_name);
        $this->assertSame('Private Pricing', $tenantSubscription['plan_name']);
        $this->assertTrue((bool) $billingAccess['is_private_pricing']);
        $this->assertSame('Private Pricing', $billingAccess['plan_name']);
        $this->assertSame('fleet', $tenantSubscription['base_plan_slug']);
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function tenantUserWithPlan(string $planSlug, string $status): array
    {
        $planId = (int) DB::table('plans')->where('slug', $planSlug)->value('id');
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Gate Tenant',
            'slug' => 'gate-tenant-'.uniqid(),
            'status' => $status === 'pending_payment' ? 'pending_payment' : 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => $status,
            'starts_at' => $status === 'pending_payment' ? null : now()->toDateString(),
            'ends_at' => $status === 'pending_payment' ? null : now()->addMonth()->toDateString(),
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        DB::table('users')->where('id', $user->id)->update(['tenant_id' => $tenantId]);

        return [$user->fresh(), $tenantId];
    }
}
