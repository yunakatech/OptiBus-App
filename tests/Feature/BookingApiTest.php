<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_super_admin' => true]));
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

        DB::table('schedules')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => $dow,
            'jam' => '09:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'unit_id' => $unitId,
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
            ->assertJsonPath('schedules.0.seats', 12)
            ->assertJsonPath('schedules.0.unit_options.0.unit_no', 1)
            ->assertJsonPath('schedules.0.unit_options.0.label', 'Reguler');
    }

    public function test_submit_booking_and_detect_conflict(): void
    {
        $this->actingAsSuperAdmin();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
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

    public function test_past_departure_can_be_marked_arrived(): void
    {
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

        $response = $this->postJson(route('api.bookings.arrive-departure'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $tanggal,
            'jam' => '09:00',
            'unit' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('id', $assignmentId)
            ->assertJsonPath('status', 'arrived');

        $this->assertDatabaseHas('trip_assignments', [
            'id' => $assignmentId,
            'status' => 'arrived',
        ]);
    }

    public function test_same_day_departure_can_be_marked_arrived_after_driver_and_nopol_are_set(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 31, 8, 0, 0));

        try {
            $this->actingAsSuperAdmin();

            $tanggal = now()->format('Y-m-d');
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
            ]);

            $response = $this->postJson(route('api.bookings.arrive-departure'), [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $tanggal,
                'jam' => '23:59',
                'unit' => 1,
            ]);

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('id', $assignmentId)
                ->assertJsonPath('status', 'arrived');

            $this->assertDatabaseHas('trip_assignments', [
                'id' => $assignmentId,
                'status' => 'arrived',
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
}
