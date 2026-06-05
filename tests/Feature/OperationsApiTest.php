<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use App\Support\PoolScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_super_admin' => true]));
    }

    public function test_master_data_endpoints_return_data(): void
    {
        $this->actingAsSuperAdmin();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('segments')->insert([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'harga' => 150000,
            'created_at' => now(),
        ]);

        DB::table('units')->insert([
            'nopol' => 'DD 1122 ZZ',
            'merek' => 'Isuzu',
            'type' => 'Elf',
            'kapasitas' => 12,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        DB::table('drivers')->insert([
            'nama' => 'ANDI SOPIR',
            'phone' => '081234567890',
            'created_at' => now(),
        ]);

        DB::table('luggage_services')->insert([
            'name' => 'Dokumen',
            'created_at' => now(),
        ]);

        DB::table('master_carter')->insert([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'duration' => 'Regular',
            'rental_price' => 2500000,
            'bop_price' => 200000,
            'created_at' => now(),
        ]);

        DB::table('customers')->insert([
            'name' => 'RIDWAN',
            'phone' => '081111111111',
            'pickup_point' => 'Terminal',
            'gmaps' => 'Pinrang',
            'created_at' => now(),
        ]);

        $this->getJson(route('api.master.segments', ['route_name' => 'PINRANG - MAKASSAR']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'segments');

        $segmentId = (int) DB::table('segments')->value('id');
        $this->getJson(route('api.master.segment-price', ['id' => $segmentId]))
            ->assertOk()
            ->assertJsonPath('price', 150000);

        $this->getJson(route('api.master.units'))
            ->assertOk()
            ->assertJsonCount(1, 'units');

        $this->getJson(route('api.master.drivers'))
            ->assertOk()
            ->assertJsonCount(1, 'drivers');

        $this->getJson(route('api.master.luggage-services'))
            ->assertOk()
            ->assertJsonCount(1, 'services');

        $this->getJson(route('api.master.charter-routes'))
            ->assertOk()
            ->assertJsonCount(1, 'routes');

        $this->getJson(route('api.master.customers.search', ['q' => 'rid']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('scope_limited', false);
    }

    public function test_customer_search_matches_split_terms_address_and_phone_variants(): void
    {
        $this->actingAsSuperAdmin();

        DB::table('customers')->insert([
            [
                'name' => 'RIDWAN SAPUTRA',
                'phone' => '+62 (812)-987.654',
                'pickup_point' => 'Terminal Daya',
                'gmaps' => 'Jalan Perintis Makassar',
                'created_at' => now(),
            ],
            [
                'name' => 'RIDWAN LAIN',
                'phone' => '089999999999',
                'pickup_point' => 'Pasar Sentral',
                'gmaps' => 'Pinrang',
                'created_at' => now(),
            ],
        ]);

        $this->getJson(route('api.master.customers.search', ['q' => 'ridwan terminal']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.phone', '+62 (812)-987.654');

        $this->getJson(route('api.master.customers.search', ['q' => '0812987']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.name', 'RIDWAN SAPUTRA');

        $this->getJson(route('api.master.customers.search', ['q' => 'perintis']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.name', 'RIDWAN SAPUTRA');
    }

    public function test_pool_operator_can_search_customers_using_route_id_and_normalized_legacy_route(): void
    {
        AccessControl::syncDefaults();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG -> MAKASSAR',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'created_at' => now(),
        ]);
        $outsideRouteId = DB::table('routes')->insertGetId([
            'name' => 'MAKASSAR -> PAREPARE',
            'origin' => 'Makassar',
            'destination' => 'Parepare',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'name' => 'POOL PINRANG',
            'code' => 'PNR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outsidePoolId = DB::table('pools')->insertGetId([
            'name' => 'POOL MAKASSAR',
            'code' => 'MKS',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            [
                'pool_id' => $poolId,
                'route_id' => $routeId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pool_id' => $outsidePoolId,
                'route_id' => $outsideRouteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $operator = User::factory()->create(['is_super_admin' => false]);
        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => DB::table('roles')->where('slug', 'operator-booking')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            [
                'pool_id' => null,
                'name' => 'CUSTOMER LEGACY ROUTE',
                'phone' => '081200000001',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
            [
                'pool_id' => null,
                'name' => 'CUSTOMER ROUTE ID',
                'phone' => '081200000002',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
            [
                'pool_id' => null,
                'name' => 'CUSTOMER LUAR POOL',
                'phone' => '081200000003',
                'pickup_point' => 'Makassar',
                'created_at' => now(),
            ],
            [
                'pool_id' => null,
                'name' => 'CUSTOMER ROUTE ID SALAH',
                'phone' => '081200000004',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
            [
                'pool_id' => $poolId,
                'name' => 'CUSTOMER TANPA BOOKING',
                'phone' => '081200000005',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
        ]);
        DB::table('bookings')->insert([
            [
                'route_id' => null,
                'rute' => 'Pinrang - Makassar',
                'tanggal' => '2026-06-05',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'CUSTOMER LEGACY ROUTE',
                'phone' => '081200000001',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'route_id' => $routeId,
                'rute' => 'LABEL BOOKING LAMA',
                'tanggal' => '2026-06-05',
                'jam' => '10:00:00',
                'unit' => 1,
                'seat' => 'A2',
                'name' => 'CUSTOMER ROUTE ID',
                'phone' => '081200000002',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'route_id' => $outsideRouteId,
                'rute' => 'MAKASSAR - PAREPARE',
                'tanggal' => '2026-06-05',
                'jam' => '11:00:00',
                'unit' => 1,
                'seat' => 'A3',
                'name' => 'CUSTOMER LUAR POOL',
                'phone' => '081200000003',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'route_id' => $routeId,
                'rute' => 'Pinrang - Makassar',
                'tanggal' => '2026-06-05',
                'jam' => '12:00:00',
                'unit' => 1,
                'seat' => 'A4',
                'name' => 'CUSTOMER ROUTE ID SALAH',
                'phone' => '081200000004',
                'status' => 'active',
                'created_at' => now(),
            ],
        ]);
        DB::table('customers')
            ->where('phone', '081200000004')
            ->update(['pool_id' => $outsidePoolId]);

        $this->actingAs($operator);

        $this->assertTrue(PoolScope::canAccessRouteName('Pinrang - Makassar'));

        $this->getJson(route('api.master.customers.search', ['q' => 'customer']))
            ->assertOk()
            ->assertJsonCount(3, 'customers')
            ->assertJsonPath('scope_limited', true)
            ->assertJsonPath('scope_name', 'POOL PINRANG')
            ->assertJsonFragment(['name' => 'CUSTOMER LEGACY ROUTE'])
            ->assertJsonFragment(['name' => 'CUSTOMER ROUTE ID'])
            ->assertJsonFragment(['name' => 'CUSTOMER TANPA BOOKING'])
            ->assertJsonMissing(['name' => 'CUSTOMER LUAR POOL'])
            ->assertJsonMissing(['name' => 'CUSTOMER ROUTE ID SALAH']);
    }

    public function test_pool_operator_can_search_master_bagasi_and_charter_customers_by_pool_without_transactions(): void
    {
        AccessControl::syncDefaults();

        $poolId = DB::table('pools')->insertGetId([
            'name' => 'POOL PINRANG',
            'code' => 'PNR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outsidePoolId = DB::table('pools')->insertGetId([
            'name' => 'POOL MAKASSAR',
            'code' => 'MKS',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operator = User::factory()->create(['is_super_admin' => false]);
        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => DB::table('roles')->where('slug', 'operator-bagasi')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_bagasi')->insert([
            [
                'pool_id' => $poolId,
                'nama' => 'BAGASI PINRANG',
                'no_hp' => '081300000001',
                'alamat' => 'Pinrang',
                'tipe' => 'pengirim',
                'created_at' => now(),
            ],
            [
                'pool_id' => $outsidePoolId,
                'nama' => 'BAGASI LUAR',
                'no_hp' => '081300000002',
                'alamat' => 'Makassar',
                'tipe' => 'pengirim',
                'created_at' => now(),
            ],
        ]);
        DB::table('customer_charter')->insert([
            [
                'pool_id' => $poolId,
                'nama' => 'CARTER PINRANG',
                'no_hp' => '081400000001',
                'alamat' => 'Pinrang',
                'company' => 'PT Pinrang',
                'created_at' => now(),
            ],
            [
                'pool_id' => $outsidePoolId,
                'nama' => 'CARTER LUAR',
                'no_hp' => '081400000002',
                'alamat' => 'Makassar',
                'company' => 'PT Makassar',
                'created_at' => now(),
            ],
        ]);

        $this->actingAs($operator);

        $this->getJson(route('api.admin.customer-bagasi.index', ['q' => 'bagasi']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.nama', 'BAGASI PINRANG');

        $this->getJson(route('api.admin.customer-charter.index', ['q' => 'carter']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.nama', 'CARTER PINRANG');
    }

    public function test_submit_charter_and_luggage(): void
    {
        $this->actingAsSuperAdmin();

        $unitId = DB::table('units')->insertGetId([
            'nopol' => 'DD 5566 QQ',
            'merek' => 'Toyota',
            'type' => 'Hiace',
            'kapasitas' => 14,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        $serviceId = DB::table('luggage_services')->insertGetId([
            'name' => 'Paket Sedang',
            'created_at' => now(),
        ]);

        $this->postJson(route('api.ops.charters.submit'), [
            'name' => 'PT MAJU',
            'company_name' => 'PT MAJU',
            'phone' => '0812',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-20',
            'departure_time' => '08:30',
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'unit_id' => $unitId,
            'driver_name' => 'ANDI',
            'price' => 3000000,
            'layanan' => 'Regular',
            'bop_price' => 200000,
        ])->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('charters', 1);

        $this->postJson(route('api.ops.luggages.submit'), [
            'sender_name' => 'A',
            'sender_phone' => '0812',
            'sender_address' => 'Pinrang',
            'receiver_name' => 'B',
            'receiver_phone' => '0822',
            'receiver_address' => 'Makassar',
            'service_id' => $serviceId,
            'quantity' => 2,
            'notes' => 'Fragile',
            'price' => 100000,
        ])->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('luggages', 1);
    }

    public function test_submit_luggage_raw_endpoint_upserts_customers(): void
    {
        $this->actingAsSuperAdmin();

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'RUTE RAW OPS',
            'origin' => 'A',
            'destination' => 'B',
            'created_at' => now(),
        ]);
        $serviceId = DB::table('luggage_services')->insertGetId([
            'name' => 'Service Raw Ops',
            'created_at' => now(),
        ]);
        $this->postJson(route('api.ops.luggages.submit-raw'), [
            'sender_name' => 'Raw Ops Sender',
            'sender_phone' => '0812 123',
            'sender_address' => 'Alamat A',
            'receiver_name' => 'Raw Ops Receiver',
            'receiver_phone' => '0813 456',
            'receiver_address' => 'Alamat B',
            'rute_id' => $routeId,
            'layanan_id' => $serviceId,
            'price' => 0,
            'quantity' => 1,
        ])->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('luggages', 1);
        $this->assertDatabaseCount('customer_bagasi', 2);
        $this->assertDatabaseHas('luggages', [
            'rute_id' => $routeId,
            'layanan_id' => $serviceId,
            'price' => 0,
        ]);
    }
}
