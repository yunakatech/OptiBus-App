<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_canceled_departure_is_rendered_with_empty_assignment_meta(): void
    {
        $this->actingAsSuperAdmin();

        $driverId = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER HISTORY',
            'phone' => '081200000003',
            'created_at' => now(),
        ]);

        DB::table('trip_assignments')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-05-15',
            'jam' => '09:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'armada_id' => 88,
            'armada_nopol' => 'DD 8888 ZZ',
            'status' => 'canceled',
            'created_at' => now(),
        ]);

        $this->get(route('bookings.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->has('bookingGroups', 1)
                ->where('bookingGroups.0.departure_status', 'canceled')
                ->where('bookingGroups.0.driver_name', '-')
                ->where('bookingGroups.0.armada_nopol', '-'),
            );
    }

    public function test_booking_route_filter_follows_master_routes_and_refund_is_counted_separately(): void
    {
        $this->actingAsSuperAdmin();

        DB::table('routes')->insert([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('bookings')->insert([
            [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-05-16',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => '1',
                'name' => 'PENUMPANG LUNAS',
                'phone' => '081200000010',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-05-16',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => '2',
                'name' => 'PENUMPANG REFUND',
                'phone' => '081200000011',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Refund',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'rute' => 'RUTE LIAR',
                'tanggal' => '2026-05-15',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => '1',
                'name' => 'PENUMPANG LIAR',
                'phone' => '081200000012',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 100000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $this->get(route('bookings.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->where('bookingRouteOptions', ['PINRANG - MAKASSAR'])
                ->has('bookingGroups', 2)
                ->where('bookingGroups.0.rute', 'PINRANG - MAKASSAR')
                ->where('bookingGroups.0.total', 2)
                ->where('bookingGroups.0.lunas', 1)
                ->where('bookingGroups.0.refund', 1)
                ->where('bookingGroups.0.belum_lunas', 0),
            );
    }
}
