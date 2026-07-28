<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TenantProvisioningService;
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
        $this->assertSame(0, DB::table('category_armada')->where('tenant_id', $tenantId)->count());
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
            'unit_template_name' => 'Minibus 8 Seat',
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
        $this->assertDatabaseHas('category_armada', [
            'tenant_id' => $tenantId,
            'pool_id' => $poolId,
            'nama_kategori' => 'MINIBUS 8 SEAT',
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

    public function test_onboarding_reuses_existing_schedules_without_duplication(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $payload = [
            'travel_name' => 'Jalur Stabil',
            'phone' => '085211112222',
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
            'unit_template_name' => 'Minibus 8 Seat',
            'unit_category' => 'Minibus',
            'seat_capacity' => 8,
            'unit_nopol' => 'DD 1234 XX',
            'armada_merk' => 'Toyota',
            'driver_name' => 'Andi Driver',
            'driver_phone' => '085299998888',
        ];

        $this->actingAs($user)->post(route('onboarding.store'), $payload)
            ->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('id', $user->id)->value('tenant_id');
        $firstScheduleCount = DB::table('schedules')->where('tenant_id', $tenantId)->count();
        $firstScheduleUnitCount = DB::table('schedule_units')->where('tenant_id', $tenantId)->count();
        $firstScheduleSegmentCount = DB::table('schedule_segment')
            ->join('schedules', 'schedule_segment.schedule_id', '=', 'schedules.id')
            ->where('schedules.tenant_id', $tenantId)
            ->count();

        $this->actingAs($user)->post(route('onboarding.store'), $payload)
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame($firstScheduleCount, DB::table('schedules')->where('tenant_id', $tenantId)->count());
        $this->assertSame($firstScheduleUnitCount, DB::table('schedule_units')->where('tenant_id', $tenantId)->count());
        $this->assertSame($firstScheduleSegmentCount, DB::table('schedule_segment')
            ->join('schedules', 'schedule_segment.schedule_id', '=', 'schedules.id')
            ->where('schedules.tenant_id', $tenantId)
            ->count());
    }

    public function test_onboarding_keeps_unit_template_unique_across_tenants(): void
    {
        $firstUser = User::factory()->create(['email_verified_at' => now()]);
        $secondUser = User::factory()->create(['email_verified_at' => now()]);

        $basePayload = [
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
            'unit_template_name' => 'Minibus 8 Seat',
            'unit_category' => 'Minibus',
            'seat_capacity' => 8,
        ];

        $this->actingAs($firstUser)->post(route('onboarding.store'), [
            ...$basePayload,
            'travel_name' => 'Tenant Pertama',
            'unit_nopol' => 'DD 1111 AA',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($secondUser)->post(route('onboarding.store'), [
            ...$basePayload,
            'travel_name' => 'Tenant Kedua',
            'unit_nopol' => 'DD 2222 BB',
        ])->assertRedirect(route('dashboard', absolute: false));

        $secondTenantId = (int) DB::table('users')
            ->where('id', $secondUser->id)
            ->value('tenant_id');
        $secondUnitName = (string) DB::table('category_armada')
            ->where('tenant_id', $secondTenantId)
            ->value('nama_kategori');

        $this->assertSame('MINIBUS 8 SEAT', (string) DB::table('category_armada')
            ->where('tenant_id', (int) DB::table('users')->where('id', $firstUser->id)->value('tenant_id'))
            ->value('nama_kategori'));
        $this->assertNotSame('MINIBUS 8 SEAT', $secondUnitName);
        $this->assertStringStartsWith('MINIBUS 8 SEAT-T', $secondUnitName);
    }

    public function test_setup_progress_counts_pool_scoped_legacy_units(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('onboarding.store'), [
            'travel_name' => 'Progress Legacy',
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'billing_interval' => 'monthly',
        ])->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('id', $user->id)->value('tenant_id');
        $poolId = (int) DB::table('pools')->where('tenant_id', $tenantId)->value('id');

        DB::table('category_armada')->insert([
            'nama_kategori' => 'LEGACY UNIT',
            'category' => 'Minibus',
            'kapasitas' => 8,
            'status' => 'Aktif',
            'pool_id' => $poolId,
            'created_at' => now(),
        ]);

        $progress = app(TenantProvisioningService::class)->setupProgressForTenant($tenantId);

        $this->assertTrue($progress['unit']);
        $this->assertContains(
            ['key' => 'unit', 'label' => 'Kategori armada sudah dibuat', 'done' => true],
            $progress['items'],
        );
    }
}
