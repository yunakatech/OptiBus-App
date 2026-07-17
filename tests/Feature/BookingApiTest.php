<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_authenticated_user_can_get_schedules(): void
    {
        $this->actingAsSuperAdmin();

        $date = '2026-05-15';
        $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

        $unitId = DB::table('units')->insertGetId([
            'nopol' => 'DD 1234 XX',
            'merek' => 'Isuzu',
            'type' => 'Elf',
            'kapasitas' => 12,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $this->defaultTenantId(),
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => $dow,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'unit_id' => $unitId,
            'created_at' => now(),
        ]);

        $segmentId = DB::table('segments')->insertGetId([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'jam' => '09:00:00',
            'jam_pickups' => json_encode(['09:00', '09:30']),
            'harga' => 150000,
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.bookings.schedules', [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $date,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'schedules')
            ->assertJsonPath('schedules.0.jam', '09:00')
            ->assertJsonPath('schedules.0.segment_matches.0.id', $segmentId)
            ->assertJsonPath('schedules.0.segment_matches.0.jam_pickups.0', '09:00')
            ->assertJsonPath('schedules.0.seats', 12)
            ->assertJsonPath('schedules.0.unit_options.0.unit_no', 1)
            ->assertJsonPath('schedules.0.unit_options.0.label', 'Reguler');
    }

    public function test_legacy_booking_console_paths_serve_json_for_api_calls(): void
    {
        $this->actingAsSuperAdmin();

        $date = '2026-05-15';
        $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => $dow,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'created_at' => now(),
        ]);

        DB::table('segments')->insert([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'jam' => '09:00:00',
            'harga' => 150000,
            'created_at' => now(),
        ]);

        $this->getJson('/bookings/routes-by-date?tanggal='.$date)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('routes.0', 'PINRANG - MAKASSAR');

        $this->getJson('/bookings/schedules?rute=PINRANG%20-%20MAKASSAR&tanggal='.$date)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('schedules.0.jam', '09:00');

        $this->getJson('/bookings/seats-detail?rute=PINRANG%20-%20MAKASSAR&tanggal='.$date.'&jam=09:00&unit=1')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/master/segments?route_name=PINRANG%20-%20MAKASSAR')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('segments.0.rute', 'PINRANG - MAKASSAR');

        $this->getJson('/master/customers/search?q=RIDWAN')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/admin/drivers')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/admin/armadas')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/admin/assignments?tanggal='.$date)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_submit_booking_and_detect_conflict(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL PERWAKILAN PINRANG',
            'code' => 'PRG',
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

        $segmentId = DB::table('segments')->insertGetId([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'jam' => '09:30:00',
            'harga' => 150000,
            'created_at' => now(),
        ]);

        $payload = [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => 'Terminal',
            'address' => 'Pinrang',
            'pembayaran' => 'Belum Lunas',
            'segment_id' => $segmentId,
            'discount' => 10000,
            'seats' => ['1', '2'],
        ];

        $success = $this->postJson(route('api.bookings.submit'), $payload);
        $success->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('added', 2);

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('bookings', [
            'route_id' => $routeId,
            'phone' => '081234567890',
        ]);
        $this->assertDatabaseHas('customers', [
            'pool_id' => $poolId,
            'phone' => '081234567890',
        ]);

        $conflict = $this->postJson(route('api.bookings.submit'), array_merge($payload, [
            'rute' => 'Pinrang -> Makassar',
            'seats' => ['1'],
            'discount' => 0,
        ]));

        $conflict->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'conflict');
    }

    public function test_update_booking_uses_route_id_for_normalized_route_variants(): void
    {
        $this->actingAsSuperAdmin();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG -> MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $trip = [
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'price' => 150000,
            'discount' => 0,
            'created_at' => now(),
        ];

        DB::table('bookings')->insert(array_merge($trip, [
            'route_id' => $routeId,
            'rute' => 'PINRANG -> MAKASSAR',
            'seat' => '1',
        ]));
        $legacyBookingId = DB::table('bookings')->insertGetId(array_merge($trip, [
            'route_id' => null,
            'rute' => 'Pinrang - Makassar',
            'seat' => '2',
        ]));

        $conflict = $this->postJson(route('api.bookings.update'), [
            'booking_id' => $legacyBookingId,
            'rute' => 'Pinrang => Makassar',
            'seat' => '1',
        ]);

        $conflict->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Kursi sudah terpakai pada keberangkatan ini');

        $success = $this->postJson(route('api.bookings.update'), [
            'booking_id' => $legacyBookingId,
            'rute' => 'Pinrang => Makassar',
            'seat' => '3',
        ]);

        $success->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('booking_id', $legacyBookingId);

        $this->assertDatabaseHas('bookings', [
            'id' => $legacyBookingId,
            'route_id' => $routeId,
            'rute' => 'Pinrang => Makassar',
            'seat' => '3',
        ]);
    }

    public function test_booked_seats_detail_includes_segment_time_and_display_name(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $segmentId = DB::table('segments')->insertGetId([
            'route_id' => $routeId,
            'rute' => 'PINRANG - PAREPARE',
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'jam' => '07:30:00',
            'jam_pickups' => json_encode(['07:30', '08:45']),
            'harga' => 75000,
            'created_at' => now(),
        ]);

        DB::table('bookings')->insert([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'seat' => 'A1',
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'segment_id' => $segmentId,
            'price' => 75000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.bookings.seats-detail', [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('details.A1.segment_name', 'PINRANG - PAREPARE')
            ->assertJsonPath('details.A1.segment_jam', '07:30')
            ->assertJsonPath('details.A1.segment_jam_pickups.0', '07:30')
            ->assertJsonPath('details.A1.segment_jam_pickups.1', '08:45');
    }

    public function test_schedules_only_return_matched_segments_no_route_fallback(): void
    {
        $this->actingAsSuperAdmin();

        $date = '2026-05-15';
        $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $this->defaultTenantId(),
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => $dow,
            'jam' => '07:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'created_at' => now(),
        ]);

        // Segment only has jam_pickups = ['10:00'] — does NOT match the schedule jam 07:00
        DB::table('segments')->insertGetId([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'jam' => '10:00:00',
            'jam_pickups' => json_encode(['10:00']),
            'harga' => 150000,
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.bookings.schedules', [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $date,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'schedules')
            ->assertJsonPath('schedules.0.jam', '07:00');

        // segment_matches must be empty — no fallback to all route segments
        $this->assertCount(0, $response->json('schedules.0.segment_matches'));
    }

    public function test_booked_seats_detail_segment_jam_pickups_come_from_segment_not_schedule(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $segmentId = DB::table('segments')->insertGetId([
            'route_id' => $routeId,
            'rute' => 'PINRANG - PAREPARE',
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'jam' => '07:30:00',
            'jam_pickups' => json_encode(['07:30', '08:45']),
            'harga' => 75000,
            'created_at' => now(),
        ]);

        // Schedule jam = 09:00 is intentionally different from segment jams
        DB::table('bookings')->insert([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00', // schedule jam – should NOT appear in output
            'unit' => 1,
            'seat' => 'B1',
            'name' => 'TOTI',
            'phone' => '081900000001',
            'pickup_point' => 'Pinrang',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'segment_id' => $segmentId,
            'price' => 75000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.bookings.seats-detail', [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
        ]));

        $response->assertOk()
            ->assertJsonPath('details.B1.segment_jam', '07:30')
            ->assertJsonPath('details.B1.segment_jam_pickups.0', '07:30')
            ->assertJsonPath('details.B1.segment_jam_pickups.1', '08:45');

        // The schedule jam (09:00) must NOT appear as segment_jam or in segment_jam_pickups
        $this->assertNotSame('09:00', $response->json('details.B1.segment_jam'));
        $this->assertNotContains('09:00', $response->json('details.B1.segment_jam_pickups'));
    }

    public function test_payment_only_update_does_not_require_complete_booking_fields(): void
    {
        $this->actingAsSuperAdmin();

        $bookingId = DB::table('bookings')->insertGetId([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'seat' => '1',
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => '',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'price' => 150000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.bookings.update'), [
            'booking_id' => $bookingId,
            'pembayaran' => 'Lunas',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('booking_id', $bookingId)
            ->assertJsonPath('pembayaran', 'Lunas');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'pembayaran' => 'Lunas',
            'pickup_point' => '',
        ]);
    }

    public function test_empty_departure_rejects_existing_active_departure(): void
    {
        $this->actingAsSuperAdmin();

        $date = '2026-05-15';
        $dow = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

        DB::table('schedules')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => $dow,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Unit 1',
            'created_at' => now(),
        ]);

        DB::table('trip_assignments')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $date,
            'jam' => '09:00:00',
            'unit' => 1,
            'status' => 'active',
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.bookings.empty-departure'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $date,
            'jam' => '09:00',
            'unit' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Keberangkatan untuk rute, tanggal, jam, dan unit ini sudah ada.');

        $this->assertDatabaseCount('trip_assignments', 1);
    }

    public function test_cancel_booking_marks_status_canceled(): void
    {
        $this->actingAsSuperAdmin();

        $bookingId = DB::table('bookings')->insertGetId([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'seat' => '1',
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'price' => 150000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.bookings.cancel'), [
            'booking_id' => $bookingId,
            'reason' => 'Test cancel',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('booking_id', $bookingId);

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'canceled',
        ]);
    }

    public function test_cancel_departure_clears_assignment_meta(): void
    {
        $this->actingAsSuperAdmin();

        $driverId = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER BATAL',
            'phone' => '081200000002',
            'created_at' => now(),
        ]);

        $assignmentId = DB::table('trip_assignments')->insertGetId([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'armada_id' => 99,
            'armada_nopol' => 'DD 9999 YY',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.bookings.cancel-departure'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('id', $assignmentId)
            ->assertJsonPath('status', 'canceled')
            ->assertJsonPath('driver_name', '-')
            ->assertJsonPath('armada_nopol', '-');

        $assignment = DB::table('trip_assignments')->where('id', $assignmentId)->first();

        $this->assertNotNull($assignment);
        $this->assertSame('canceled', $assignment->status);
        $this->assertNull($assignment->driver_id);
        $this->assertNull($assignment->armada_id);
        $this->assertNull($assignment->armada_nopol);
    }

    public function test_past_departure_auto_closes_manifest_when_marked_arrived(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 16, 8, 0, 0));

        try {
            $this->actingAsSuperAdmin();

            $tanggal = now()->subDay()->format('Y-m-d');
            $driverId = DB::table('drivers')->insertGetId([
                'nama' => 'DRIVER TEST',
                'phone' => '081200000001',
                'created_at' => now(),
            ]);

            $assignmentId = DB::table('trip_assignments')->insertGetId([
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '09:00:00',
                'unit' => 1,
                'driver_id' => $driverId,
                'armada_nopol' => 'DD 1234 XX',
                'status' => 'active',
                'created_at' => now(),
            ]);

            $departResponse = $this->postJson(route('api.bookings.depart-departure'), [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '09:00',
                'unit' => 1,
            ]);

            $departResponse->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('id', $assignmentId)
                ->assertJsonPath('status', 'departed');

            $response = $this->postJson(route('api.bookings.arrive-departure'), [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '09:00',
                'unit' => 1,
            ]);

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('id', $assignmentId)
                ->assertJsonPath('status', 'closed');

            $this->assertDatabaseHas('trip_assignments', [
                'id' => $assignmentId,
                'status' => 'closed',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_closed_manifest_blocks_booking_and_assignment_edits(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        DB::table('routes')->insert([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => Carbon::createFromFormat('Y-m-d', '2026-05-15')->dayOfWeek,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'created_at' => now(),
        ]);

        $driverId = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER LOCKED',
            'phone' => '081200000099',
            'created_at' => now(),
        ]);

        DB::table('armadas')->insert([
            'tenant_id' => $tenantId,
            'nopol' => 'DD 9999 ZZ',
            'created_at' => now(),
        ]);

        $bookingId = DB::table('bookings')->insertGetId([
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'seat' => '1',
            'name' => 'PENUMPANG LOCK',
            'phone' => '081200000090',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $assignmentId = DB::table('trip_assignments')->insertGetId([
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'armada_nopol' => 'DD 9999 ZZ',
            'status' => 'arrived',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $closeResponse = $this->postJson(route('api.bookings.close-manifest'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
        ]);

        $closeResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'closed');

        $this->assertDatabaseHas('trip_assignments', [
            'id' => $assignmentId,
            'status' => 'closed',
        ]);

        $bookingUpdate = $this->postJson(route('api.bookings.update'), [
            'booking_id' => $bookingId,
            'name' => 'PENUMPANG BARU',
        ]);

        $bookingUpdate->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Manifest sudah ditutup. Data booking tidak bisa diubah lagi.');

        $assignmentUpdate = $this->postJson(route('api.admin.assignments.save'), [
            'id' => $assignmentId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00',
            'unit' => 1,
            'driver_id' => $driverId,
        ]);

        $assignmentUpdate->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Manifest sudah ditutup. Data assignment tidak bisa diubah lagi.');
    }

    public function test_booking_updates_are_blocked_once_auto_close_time_is_reached(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        Carbon::setTestNow(Carbon::create(2026, 5, 17, 10, 0, 0));
        try {
            $routeId = DB::table('routes')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'PINRANG - MAKASSAR',
                'origin' => 'PINRANG',
                'destination' => 'MAKASSAR',
                'created_at' => now(),
            ]);

            DB::table('schedules')->insert([
                'tenant_id' => $tenantId,
                'rute' => 'PINRANG - MAKASSAR',
                'dow' => Carbon::createFromFormat('Y-m-d', '2026-05-16')->dayOfWeek,
                'jam' => '10:00:00',
                'units' => 1,
                'unit_label' => 'Reguler',
                'created_at' => now(),
            ]);

            $bookingId = DB::table('bookings')->insertGetId([
                'tenant_id' => $tenantId,
                'route_id' => $routeId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-05-16',
                'jam' => '10:00:00',
                'unit' => 1,
                'seat' => '1',
                'name' => 'PENUMPANG AUTO',
                'phone' => '081200000091',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('trip_assignments')->insert([
                'tenant_id' => $tenantId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-05-16',
                'jam' => '10:00:00',
                'unit' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $response = $this->postJson(route('api.bookings.update'), [
                'booking_id' => $bookingId,
                'pembayaran' => 'Lunas',
            ]);

            $response->assertStatus(409)
                ->assertJsonPath('success', false)
                ->assertJsonPath('error', 'Manifest sudah ditutup. Data booking tidak bisa diubah lagi.');

            $this->assertDatabaseHas('trip_assignments', [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-05-16',
                'jam' => '10:00:00',
                'status' => 'closed',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_departure_auto_closes_manifest_after_driver_and_nopol_are_set(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 31, 8, 0, 0));

        $tenantId = $this->defaultTenantId();
        try {
            $this->actingAsSuperAdmin();

            $tanggal = now()->format('Y-m-d');
            DB::table('routes')->insert([
                'tenant_id' => $tenantId,
                'name' => 'PINRANG - MAKASSAR',
                'origin' => 'PINRANG',
                'destination' => 'MAKASSAR',
                'created_at' => now(),
            ]);

            DB::table('schedules')->insert([
                'tenant_id' => $tenantId,
                'rute' => 'PINRANG - MAKASSAR',
                'dow' => Carbon::now()->dayOfWeek,
                'jam' => '23:59:00',
                'units' => 1,
                'unit_label' => 'Reguler',
                'created_at' => now(),
            ]);

            $driverId = DB::table('drivers')->insertGetId([
                'nama' => 'DRIVER HARI INI',
                'phone' => '081200000004',
                'created_at' => now(),
            ]);

            $assignmentId = DB::table('trip_assignments')->insertGetId([
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '23:59:00',
                'unit' => 1,
                'driver_id' => $driverId,
                'armada_nopol' => 'DD 5555 TT',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $depart = $this->postJson(route('api.bookings.depart-departure'), [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '23:59',
                'unit' => 1,
            ]);

            $depart->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('id', $assignmentId)
                ->assertJsonPath('status', 'departed');

            $response = $this->postJson(route('api.bookings.arrive-departure'), [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '23:59',
                'unit' => 1,
            ]);

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('id', $assignmentId)
                ->assertJsonPath('status', 'closed');

            $this->assertDatabaseHas('trip_assignments', [
                'id' => $assignmentId,
                'status' => 'closed',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_departure_cannot_be_marked_arrived_without_driver_and_nopol(): void
    {
        $this->actingAsSuperAdmin();

        $tanggal = now()->subDay()->format('Y-m-d');

        DB::table('trip_assignments')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $tanggal,
            'jam' => '09:00:00',
            'unit' => 1,
            'status' => 'active',
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.bookings.arrive-departure'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $tanggal,
            'jam' => '09:00',
            'unit' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Armada hanya bisa ditandai tiba jika Driver dan Nopol sudah diisi.');
    }

    public function test_bulk_payment_update_marks_all_booking_rows_lunas_in_one_request(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $bookingIds = [];
        foreach (['1', '2'] as $seat) {
            $bookingIds[] = DB::table('bookings')->insertGetId([
                'tenant_id' => $tenantId,
                'route_id' => $routeId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => now()->toDateString(),
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => $seat,
                'name' => 'PENUMPANG '.$seat,
                'phone' => '0812000001'.$seat.$seat.$seat,
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->postJson(route('api.bookings.bulk-payment'), [
            'booking_ids' => $bookingIds,
            'pembayaran' => 'Lunas',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment_status', 'Lunas')
            ->assertJsonPath('updated_count', 2);

        foreach ($bookingIds as $bookingId) {
            $this->assertDatabaseHas('bookings', [
                'id' => $bookingId,
                'pembayaran' => 'Lunas',
            ]);
        }
    }
}
