<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\BookingCode;
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

    public function test_booking_console_page_renders_the_booking_console_component(): void
    {
        $this->actingAsSuperAdmin();
        $this->createPool($this->defaultTenantId(), 'Default Booking Pool', 'DBP');

        $this->get(route('booking-console.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('BookingConsole')
                ->where('listOnly', false)
                ->has('migrationChecklist', 4));
    }

    public function test_canceled_departure_is_rendered_with_empty_assignment_meta(): void
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

        $this->get(route('bookings.index', ['from' => '2026-05-15', 'to' => '2026-05-15']))
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

    public function test_departure_date_range_and_status_are_applied_before_pagination(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        foreach (['departed', 'closed', 'closed', 'arrived'] as $index => $status) {
            DB::table('trip_assignments')->insert([
                'tenant_id' => $tenantId, 'rute' => 'RUTE FILTER', 'tanggal' => '2099-01-0'.($index + 1),
                'jam' => '09:00:00', 'unit' => 1, 'status' => $status, 'created_at' => now(),
            ]);
        }

        $this->get(route('bookings.index', [
            'from' => '2099-01-01', 'to' => '2099-01-03', 'departure_status' => 'closed', 'per_page' => 1, 'page' => 2,
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('bookingQuery.departure_status', 'closed')
            ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                ->where('bookingPagination.total', 2)
                ->where('bookingPagination.page', 2)
                ->has('bookingGroups', 1)
                ->where('bookingGroups.0.tanggal', '2099-01-03')
                ->where('bookingGroups.0.departure_status', 'closed')));
    }

    public function test_open_manifest_filter_includes_active_departed_and_arrived_before_pagination(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        foreach (['active', 'departed', 'arrived', 'closed', 'canceled'] as $index => $status) {
            DB::table('trip_assignments')->insert([
                'tenant_id' => $tenantId, 'rute' => 'RUTE FILTER', 'tanggal' => '2099-01-0'.($index + 1),
                'jam' => '09:00:00', 'unit' => 1, 'status' => $status, 'created_at' => now(),
            ]);
        }

        $this->get(route('bookings.index', [
            'from' => '2099-01-01', 'to' => '2099-01-05', 'departure_status' => 'open', 'per_page' => 2, 'page' => 2,
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('bookingQuery.departure_status', 'open')
            ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                ->where('bookingPagination.total', 3)
                ->where('bookingPagination.page', 2)
                ->has('bookingGroups', 1)
                ->where('bookingGroups.0.departure_status', 'arrived')));
    }

    public function test_departure_driver_and_plate_filters_apply_before_pagination(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        $driverId = DB::table('drivers')->insertGetId(['tenant_id' => $tenantId, 'nama' => 'DRIVER ANDI', 'created_at' => now()]);
        foreach (['DD 1234 AB', 'DD 1234 AB', 'DD 9999 XY'] as $index => $plate) {
            DB::table('trip_assignments')->insert([
                'tenant_id' => $tenantId, 'rute' => 'RUTE FILTER', 'tanggal' => '2099-01-0'.($index + 1),
                'jam' => '09:00:00', 'unit' => 1, 'driver_id' => $driverId,
                'armada_nopol' => $plate, 'status' => 'active', 'created_at' => now(),
            ]);
        }
        $this->getJson(route('api.bookings.filter-suggestions', ['type' => 'driver', 'q' => 'andi']))
            ->assertOk()->assertJsonPath('suggestions.0', 'DRIVER ANDI');
        $this->getJson(route('api.bookings.filter-suggestions', ['type' => 'nopol', 'q' => '1234']))
            ->assertOk()->assertJsonPath('suggestions.0', 'DD 1234 AB')->assertJsonCount(1, 'suggestions');
        $this->get(route('bookings.index', [
            'from' => '2099-01-01', 'to' => '2099-01-03', 'driver' => 'andi', 'nopol' => '1234', 'per_page' => 1, 'page' => 2,
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('bookingQuery.driver', 'andi')->where('bookingQuery.nopol', '1234')
            ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                ->where('bookingPagination.total', 2)->where('bookingPagination.page', 2)
                ->has('bookingGroups', 1)->where('bookingGroups.0.tanggal', '2099-01-02')));
    }

    public function test_manifest_number_filter_matches_generated_departure_code_before_pagination(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        foreach ([1, 2, 3] as $unit) {
            DB::table('trip_assignments')->insert([
                'tenant_id' => $tenantId, 'rute' => 'RUTE FILTER', 'tanggal' => '2099-01-01',
                'jam' => '09:00:00', 'unit' => $unit, 'status' => 'active', 'created_at' => now(),
            ]);
        }
        $code = BookingCode::departureCode('2099-01-01', '09:00', 2, 'RUTE FILTER');
        $this->get(route('bookings.index', [
            'from' => '2099-01-01', 'to' => '2099-01-01', 'manifest' => strtolower($code), 'per_page' => 1,
        ]))->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                ->where('bookingPagination.total', 1)->has('bookingGroups', 1)
                ->where('bookingGroups.0.departure_code', $code)));
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

        $this->get(route('bookings.index', ['from' => '2026-05-16', 'to' => '2026-05-16']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->missingAll(['bookingRouteOptions', 'bookingGroups'])
                ->loadDeferredProps('booking-list', fn (Assert $reload) => $reload
                    ->where('bookingRouteOptions', ['PINRANG - MAKASSAR'])
                    ->has('bookingGroups', 1)
                    ->where('bookingGroups.0.rute', 'PINRANG - MAKASSAR')
                    ->where('bookingGroups.0.total', 2)
                    ->where('bookingGroups.0.lunas', 1)
                    ->where('bookingGroups.0.refund', 1)
                    ->where('bookingGroups.0.belum_lunas', 0)),
            );
    }

    public function test_canceled_unpaid_passenger_remains_in_booking_detail_history_payload(): void
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
                'name' => 'PENUMPANG AKTIF',
                'phone' => '081200000030',
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
                'name' => 'PENUMPANG CANCEL',
                'phone' => '081200000031',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'canceled',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $groupKey = md5('PINRANG TO MAKASSAR|2026-05-16|09:00|1');

        $this->get(route('bookings.detail', ['groupKey' => $groupKey]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->has('bookingGroups', 1)
                ->where('bookingGroups.0.bookings.1.name', 'PENUMPANG CANCEL')
                ->where('bookingGroups.0.bookings.1.status', 'canceled')
                ->where('bookingGroups.0.bookings.1.pembayaran', 'Belum Lunas')
                ->where('bookingGroups.0.canceled', 1));
    }

    public function test_booking_detail_page_ignores_invalid_booking_dates_without_failing(): void
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
                'tanggal' => '2026-05-17',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => '1',
                'name' => 'PENUMPANG VALID',
                'phone' => '081200000020',
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
                'tanggal' => 'invalid-date',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => '2',
                'name' => 'PENUMPANG RUSAK',
                'phone' => '081200000021',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $groupKey = md5('PINRANG TO MAKASSAR|2026-05-17|09:00|1');

        $this->get(route('bookings.detail', ['groupKey' => $groupKey]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->where('groupDetailPage', true)
                ->where('groupDetailKey', $groupKey)
                ->has('bookingGroups', 1));
    }

    public function test_server_pages_whole_groups_without_sending_passenger_details(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        DB::table('routes')->insert(['tenant_id' => $tenantId, 'name' => 'A - B', 'created_at' => now()]);
        $rows = [];
        for ($unit = 1; $unit <= 3; $unit++) {
            for ($seat = 1; $seat <= 3; $seat++) {
                $rows[] = ['tenant_id' => $tenantId, 'rute' => 'A - B', 'tanggal' => '2026-11-04',
                    'jam' => '09:00:00', 'unit' => $unit, 'seat' => (string) $seat,
                    'name' => 'Penumpang '.$seat, 'phone' => '0812000'.$unit.$seat,
                    'status' => 'active', 'pembayaran' => 'Belum Lunas', 'price' => 100000,
                    'discount' => 10000, 'created_at' => now()];
            }
        }
        DB::table('bookings')->insert($rows);
        $url = route('bookings.index', ['from' => '2026-11-04', 'per_page' => 2]);
        $this->get($url)->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('booking-list', fn (Assert $data) => $data
                ->has('bookingGroups', 2)->where('bookingPagination.total', 3)
                ->where('bookingPagination.last_page', 2)
                ->where('bookingGroups.0.total', 3)
                ->where('bookingGroups.0.unpaid_amount', 270000)
                ->where('bookingGroups.0.bookings', [])
                ->where('bookingGroups.0.detail_loaded', false)
                ->where('bookingListSummary.activePassengers', 9)));
        $this->get($url.'&page=2')->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('booking-list', fn (Assert $data) => $data
                ->has('bookingGroups', 1)->where('bookingGroups.0.unit', 3)));
        $key = md5('A TO B|2026-11-04|09:00|3');
        $this->getJson('/api/bookings/groups/'.$key)->assertOk()
            ->assertJsonCount(3, 'group.bookings')->assertJsonPath('group.detail_loaded', true);
    }

    public function test_group_details_do_not_return_another_tenants_passengers_or_maps(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();
        $otherTenant = DB::table('tenants')->insertGetId(['name' => 'Other', 'slug' => 'other-mobile', 'status' => 'active', 'created_at' => now()]);
        foreach ([$tenantId, $otherTenant] as $tenant) {
            DB::table('routes')->insert(['tenant_id' => $tenant, 'name' => 'A - B', 'created_at' => now()]);
            DB::table('bookings')->insert(['tenant_id' => $tenant, 'rute' => 'A - B',
                'tanggal' => '2026-11-04', 'jam' => '09:00:00', 'unit' => 1,
                'seat' => '1', 'name' => $tenant === $tenantId ? 'Own' : 'Foreign',
                'phone' => '0812345', 'status' => 'active', 'pembayaran' => 'Lunas', 'created_at' => now()]);
        }
        DB::table('customers')->insert(['tenant_id' => $otherTenant, 'name' => 'Foreign', 'phone' => '0812345', 'gmaps' => 'foreign-maps', 'created_at' => now()]);
        $key = md5('A TO B|2026-11-04|09:00|1');
        $this->getJson('/api/bookings/groups/'.$key)->assertOk()
            ->assertJsonCount(1, 'group.bookings')->assertJsonPath('group.bookings.0.name', 'Own')
            ->assertJsonPath('group.bookings.0.gmaps', '');
        $this->getJson('/api/bookings/groups/'.str_repeat('f', 32))->assertNotFound();
        auth()->logout();
        $this->getJson('/api/bookings/groups/'.$key)->assertUnauthorized();
    }

    public function test_a_large_group_is_not_cut_at_the_old_booking_row_limit(): void
    {
        $this->actingAsSuperAdmin();
        $tenant = $this->defaultTenantId();
        DB::table('routes')->insert(['tenant_id' => $tenant, 'name' => 'A - B', 'created_at' => now()]);
        $rows = [];
        for ($seat = 1; $seat <= 2001; $seat++) {
            $rows[] = ['tenant_id' => $tenant, 'rute' => 'A - B', 'tanggal' => '2026-11-04',
                'jam' => '09:00:00', 'unit' => 1, 'seat' => (string) $seat,
                'name' => 'Penumpang '.$seat, 'phone' => '0812000'.$seat,
                'status' => 'active', 'pembayaran' => 'Lunas', 'created_at' => now()];
        }
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('bookings')->insert($chunk);
        }
        $this->get(route('bookings.index', ['from' => '2026-11-04', 'per_page' => 1]))
            ->assertInertia(fn (Assert $page) => $page->loadDeferredProps('booking-list', fn (Assert $data) => $data
                ->where('bookingGroups.0.total', 2001)->where('bookingGroups.0.bookings', [])));
        $this->getJson('/api/bookings/groups/'.md5('A TO B|2026-11-04|09:00|1'))
            ->assertOk()->assertJsonCount(2001, 'group.bookings');
    }

    private function createPool(int $tenantId, string $name, string $code): int
    {
        return (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => $code,
            'status' => 'active',
            'target_revenue' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
