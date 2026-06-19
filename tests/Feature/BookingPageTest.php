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
        return $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_canceled_departure_is_rendered_with_empty_assignment_meta(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $driverId = DB::table('drivers')->insertGetId([
            'tenant_id' => $tenantId,
            'nama' => 'DRIVER HISTORY',
            'phone' => '081200000003',
            'created_at' => now(),
        ]);

        DB::table('trip_assignments')->insert([
            'tenant_id' => $tenantId,
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
                ->missing('bookingGroups')
                ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                    ->has('bookingGroups', 1)
                    ->where('bookingGroups.0.departure_status', 'canceled')
                    ->where('bookingGroups.0.driver_name', '-')
                    ->where('bookingGroups.0.armada_nopol', '-')),
            );
    }

    public function test_booking_route_filter_follows_master_routes_and_refund_is_counted_separately(): void
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

        DB::table('bookings')->insert([
            [
                'tenant_id' => $tenantId,
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
                'tenant_id' => $tenantId,
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
                'tenant_id' => $tenantId,
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
                ->missingAll(['bookingRouteOptions', 'bookingGroups'])
                ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                    ->where('bookingRouteOptions', ['PINRANG - MAKASSAR'])
                    ->has('bookingGroups', 2)
                    ->where('bookingGroups.0.rute', 'PINRANG - MAKASSAR')
                    ->where('bookingGroups.0.total', 2)
                    ->where('bookingGroups.0.lunas', 1)
                    ->where('bookingGroups.0.refund', 1)
                    ->where('bookingGroups.0.belum_lunas', 0)),
            );
    }
}
