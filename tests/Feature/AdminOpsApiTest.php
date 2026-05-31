<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOpsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_crud_works(): void
    {
        $this->actingAs(User::factory()->create());

        $create = $this->postJson(route('api.admin.routes.save'), [
            'name' => 'BONE - MAKASSAR',
            'origin' => 'BONE',
            'destination' => 'MAKASSAR',
        ])->assertCreated()->json();

        $id = (int) ($create['id'] ?? 0);
        $this->assertTrue($id > 0);

        $this->postJson(route('api.admin.routes.save'), [
            'id' => $id,
            'name' => 'BONE - MAKASSAR VIA MAROS',
            'origin' => 'BONE',
            'destination' => 'MAKASSAR',
        ])->assertOk();

        $this->deleteJson(route('api.admin.routes.delete', ['id' => $id]))->assertOk();
        $this->assertDatabaseMissing('routes', ['id' => $id]);
    }

    public function test_schedule_duplicate_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => 1,
            'jam' => '08:00:00',
            'units' => 1,
            'unit_label' => 'Reguler',
            'created_at' => now(),
        ]);

        $this->postJson(route('api.admin.schedules.save'), [
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
            'dow' => 1,
            'jam' => '08:00',
            'units' => 1,
            'unit_label' => 'Reguler',
        ])->assertStatus(409);
    }

    public function test_driver_and_luggage_service_crud_works(): void
    {
        $this->actingAs(User::factory()->create());

        $unitId = DB::table('units')->insertGetId([
            'nopol' => 'DD 7788 XX',
            'merek' => 'Isuzu',
            'type' => 'Elf',
            'kapasitas' => 12,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        $driver = $this->postJson(route('api.admin.drivers.save'), [
            'nama' => 'SUPIR TEST',
            'phone' => '08123',
            'unit_id' => $unitId,
        ])->assertCreated()->json();

        $driverId = (int) ($driver['id'] ?? 0);
        $this->deleteJson(route('api.admin.drivers.delete', ['id' => $driverId]))->assertOk();

        $service = $this->postJson(route('api.admin.luggage-services.save'), [
            'name' => 'Dokumen Kilat',
        ])->assertCreated()->json();

        $serviceId = (int) ($service['id'] ?? 0);
        $this->deleteJson(route('api.admin.luggage-services.delete', ['id' => $serviceId]))->assertOk();
    }

    public function test_units_and_users_crud_works(): void
    {
        $authUser = User::factory()->create([
            'name' => 'Root Admin',
            'email' => 'root.admin@example.com',
        ]);
        $this->actingAs($authUser);

        $unitCreate = $this->postJson(route('api.admin.units.save'), [
            'nopol' => 'DD 9900 AA',
            'merek' => 'Toyota',
            'type' => 'Hiace',
            'category' => 'Micro Bus',
            'tahun' => 2020,
            'warna' => 'Silver',
            'kapasitas' => 14,
            'status' => 'Aktif',
        ])->assertCreated()->json();

        $unitId = (int) ($unitCreate['id'] ?? 0);
        $this->assertTrue($unitId > 0);

        $this->postJson(route('api.admin.units.save'), [
            'id' => $unitId,
            'nopol' => 'DD 9900 AB',
            'merek' => 'Toyota',
            'type' => 'Hiace Premio',
            'category' => 'Micro Bus',
            'tahun' => 2022,
            'warna' => 'White',
            'kapasitas' => 15,
            'status' => 'Aktif',
        ])->assertOk();

        $this->getJson(route('api.admin.units.index'))
            ->assertOk()
            ->assertJsonPath('units.0.nopol', 'DD 9900 AB');

        $userCreate = $this->postJson(route('api.admin.users.save'), [
            'name' => 'Ops Baru',
            'email' => 'ops.baru@example.com',
            'password' => 'password123',
        ])->assertCreated()->json();

        $userId = (int) ($userCreate['id'] ?? 0);
        $this->assertTrue($userId > 0);

        $this->postJson(route('api.admin.users.save'), [
            'id' => $userId,
            'name' => 'Ops Baru Update',
            'email' => 'ops.baru@example.com',
        ])->assertOk();

        $this->getJson(route('api.admin.users.index', ['q' => 'ops baru']))
            ->assertOk()
            ->assertJsonCount(1, 'users');

        $this->deleteJson(route('api.admin.users.delete', ['id' => $authUser->id]))
            ->assertStatus(409);

        $this->deleteJson(route('api.admin.users.delete', ['id' => $userId]))->assertOk();
        $this->deleteJson(route('api.admin.units.delete', ['id' => $unitId]))->assertOk();
    }

    public function test_segments_customers_and_reports_endpoints_work(): void
    {
        $this->actingAs(User::factory()->create());

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $segmentCreate = $this->postJson(route('api.admin.segments.save'), [
            'route_id' => $routeId,
            'rute' => 'PINRANG - PAREPARE',
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'pickup_time' => '07:30',
            'harga' => 75000,
        ])->assertCreated()->json();

        $segmentId = (int) ($segmentCreate['id'] ?? 0);
        $this->assertTrue($segmentId > 0);
        $segments = $this->getJson(route('api.admin.segments.index'))
            ->assertOk()
            ->json('segments');
        $this->assertTrue(
            collect($segments)->contains(
                fn (array $row) => (int) ($row['id'] ?? 0) === $segmentId,
            ),
        );

        $customerCreate = $this->postJson(route('api.admin.customers.save'), [
            'name' => 'RIDWAN',
            'phone' => '089999000111',
            'pickup_point' => 'Terminal',
            'address' => 'Pinrang',
        ])->assertCreated()->json();

        $customerId = (int) ($customerCreate['id'] ?? 0);
        $this->assertTrue($customerId > 0);
        $customers = $this->getJson(route('api.admin.customers.index', ['q' => 'rid']))
            ->assertOk()
            ->json('customers');
        $this->assertTrue(
            collect($customers)->contains(
                fn (array $row) => (int) ($row['id'] ?? 0) === $customerId,
            ),
        );

        $bookingId = DB::table('bookings')->insertGetId([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => now()->toDateString(),
            'jam' => '08:00:00',
            'unit' => 1,
            'seat' => 'A1',
            'name' => 'RIDWAN',
            'phone' => '089999000111',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'canceled',
            'price' => 120000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        DB::table('cancellations')->insert([
            'booking_id' => $bookingId,
            'admin_user' => 'admin@cabooq.local',
            'reason' => 'Testing',
            'created_at' => now(),
        ]);
        \App\Support\ActivityLog::write(
            'CANCEL',
            'Testing cancellation log',
            'Testing',
            'tester@example.com',
        );

        $cancellations = $this->getJson(route('api.admin.cancellations.index'))
            ->assertOk()
            ->json('cancellations');
        $this->assertTrue(
            collect($cancellations)->contains(
                fn (array $row) => str_contains((string) ($row['title'] ?? ''), 'Testing cancellation log'),
            ),
        );

        DB::table('bookings')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => now()->toDateString(),
            'jam' => '10:00:00',
            'unit' => 1,
            'seat' => 'A2',
            'name' => 'REPORT AKTIF',
            'phone' => '08111111111',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 120000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $report = $this->getJson(route('api.admin.reports.summary', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
            'type' => 'booking',
        ]))
            ->assertOk()
            ->json();

        $this->assertSame('booking', $report['summary']['type'] ?? null);
        $this->assertSame(1, $report['summary']['total_rows'] ?? null);
        $this->assertEquals(120000.0, $report['summary']['revenue_total'] ?? 0);
        $this->assertCount(1, $report['rows'] ?? []);

        $this->deleteJson(route('api.admin.segments.delete', ['id' => $segmentId]))->assertOk();
        $this->deleteJson(route('api.admin.customers.delete', ['id' => $customerId]))->assertOk();
    }

    public function test_driver_and_armada_revenue_include_luggage_revenue_in_totals(): void
    {
        $this->actingAs(User::factory()->create());

        $today = now()->toDateString();
        $armadaNopol = 'DD 1234 UX';

        $armadaPayload = [
            'merk' => 'ISUZU',
            'tahun' => 2024,
            'warna' => 'PUTIH',
            'nopol' => $armadaNopol,
            'kategori' => 'MICRO BUS',
            'ac_type' => 'AC',
            'target_bulanan' => 1000000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
            $armadaPayload['nama_kendaraan'] = 'ARMADA BAGASI';
        }

        $armadaId = DB::table('armadas')->insertGetId($armadaPayload);

        $driverPayload = [
            'nama' => 'DRIVER BAGASI',
            'phone' => '08123456789',
            'target_revenue_bulanan' => 800000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('drivers', 'armada_id')) {
            $driverPayload['armada_id'] = $armadaId;
        } else {
            $driverPayload['unit_id'] = null;
        }

        if (Schema::hasColumn('drivers', 'armada_nopol')) {
            $driverPayload['armada_nopol'] = $armadaNopol;
        }

        $driverId = DB::table('drivers')->insertGetId($driverPayload);

        $assignmentPayload = [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('trip_assignments', 'armada_id')) {
            $assignmentPayload['armada_id'] = $armadaId;
        }

        if (Schema::hasColumn('trip_assignments', 'armada_nopol')) {
            $assignmentPayload['armada_nopol'] = $armadaNopol;
        }

        if (Schema::hasColumn('trip_assignments', 'status')) {
            $assignmentPayload['status'] = 'active';
        }

        $assignmentId = DB::table('trip_assignments')->insertGetId($assignmentPayload);

        DB::table('bookings')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'seat' => 'B1',
            'name' => 'PENUMPANG TEST',
            'phone' => '0812000001',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 100000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        DB::table('luggages')->insert([
            'sender_name' => 'PENGIRIM BAGASI',
            'sender_phone' => '0812000002',
            'receiver_name' => 'PENERIMA BAGASI',
            'receiver_phone' => '0812000003',
            'quantity' => 1,
            'price' => 50000,
            'status' => 'active',
            'payment_status' => 'Lunas',
            'trip_assignment_id' => $assignmentId,
            'created_at' => now(),
        ]);

        $luggageRows = $this->getJson(route('api.admin.luggages.index', ['q' => 'PENGIRIM BAGASI']))
            ->assertOk()
            ->json('luggages');

        $luggageRow = collect($luggageRows)->firstWhere('sender_name', 'PENGIRIM BAGASI');
        $this->assertNotNull($luggageRow);
        $this->assertSame('Dalam Perjalanan', $luggageRow['status'] ?? null);
        $this->assertSame($today, $luggageRow['departure_date'] ?? null);
        $this->assertSame('08:00:00', $luggageRow['departure_time'] ?? null);
        $this->assertSame(1, (int) ($luggageRow['departure_unit'] ?? 0));
        $this->assertSame('DRIVER BAGASI', $luggageRow['departure_driver_name'] ?? null);
        $this->assertSame($armadaNopol, $luggageRow['departure_armada_nopol'] ?? null);

        $historicalAssignmentPayload = $assignmentPayload;
        $historicalAssignmentPayload['tanggal'] = now()->subMonth()->toDateString();
        $historicalAssignmentId = DB::table('trip_assignments')->insertGetId($historicalAssignmentPayload);

        DB::table('luggages')->insert([
            'sender_name' => 'PENGIRIM BAGASI LAMA',
            'sender_phone' => '0812000012',
            'receiver_name' => 'PENERIMA BAGASI LAMA',
            'receiver_phone' => '0812000013',
            'quantity' => 1,
            'price' => 25000,
            'status' => 'active',
            'payment_status' => 'Lunas',
            'tanggal' => $today,
            'trip_assignment_id' => $historicalAssignmentId,
            'created_at' => now(),
        ]);

        $charterPayload = [
            'name' => 'CHARTER BAGASI',
            'phone' => '0812000004',
            'start_date' => $today,
            'end_date' => $today,
            'departure_time' => '09:00:00',
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'driver_name' => 'DRIVER BAGASI',
            'price' => 300000,
            'bop_price' => 40000,
            'payment_status' => 'Lunas',
            'bop_status' => 'done',
            'created_at' => now(),
        ];

        if (Schema::hasColumn('charters', 'armada_id')) {
            $charterPayload['armada_id'] = $armadaId;
        }

        if (Schema::hasColumn('charters', 'armada_nopol')) {
            $charterPayload['armada_nopol'] = $armadaNopol;
        }

        if (Schema::hasColumn('charters', 'status')) {
            $charterPayload['status'] = 'done';
        }

        DB::table('charters')->insert($charterPayload);

        $drivers = $this->getJson(route('api.admin.drivers.index'))
            ->assertOk()
            ->json('drivers');

        $driver = collect($drivers)->firstWhere('id', $driverId);
        $this->assertNotNull($driver);
        $this->assertEquals(75000.0, $driver['luggage_revenue'] ?? 0);
        $this->assertEquals(100000.0, $driver['departure_revenue'] ?? 0);
        $this->assertEquals(300000.0, $driver['charter_revenue'] ?? 0);
        $this->assertEquals(475000.0, $driver['revenue'] ?? 0);

        $armadas = $this->getJson(route('api.admin.armadas.index'))
            ->assertOk()
            ->json('armadas');

        $armada = collect($armadas)->firstWhere('id', $armadaId);
        $this->assertNotNull($armada);
        $this->assertEquals(75000.0, $armada['luggage_revenue'] ?? 0);
        $this->assertEquals(100000.0, $armada['departure_revenue'] ?? 0);
        $this->assertEquals(300000.0, $armada['charter_revenue'] ?? 0);
        $this->assertEquals(475000.0, $armada['revenue'] ?? 0);
    }

    public function test_driver_and_armada_luggage_revenue_refresh_immediately_after_mapping_existing_luggage(): void
    {
        $this->actingAs(User::factory()->create());
        Cache::flush();

        $today = now()->toDateString();
        $armadaNopol = 'DD 4321 UX';

        $armadaPayload = [
            'merk' => 'ISUZU',
            'tahun' => 2024,
            'warna' => 'PUTIH',
            'nopol' => $armadaNopol,
            'kategori' => 'MICRO BUS',
            'ac_type' => 'AC',
            'target_bulanan' => 1000000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
            $armadaPayload['nama_kendaraan'] = 'ARMADA CACHE BAGASI';
        }

        $armadaId = DB::table('armadas')->insertGetId($armadaPayload);

        $driverPayload = [
            'nama' => 'DRIVER CACHE BAGASI',
            'phone' => '08123456780',
            'target_revenue_bulanan' => 800000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('drivers', 'armada_id')) {
            $driverPayload['armada_id'] = $armadaId;
        } else {
            $driverPayload['unit_id'] = null;
        }

        if (Schema::hasColumn('drivers', 'armada_nopol')) {
            $driverPayload['armada_nopol'] = $armadaNopol;
        }

        $driverId = DB::table('drivers')->insertGetId($driverPayload);

        $assignmentPayload = [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('trip_assignments', 'armada_id')) {
            $assignmentPayload['armada_id'] = $armadaId;
        }

        if (Schema::hasColumn('trip_assignments', 'armada_nopol')) {
            $assignmentPayload['armada_nopol'] = $armadaNopol;
        }

        if (Schema::hasColumn('trip_assignments', 'status')) {
            $assignmentPayload['status'] = 'active';
        }

        $assignmentId = DB::table('trip_assignments')->insertGetId($assignmentPayload);

        $luggageId = DB::table('luggages')->insertGetId([
            'sender_name' => 'PENGIRIM CACHE',
            'sender_phone' => '0812000100',
            'receiver_name' => 'PENERIMA CACHE',
            'receiver_phone' => '0812000101',
            'quantity' => 1,
            'price' => 50000,
            'status' => 'active',
            'payment_status' => 'Lunas',
            'tanggal' => $today,
            'trip_assignment_id' => null,
            'created_at' => now(),
        ]);

        $driversBefore = $this->getJson(route('api.admin.drivers.index'))
            ->assertOk()
            ->json('drivers');
        $driverBefore = collect($driversBefore)->firstWhere('id', $driverId);
        $this->assertNotNull($driverBefore);
        $this->assertEquals(0.0, $driverBefore['luggage_revenue'] ?? 0);

        $armadasBefore = $this->getJson(route('api.admin.armadas.index'))
            ->assertOk()
            ->json('armadas');
        $armadaBefore = collect($armadasBefore)->firstWhere('id', $armadaId);
        $this->assertNotNull($armadaBefore);
        $this->assertEquals(0.0, $armadaBefore['luggage_revenue'] ?? 0);

        DB::table('luggages')->where('id', $luggageId)->update([
            'trip_assignment_id' => $assignmentId,
        ]);

        $driversAfter = $this->getJson(route('api.admin.drivers.index'))
            ->assertOk()
            ->json('drivers');
        $driverAfter = collect($driversAfter)->firstWhere('id', $driverId);
        $this->assertNotNull($driverAfter);
        $this->assertEquals(50000.0, $driverAfter['luggage_revenue'] ?? 0);
        $this->assertEquals(50000.0, $driverAfter['revenue'] ?? 0);

        $armadasAfter = $this->getJson(route('api.admin.armadas.index'))
            ->assertOk()
            ->json('armadas');
        $armadaAfter = collect($armadasAfter)->firstWhere('id', $armadaId);
        $this->assertNotNull($armadaAfter);
        $this->assertEquals(50000.0, $armadaAfter['luggage_revenue'] ?? 0);
        $this->assertEquals(50000.0, $armadaAfter['revenue'] ?? 0);
    }

    public function test_charter_luggage_assignment_and_csv_endpoints_work(): void
    {
        $this->actingAs(User::factory()->create());

        $unitId = DB::table('units')->insertGetId([
            'nopol' => 'DD 8899 ZZ',
            'merek' => 'Isuzu',
            'type' => 'Elf',
            'kapasitas' => 12,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        $driverId = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER BARU',
            'phone' => '0812',
            'unit_id' => $unitId,
            'created_at' => now(),
        ]);

        $serviceId = DB::table('luggage_services')->insertGetId([
            'name' => 'Dokumen',
            'created_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $charter = $this->postJson(route('api.admin.charters.save'), [
            'name' => 'ROMBONGAN TEST',
            'company_name' => 'PT TEST',
            'phone' => '08129999',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-20',
            'departure_time' => '09:00',
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'unit_id' => $unitId,
            'driver_name' => 'DRIVER BARU',
            'price' => 3000000,
            'layanan' => 'VIP',
            'bop_price' => 500000,
            'bop_status' => 'pending',
            'payment_status' => 'DP',
        ])->assertCreated()->json();
        $charterId = (int) ($charter['id'] ?? 0);
        $this->assertTrue($charterId > 0);
        $this->getJson(route('api.admin.charters.index', ['payment_status' => 'DP']))
            ->assertOk()
            ->assertJsonCount(1, 'charters');
        $this->assertDatabaseHas('charters', [
            'id' => $charterId,
            'layanan' => 'VIP',
            'payment_status' => 'DP',
        ]);
        $this->assertDatabaseHas('customer_charter', [
            'nama' => 'ROMBONGAN TEST',
            'no_hp' => '08129999',
            'alamat' => 'Pinrang',
            'company' => 'PT TEST',
        ]);

        $this->postJson(route('api.admin.charters.save'), [
            'id' => $charterId,
            'name' => 'ROMBONGAN TEST UPDATE',
            'company_name' => 'PT TEST UPDATE',
            'phone' => '08129999',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-20',
            'departure_time' => '09:00',
            'pickup_point' => 'Terminal Pinrang',
            'drop_point' => 'Makassar',
            'unit_id' => $unitId,
            'driver_name' => 'DRIVER BARU',
            'price' => 3000000,
            'layanan' => 'VIP',
            'bop_price' => 500000,
            'bop_status' => 'pending',
            'payment_status' => 'DP',
        ])->assertOk();
        $this->assertSame(1, DB::table('customer_charter')->where('no_hp', '08129999')->count());
        $this->assertDatabaseHas('customer_charter', [
            'nama' => 'ROMBONGAN TEST UPDATE',
            'no_hp' => '08129999',
            'alamat' => 'Terminal Pinrang',
            'company' => 'PT TEST UPDATE',
        ]);

        $luggage = $this->postJson(route('api.admin.luggages.save'), [
            'sender_name' => 'A',
            'sender_phone' => '0811',
            'sender_address' => 'Pinrang',
            'receiver_name' => 'B',
            'receiver_phone' => '0822',
            'receiver_address' => 'Makassar',
            'service_id' => $serviceId,
            'rute_id' => $routeId,
            'quantity' => 2,
            'notes' => 'Handle with care',
            'price' => 50000,
            'status' => 'pending',
            'payment_status' => 'Belum Bayar',
        ])->assertCreated()->json();
        $luggageId = (int) ($luggage['id'] ?? 0);
        $this->assertTrue($luggageId > 0);
        $resi = (string) (DB::table('luggages')->where('id', $luggageId)->value('kode_resi') ?? '');

        $this->getJson(route('api.admin.luggages.index', ['status' => 'Diterima', 'payment_status' => 'Belum Bayar']))
            ->assertOk()
            ->assertJsonCount(1, 'luggages')
            ->assertJsonPath('luggages.0.status', 'Diterima')
            ->assertJsonPath('luggages.0.route_name', 'PINRANG - MAKASSAR');
        $this->getJson(route('api.admin.luggages.index', ['q' => $resi]))
            ->assertOk()
            ->assertJsonCount(1, 'luggages');
        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'notes' => 'Handle with care',
        ]);

        $assignment = $this->postJson(route('api.admin.assignments.save'), [
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => now()->toDateString(),
            'jam' => '08:30',
            'unit' => 1,
            'driver_id' => $driverId,
        ])->assertCreated()->json();
        $assignmentId = (int) ($assignment['id'] ?? 0);
        $this->assertTrue($assignmentId > 0);
        $this->getJson(route('api.admin.assignments.index'))->assertOk()->assertJsonCount(1, 'assignments');

        DB::table('bookings')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => now()->toDateString(),
            'jam' => '08:00:00',
            'unit' => 1,
            'seat' => 'A1',
            'name' => 'CSV TEST',
            'phone' => '08123',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 100000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $csv = $this->get(route('api.admin.reports.bookings-csv', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]));
        $csv->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csv->headers->get('content-type'));

        $this->deleteJson(route('api.admin.charters.delete', ['id' => $charterId]))->assertOk();
        $this->deleteJson(route('api.admin.luggages.delete', ['id' => $luggageId]))->assertOk();
        $this->deleteJson(route('api.admin.assignments.delete', ['id' => $assignmentId]))->assertOk();
    }

    public function test_customer_ops_crud_works(): void
    {
        $this->actingAs(User::factory()->create());

        $bagasi = $this->postJson(route('api.admin.customer-bagasi.save'), [
            'nama' => 'PENGIRIM A',
            'no_hp' => '0812345',
            'alamat' => 'Pinrang',
            'tipe' => 'pengirim',
        ])->assertCreated()->json();
        $bagasiId = (int) ($bagasi['id'] ?? 0);
        $this->assertTrue($bagasiId > 0);

        $this->getJson(route('api.admin.customer-bagasi.index', ['q' => 'pengirim']))
            ->assertOk()
            ->assertJsonCount(1, 'customers');

        $charter = $this->postJson(route('api.admin.customer-charter.save'), [
            'nama' => 'PEMESAN B',
            'no_hp' => '089999',
            'alamat' => 'Makassar',
            'company' => 'PT MAJU',
        ])->assertCreated()->json();
        $charterId = (int) ($charter['id'] ?? 0);
        $this->assertTrue($charterId > 0);

        $this->getJson(route('api.admin.customer-charter.index', ['q' => 'PT MAJU']))
            ->assertOk()
            ->assertJsonCount(1, 'customers');

        $this->deleteJson(route('api.admin.customer-bagasi.delete', ['id' => $bagasiId]))->assertOk();
        $this->deleteJson(route('api.admin.customer-charter.delete', ['id' => $charterId]))->assertOk();
    }

    public function test_luggage_raw_mode_autofills_customer_ids(): void
    {
        $this->actingAs(User::factory()->create());

        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PINRANG - PAREPARE',
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'created_at' => now(),
        ]);
        $serviceId = DB::table('luggage_services')->insertGetId([
            'name' => 'Kilat',
            'created_at' => now(),
        ]);
        $save = $this->postJson(route('api.admin.luggages.save-raw'), [
            'sender_name' => 'Pengirim Raw',
            'sender_phone' => '0812-111',
            'sender_address' => 'Pinrang',
            'receiver_name' => 'Penerima Raw',
            'receiver_phone' => '0813-222',
            'receiver_address' => 'Parepare',
            'rute_id' => $routeId,
            'service_id' => $serviceId,
            'quantity' => 1,
            'price' => 0,
            'tanggal' => now()->toDateString(),
            'status' => 'pending',
        ])->assertCreated()->json();

        $luggageId = (int) ($save['id'] ?? 0);
        $this->assertTrue($luggageId > 0);

        $row = DB::table('luggages')->where('id', $luggageId)->first();
        $this->assertNotNull($row);
        $this->assertSame(0.0, (float) $row->price);
        $this->assertSame($routeId, (int) $row->rute_id);
        $this->assertSame($serviceId, (int) $row->layanan_id);
        $this->assertNotEmpty((string) $row->kode_resi);
        $this->assertTrue((int) $row->pengirim_id > 0);
        $this->assertTrue((int) $row->penerima_id > 0);

        $this->assertDatabaseCount('customer_bagasi', 2);
        $this->assertDatabaseCount('bagasi_logs', 1);
    }

    public function test_bulk_actions_endpoints_work(): void
    {
        $this->actingAs(User::factory()->create());

        $charterIds = [];
        for ($i = 0; $i < 2; $i++) {
            $charterIds[] = DB::table('charters')->insertGetId([
                'name' => 'C'.$i,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'price' => 1000000,
                'created_at' => now(),
            ]);
        }

        $luggageIds = [];
        for ($i = 0; $i < 2; $i++) {
            $luggageIds[] = DB::table('luggages')->insertGetId([
                'sender_name' => 'S'.$i,
                'sender_phone' => '081'.$i,
                'receiver_name' => 'R'.$i,
                'receiver_phone' => '082'.$i,
                'quantity' => 1,
                'price' => 25000,
                'status' => 'pending',
                'created_at' => now(),
            ]);
        }

        $assignmentIds = [];
        for ($i = 0; $i < 2; $i++) {
            $assignmentIds[] = DB::table('trip_assignments')->insertGetId([
                'rute' => 'RUTE '.$i,
                'tanggal' => now()->toDateString(),
                'jam' => '08:00:00',
                'unit' => 1,
                'driver_id' => null,
                'created_at' => now(),
            ]);
        }

        $this->postJson(route('api.admin.luggages.bulk-status'), [
            'ids' => $luggageIds,
            'status' => 'sent',
            'payment_status' => 'Lunas',
        ])->assertOk()->assertJsonPath('updated', 2);

        $this->assertDatabaseHas('luggages', ['id' => $luggageIds[0], 'status' => 'Dalam Perjalanan']);

        $this->postJson(route('api.admin.charters.bulk-delete'), [
            'ids' => $charterIds,
        ])->assertOk()->assertJsonPath('updated', 2);

        $this->postJson(route('api.admin.luggages.bulk-delete'), [
            'ids' => $luggageIds,
        ])->assertOk()->assertJsonPath('deleted', 2);

        $this->postJson(route('api.admin.assignments.bulk-delete'), [
            'ids' => $assignmentIds,
        ])->assertOk()->assertJsonPath('deleted', 2);
    }

    public function test_assignment_conflict_detection_and_override_work(): void
    {
        $this->actingAs(User::factory()->create());

        $driverA = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER A',
            'phone' => '0811',
            'created_at' => now(),
        ]);
        $driverB = DB::table('drivers')->insertGetId([
            'nama' => 'DRIVER B',
            'phone' => '0822',
            'created_at' => now(),
        ]);

        DB::table('trip_assignments')->insert([
            'rute' => 'RUTE BASE',
            'tanggal' => '2026-05-15',
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverA,
            'created_at' => now(),
        ]);

        $conflict = $this->postJson(route('api.admin.assignments.conflicts'), [
            'tanggal' => '2026-05-15',
            'jam' => '08:00',
            'unit' => 1,
            'driver_id' => $driverB,
        ]);
        $conflict->assertOk()
            ->assertJsonPath('has_conflict', true);
        $this->assertNotEmpty($conflict->json('conflicts'));

        $blocked = $this->postJson(route('api.admin.assignments.save'), [
            'rute' => 'RUTE CONFLICT',
            'tanggal' => '2026-05-15',
            'jam' => '08:00',
            'unit' => 1,
            'driver_id' => $driverB,
        ]);
        $blocked->assertStatus(409)
            ->assertJsonPath('error', 'assignment_conflict');

        $forced = $this->postJson(route('api.admin.assignments.save'), [
            'rute' => 'RUTE CONFLICT',
            'tanggal' => '2026-05-15',
            'jam' => '08:00',
            'unit' => 1,
            'driver_id' => $driverB,
            'allow_conflict' => true,
        ]);
        $forced->assertCreated();
        $this->assertDatabaseHas('trip_assignments', [
            'rute' => 'RUTE CONFLICT',
            'tanggal' => '2026-05-15',
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverB,
        ]);
    }

    public function test_ops_lifecycle_actions_and_revenue_csv_work(): void
    {
        $this->actingAs(User::factory()->create());

        $today = now()->toDateString();

        $charterId = DB::table('charters')->insertGetId([
            'name' => 'CHARTER LIFECYCLE',
            'start_date' => $today,
            'end_date' => $today,
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'price' => 2200000,
            'bop_status' => 'pending',
            'payment_status' => 'DP',
            'created_at' => now(),
        ]);

        $this->postJson(route('api.admin.charters.mark-bop-done', ['id' => $charterId]))
            ->assertOk();
        $this->postJson(route('api.admin.charters.mark-paid', ['id' => $charterId]))
            ->assertOk();

        $this->assertDatabaseHas('charters', [
            'id' => $charterId,
            'bop_status' => 'done',
            'payment_status' => 'Lunas',
        ]);

        $luggageId = DB::table('luggages')->insertGetId([
            'sender_name' => 'S',
            'sender_phone' => '081',
            'receiver_name' => 'R',
            'receiver_phone' => '082',
            'quantity' => 1,
            'price' => 25000,
            'status' => 'pending',
            'payment_status' => 'Belum Bayar',
            'created_at' => now(),
        ]);

        $this->postJson(route('api.admin.luggages.mark-active', ['id' => $luggageId]))->assertOk();
        $this->postJson(route('api.admin.luggages.mark-paid', ['id' => $luggageId]))->assertOk();
        $this->postJson(route('api.admin.luggages.mark-done', ['id' => $luggageId]))->assertOk();
        $this->postJson(route('api.admin.luggages.tracking.add', ['id' => $luggageId]), [
            'status' => 'sent',
            'notes' => 'On transit',
        ])->assertOk();
        $this->postJson(route('api.admin.luggages.mark-canceled', ['id' => $luggageId]))->assertOk();

        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'status' => 'canceled',
            'payment_status' => 'Lunas',
        ]);
        $this->assertDatabaseCount('bagasi_logs', 5);

        $tracking = $this->getJson(route('api.admin.luggages.tracking', ['id' => $luggageId]))
            ->assertOk()
            ->json();
        $this->assertNotEmpty($tracking['luggage']['kode_resi'] ?? null);
        $this->assertTrue(count($tracking['logs'] ?? []) >= 5);

        DB::table('bookings')->insert([
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'seat' => 'A3',
            'name' => 'REGULER CSV',
            'phone' => '0899',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 120000,
            'discount' => 10000,
            'created_at' => now(),
        ]);

        DB::table('luggages')->insert([
            'sender_name' => 'CSV S',
            'sender_phone' => '08111',
            'receiver_name' => 'CSV R',
            'receiver_phone' => '08222',
            'quantity' => 1,
            'price' => 50000,
            'status' => 'done',
            'payment_status' => 'Lunas',
            'created_at' => now(),
        ]);

        $csvReguler = $this->get(route('api.admin.reports.revenue-csv', [
            'from' => $today,
            'to' => $today,
            'type' => 'reguler',
        ]));
        $csvReguler->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvReguler->headers->get('content-type'));

        $csvBagasi = $this->get(route('api.admin.reports.revenue-csv', [
            'from' => $today,
            'to' => $today,
            'type' => 'bagasi',
        ]));
        $csvBagasi->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvBagasi->headers->get('content-type'));

        $csvCharter = $this->get(route('api.admin.reports.revenue-csv', [
            'from' => $today,
            'to' => $today,
            'type' => 'charter',
        ]));
        $csvCharter->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvCharter->headers->get('content-type'));
    }
}
