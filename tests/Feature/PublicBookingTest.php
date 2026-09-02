<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function test_public_availability_uses_the_configured_label_for_each_unit_slot(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        DB::table('schedules')->where('id', $scheduleId)->update([
            'units' => 2,
        ]);
        DB::table('schedule_units')->where('schedule_id', $scheduleId)->delete();

        $slotRows = [
            [
                'schedule_id' => $scheduleId,
                'unit_no' => 1,
                'label' => 'Armada Pagi',
                'created_at' => now(),
            ],
            [
                'schedule_id' => $scheduleId,
                'unit_no' => 2,
                'label' => 'Armada Siang',
                'created_at' => now(),
            ],
        ];
        if (Schema::hasColumn('schedule_units', 'tenant_id')) {
            $slotRows = array_map(
                fn (array $row): array => [...$row, 'tenant_id' => $tenantId],
                $slotRows,
            );
        }
        DB::table('schedule_units')->insert($slotRows);

        $response = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'route_id' => $routeId,
        ]));

        $response->assertOk()
            ->assertJsonPath('schedules.0.unit_label', 'Armada Pagi')
            ->assertJsonPath('schedules.1.unit_label', 'Armada Siang');
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

    public function test_public_booking_uses_segment_price_pickup_and_parent_route_schedule(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        $segmentPayload = [
            'route_id' => $routeId,
            'rute' => 'MAKASSAR - PAREPARE',
            'origin' => 'MAKASSAR',
            'destination' => 'MAROS',
            'jam' => '08:00:00',
            'jam_pickups' => json_encode(['08:00']),
            'harga' => 150000,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('segments', 'tenant_id')) {
            $segmentPayload['tenant_id'] = $tenantId;
        }
        $segmentId = (int) DB::table('segments')->insertGetId($segmentPayload);
        DB::table('schedule_segment')->insert([
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
            'jam_pickup' => '08:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('tenants')->where('id', $tenantId)->update([
            'public_booking_whatsapp' => '+62 811-2222-3333',
        ]);

        $availability = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'segment_id' => $segmentId,
        ]));
        $availability->assertOk()
            ->assertJsonPath('segments.0.id', $segmentId)
            ->assertJsonPath('segments.0.price', 150000)
            ->assertJsonPath('segments.0.pickup_times.0', '08:00')
            ->assertJsonPath('selected_route_id', $routeId)
            ->assertJsonPath('schedules.0.id', $scheduleId)
            ->assertJsonPath('schedules.0.segment.price', 150000)
            ->assertJsonPath('schedules.0.segment.pickup_time', '08:00');

        $response = $this->postJson(
            route('api.public.booking.requests.store', ['tenantSlug' => 'qbus-default']),
            [...$this->requestPayload($routeId, $scheduleId), 'segment_id' => $segmentId],
        );

        $response->assertCreated()
            ->assertJsonPath('whatsapp_url', fn ($value) => str_starts_with((string) $value, 'https://wa.me/6281122223333?'));
        $requestId = (int) $response->json('request_id');
        $this->assertDatabaseHas('public_booking_requests', [
            'id' => $requestId,
            'segment_id' => $segmentId,
            'price' => 150000,
            'pickup_time' => '08:00',
        ]);

        DB::table('segments')->where('id', $segmentId)->update(['harga' => 200000]);
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->postJson(route('api.admin.public-booking-requests.approve', ['id' => $requestId]))
            ->assertOk()
            ->assertJsonPath('result.status', 'approved');

        $this->assertDatabaseHas('bookings', [
            'public_booking_request_id' => $requestId,
            'segment_id' => $segmentId,
            'price' => 150000,
        ]);
    }

    public function test_hidden_segment_is_excluded_from_public_booking_but_remains_operational(): void
    {
        [$tenantId, $routeId, $scheduleId] = $this->fixture();
        $segmentId = (int) DB::table('segments')->insertGetId([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => 'MAKASSAR - MAROS',
            'origin' => 'MAKASSAR',
            'destination' => 'MAROS',
            'jam' => '08:00:00',
            'jam_pickups' => json_encode(['08:00']),
            'harga' => 100000,
            'created_at' => now(),
        ]);
        DB::table('schedule_segment')->insert([
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
            'jam_pickup' => '08:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('segments')->where('id', $segmentId)->update([
            'public_booking_enabled' => false,
        ]);

        $availability = $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
        ]))->assertOk();

        $this->assertFalse(
            collect($availability->json('segments'))->contains('id', $segmentId),
        );
        $this->getJson(route('api.public.booking.availability', [
            'tenantSlug' => 'qbus-default',
            'tanggal' => '2026-09-02',
            'segment_id' => $segmentId,
        ]))->assertNotFound();

        $this->assertDatabaseHas('segments', [
            'id' => $segmentId,
            'route_id' => $routeId,
            'public_booking_enabled' => 0,
        ]);
        $this->assertDatabaseHas('schedule_segment', [
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
        ]);
    }

    public function test_public_booking_whatsapp_button_is_empty_without_tenant_whatsapp(): void
    {
        [$routeId, $scheduleId] = array_slice($this->fixture(), 1);

        $response = $this->postJson(
            route('api.public.booking.requests.store', ['tenantSlug' => 'qbus-default']),
            $this->requestPayload($routeId, $scheduleId),
        );

        $response->assertCreated()->assertJsonPath('whatsapp_url', null);
    }

    public function test_admin_can_save_public_booking_whatsapp_number(): void
    {
        $tenantId = $this->defaultTestTenantId();
        $this->actingAsSuperAdminWithTenantContext($tenantId);

        $this->postJson(route('api.admin.public-booking-settings.update'), [
            'enabled' => false,
            'whatsapp' => '+62 811-2222-3333',
        ])->assertOk()
            ->assertJsonPath('settings.tenant.whatsapp', '6281122223333');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'public_booking_whatsapp' => '6281122223333',
        ]);
    }

    public function test_tenant_admin_can_upload_public_booking_logo_to_supabase_storage(): void
    {
        $tenantId = $this->defaultTestTenantId();
        config()->set('filesystems.disks.supabase.key', 'test-key');
        config()->set('filesystems.disks.supabase.secret', 'test-secret');
        config()->set('filesystems.disks.supabase.region', 'us-east-1');
        config()->set('filesystems.disks.supabase.bucket', 'avatars');
        config()->set('filesystems.disks.supabase.endpoint', 'https://project.test/storage/v1/s3');
        config()->set('filesystems.disks.supabase.url', 'https://project.test/storage/v1/object/public/avatars');
        Storage::fake('supabase');

        $oldPath = 'public-booking/logos/old-logo.png';
        Storage::disk('supabase')->put($oldPath, 'old-logo');
        DB::table('tenants')->where('id', $tenantId)->update([
            'public_booking_enabled' => true,
            'logo_url' => config('filesystems.disks.supabase.url').'/'.$oldPath,
        ]);

        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $response = $this->post(route('api.admin.public-booking-settings.logo'), [
            'logo' => UploadedFile::fake()->image('new-logo.png'),
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $logoUrl = (string) $response->json('settings.tenant.logo_url');
        $this->assertStringContainsString('/storage/v1/object/public/avatars/', $logoUrl);
        $newPath = Str::after($logoUrl, '/storage/v1/object/public/avatars/');
        $this->assertNotSame('', $newPath);
        Storage::disk('supabase')->assertExists($newPath);
        Storage::disk('supabase')->assertMissing($oldPath);
    }

    public function test_public_booking_logo_upload_rejects_files_larger_than_two_mb(): void
    {
        $tenantId = $this->defaultTestTenantId();

        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->post(route('api.admin.public-booking-settings.logo'), [
            'logo' => UploadedFile::fake()->image('large-logo.png')->size(2049),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('logo');
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
