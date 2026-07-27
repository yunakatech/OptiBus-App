<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnboardingSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_minimal_creates_tenant_pool_and_route(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'travel_name' => 'Cahaya Baru',
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('id', $user->id)->value('tenant_id');
        $this->assertGreaterThan(0, $tenantId);
        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'name' => 'Cahaya Baru']);
        $this->assertDatabaseHas('pools', ['tenant_id' => $tenantId]);
        $this->assertDatabaseHas('routes', [
            'tenant_id' => $tenantId,
            'name' => 'PINRANG -> MAKASSAR',
        ]);
        $this->assertSame(0, DB::table('segments')->where('tenant_id', $tenantId)->count());
        $this->assertSame(0, DB::table('schedules')->where('tenant_id', $tenantId)->count());
        $this->assertSame(0, DB::table('units')->where('tenant_id', $tenantId)->count());
        $this->assertSame(0, DB::table('armadas')->where('tenant_id', $tenantId)->count());
        $this->assertSame(0, DB::table('drivers')->where('tenant_id', $tenantId)->count());
    }

    public function test_onboarding_complete_creates_operational_setup(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'travel_name' => 'Siap Jalan',
            'phone' => '085233334444',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
            'segment_origin' => 'Pool Pinrang',
            'segment_destination' => 'Makassar Kota',
            'pickup_times' => ['08:00'],
            'ticket_price' => 120000,
            'schedule_days' => [1, 3, 5],
            'departure_time' => '08:00',
            'unit_category' => 'Minibus',
            'seat_capacity' => 8,
            'unit_nopol' => 'DD 1234 XX',
            'armada_merk' => 'Toyota',
            'driver_name' => 'Andi Driver',
            'driver_phone' => '085299998888',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('id', $user->id)->value('tenant_id');
        $poolId = (int) DB::table('pools')->where('tenant_id', $tenantId)->value('id');
        $routeId = (int) DB::table('routes')->where('tenant_id', $tenantId)->value('id');

        $this->assertDatabaseHas('segments', [
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'harga' => 120000,
        ]);
        $this->assertDatabaseHas('schedules', [
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => 'PINRANG -> MAKASSAR',
        ]);
        $this->assertDatabaseHas('units', [
            'tenant_id' => $tenantId,
            'pool_id' => $poolId,
            'nopol' => 'DD 1234 XX',
            'category' => 'Minibus',
        ]);
        $this->assertDatabaseHas('armadas', [
            'tenant_id' => $tenantId,
            'pool_id' => $poolId,
            'nopol' => 'DD 1234 XX',
            'kategori' => 'Minibus',
        ]);
        $this->assertDatabaseHas('drivers', [
            'tenant_id' => $tenantId,
            'pool_id' => $poolId,
            'nama' => 'ANDI DRIVER',
        ]);
    }

    public function test_existing_tenant_can_continue_onboarding_setup(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('onboarding.store'), [
            'travel_name' => 'Lanjut Travel',
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
        ]);

        $response = $this->actingAs($user)->get(route('onboarding', ['continue' => 1]));
        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding')
                ->where('continuationMode', true)
                ->has('setupProgress')
            );

        $this->actingAs($user)->post(route('onboarding.store'), [
            'travel_name' => 'Lanjut Travel',
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
            'segment_origin' => 'Pool Pinrang',
            'segment_destination' => 'Makassar Kota',
            'pickup_times' => ['09:00'],
            'ticket_price' => 125000,
        ])->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('id', $user->id)->value('tenant_id');
        $this->assertDatabaseHas('segments', [
            'tenant_id' => $tenantId,
            'harga' => 125000,
        ]);
    }
}
