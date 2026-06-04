<?php

namespace Tests\Feature;

use App\Models\User;
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
            ->assertJsonCount(1, 'customers');
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
