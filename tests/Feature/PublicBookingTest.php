<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_enabled_tenant_exposes_mobile_booking_page_and_availability(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();

        $this->get(route('public.booking.show', ['tenantSlug' => 'qbus-default']))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('PublicBooking')
                ->where('tenant.slug', 'qbus-default')
                ->where('date_min', '2026-09-01'));

        $response = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'route_id' => $routeId,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('routes.0.id', $routeId)
            ->assertJsonPath('schedules.0.id', $scheduleId)
            ->assertJsonPath('schedules.0.layout.0.0', '1')
            ->assertJsonPath('schedules.0.seats.0.status', 'available');
    }

    public function test_public_request_holds_multiple_seats_and_is_visible_as_held(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        $payload = $this->requestPayload($routeId, $scheduleId);

        $response = $this->postJson(route('api.public.booking.requests.store', ['tenantSlug' => 'qbus-default']), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('request_code', fn ($value) => str_starts_with((string) $value, 'PB-'));

        $requestId = (int) $response->json('request_id');
        $this->assertDatabaseHas('public_booking_requests', [
            'id' => $requestId,
            'tenant_id' => $tenantId,
            'status' => 'pending',
            'contact_name' => 'BUDI',
            'pickup_address' => 'Jl. Merdeka 1',
        ]);
        $this->assertDatabaseCount('public_booking_request_seats', 2);

        $availability = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'route_id' => $routeId,
        ]));

        $availability->assertJsonPath('schedules.0.seats.0.status', 'held')
            ->assertJsonPath('schedules.0.seats.1.status', 'held');
    }

    public function test_pool_admin_can_approve_public_request_into_official_bookings(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        $response = $this->postJson(route('api.public.booking.requests.store', ['tenantSlug' => 'qbus-default']), $this->requestPayload($routeId, $scheduleId));
        $requestId = (int) $response->json('request_id');
        $this->actingAsSuperAdminWithTenantContext($tenantId);

        $this->postJson(route('api.admin.public-booking-requests.approve', ['id' => $requestId]))
            ->assertOk()
            ->assertJsonPath('result.status', 'approved')
            ->assertJsonCount(2, 'result.booking_ids');

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('bookings', [
            'tenant_id' => $tenantId,
            'public_booking_request_id' => $requestId,
            'name' => 'BUDI',
            'pembayaran' => 'Transfer',
        ]);
        $this->assertDatabaseHas('public_booking_requests', ['id' => $requestId, 'status' => 'approved']);
    }

    public function test_rejecting_public_request_releases_the_held_seats(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        $response = $this->postJson(route('api.public.booking.requests.store', ['tenantSlug' => 'qbus-default']), $this->requestPayload($routeId, $scheduleId));
        $requestId = (int) $response->json('request_id');
        $this->actingAsSuperAdminWithTenantContext($tenantId);

        $this->postJson(route('api.admin.public-booking-requests.reject', ['id' => $requestId]), ['reason' => 'Jadwal penuh'])
            ->assertOk()
            ->assertJsonPath('result.status', 'rejected');

        $availability = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'route_id' => $routeId,
        ]));
        $availability->assertJsonPath('schedules.0.seats.0.status', 'available');
    }

    public function test_disabled_tenant_does_not_expose_public_booking_page(): void
    {
        DB::table('tenants')->where('slug', 'qbus-default')->update(['public_booking_enabled' => false]);

        $this->get(route('public.booking.show', ['tenantSlug' => 'qbus-default']))->assertNotFound();
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function fixture(): array
    {
        $tenantId = $this->defaultTestTenantId();
        DB::table('tenants')->where('id', $tenantId)->update([
            'public_booking_enabled' => true,
            'phone' => '08123456789',
        ]);
        $routeId = (int) DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'MAKASSAR - PAREPARE',
            'origin' => 'MAKASSAR',
            'destination' => 'PAREPARE',
            'created_at' => now(),
        ]);
        $poolId = (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Pool Makassar',
            'code' => 'MKS-'.uniqid(),
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
        $date = '2026-09-02';
        $scheduleId = (int) DB::table('schedules')->insertGetId([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => 'MAKASSAR - PAREPARE',
            'dow' => Carbon::parse($date)->dayOfWeek,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Bus Pagi',
            'layout' => json_encode([['1', '2'], ['3', '4']]),
            'created_at' => now(),
        ]);

        return [$tenantId, $routeId, $scheduleId];
    }

    /** @return array<string, mixed> */
    private function requestPayload(int $routeId, int $scheduleId): array
    {
        return [
            'route_id' => $routeId,
            'schedule_id' => $scheduleId,
            'tanggal' => '2026-09-02',
            'unit' => 1,
            'contact_name' => 'BUDI',
            'phone' => '08123456789',
            'pickup_address' => 'Jl. Merdeka 1',
            'payment_method' => 'Transfer',
            'notes' => 'Dekat gerbang utama',
            'passengers' => [
                ['seat' => '1', 'passenger_name' => 'BUDI'],
                ['seat' => '2', 'passenger_name' => 'SITI'],
            ],
        ];
    }
}
