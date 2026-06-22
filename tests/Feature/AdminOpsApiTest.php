<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use App\Support\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOpsApiTest extends TestCase
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

    public function test_routes_crud_works(): void
    {
        $this->actingAsSuperAdmin();

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
        $this->actingAsSuperAdmin();
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

    public function test_schedules_index_prefers_route_id_over_stale_route_name(): void
    {
        $this->actingAsSuperAdmin();

        DB::table('routes')->insert([
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'name' => 'PAREPARE - MAKASSAR',
            'origin' => 'PAREPARE',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'route_id' => $routeId,
            'rute' => 'PAREPARE - MAKASSAR',
            'dow' => 1,
            'jam' => '08:00:00',
            'units' => 1,
            'unit_label' => 'Unit 1',
            'created_at' => now(),
        ]);

        $this->getJson(route('api.admin.schedules.index', [
            'route_id' => $routeId,
            'rute' => 'PINRANG - MAKASSAR',
        ]))->assertOk()
            ->assertJsonCount(1, 'schedules')
            ->assertJsonPath('schedules.0.route_id', $routeId)
            ->assertJsonPath('schedules.0.rute', 'PAREPARE - MAKASSAR');
    }

    public function test_driver_and_luggage_service_crud_works(): void
    {
        $this->actingAsSuperAdmin();

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
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Tenant Test CRUD',
            'slug' => 'tenant-test-crud',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $authUser = User::factory()->create([
            'name' => 'Root Admin',
            'email' => 'root.admin@example.com',
            'is_super_admin' => true,
        ]);
        $this->actingAs($authUser)->withSession(['active_tenant_id' => $tenantId]);

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

        $this->postJson(route('api.admin.users.save'), [
            'name' => 'Ops Cadangan',
            'email' => 'ops.cadangan@example.com',
            'password' => 'password123',
        ])->assertCreated();

        $this->deleteJson(route('api.admin.users.delete', ['id' => $authUser->id]))
            ->assertStatus(409);

        $this->deleteJson(route('api.admin.users.delete', ['id' => $userId]))->assertOk();

        $this->deleteJson(route('api.admin.units.delete', ['id' => $unitId]))->assertOk();
    }

    public function test_armada_category_and_armada_create_use_default_tenant(): void
    {
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $unitCreate = $this->postJson(route('api.admin.units.save'), [
            'nopol' => 'TEMPLATE BIGBUS 42',
            'category' => 'Bigbus',
            'kapasitas' => 42,
            'status' => 'Aktif',
        ])->assertCreated()->json();

        $unitId = (int) ($unitCreate['id'] ?? 0);
        $this->assertDatabaseHas('units', [
            'id' => $unitId,
            'tenant_id' => $tenantId,
            'category' => 'Bigbus',
        ]);

        $categories = $this->getJson(route('api.admin.armada-categories.index'))
            ->assertOk()
            ->json('categories');

        $this->assertContains('Bigbus', $categories);

        $armadaCreate = $this->postJson(route('api.admin.armadas.save'), [
            'merk' => 'Hino',
            'tahun' => 2024,
            'warna' => 'Putih',
            'nopol' => 'DD 5500 QB',
            'nomor_rangka' => 'QBUS-RANGKA-5500',
            'kategori' => 'Bigbus',
            'ac_type' => 'AC',
            'target_revenue' => 25000000,
            'fixed_cost' => 5000000,
        ])->assertCreated()->json();

        $armadaId = (int) ($armadaCreate['id'] ?? 0);
        $this->assertDatabaseHas('armadas', [
            'id' => $armadaId,
            'tenant_id' => $tenantId,
            'nopol' => 'DD 5500 QB',
        ]);

        $armadas = $this->getJson(route('api.admin.armadas.index'))
            ->assertOk()
            ->json('armadas');

        $this->assertNotNull(collect($armadas)->firstWhere('id', $armadaId));
    }

    public function test_legacy_master_data_is_scoped_by_tenant(): void
    {
        AccessControl::syncDefaults();
        $tenantId = $this->defaultTenantId();
        $tenantTwoId = DB::table('tenants')->insertGetId([
            'name' => 'Tenant Dua',
            'slug' => 'tenant-dua',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $otherRouteId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantTwoId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL TENANT SATU',
            'code' => 'T1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherPoolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantTwoId,
            'name' => 'POOL TENANT DUA',
            'code' => 'T2',
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
                'pool_id' => $otherPoolId,
                'route_id' => $otherRouteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('schedules')->insert([
            [
                'tenant_id' => $tenantId,
                'route_id' => $routeId,
                'rute' => 'PINRANG - MAKASSAR',
                'dow' => 1,
                'jam' => '08:00:00',
                'units' => 1,
                'unit_label' => 'Tenant Satu',
                'created_at' => now(),
            ],
            [
                'tenant_id' => $tenantTwoId,
                'route_id' => $otherRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'dow' => 1,
                'jam' => '09:00:00',
                'units' => 1,
                'unit_label' => 'Tenant Dua',
                'created_at' => now(),
            ],
        ]);
        DB::table('segments')->insert([
            [
                'tenant_id' => $tenantId,
                'route_id' => $routeId,
                'rute' => 'PINRANG - MAKASSAR',
                'origin' => 'PINRANG',
                'destination' => 'MAROS',
                'harga' => 75000,
                'created_at' => now(),
            ],
            [
                'tenant_id' => $tenantTwoId,
                'route_id' => $otherRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'origin' => 'PINRANG',
                'destination' => 'TENANT DUA',
                'harga' => 99000,
                'created_at' => now(),
            ],
        ]);
        DB::table('customers')->insert([
            [
                'tenant_id' => $tenantId,
                'pool_id' => $poolId,
                'name' => 'CUSTOMER TENANT SATU',
                'phone' => '081211111111',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
            [
                'tenant_id' => $tenantTwoId,
                'pool_id' => $otherPoolId,
                'name' => 'CUSTOMER TENANT DUA',
                'phone' => '081222222222',
                'pickup_point' => 'Pinrang',
                'created_at' => now(),
            ],
        ]);

        $operator = User::factory()->create(['is_super_admin' => false, 'tenant_id' => $tenantId]);
        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => DB::table('roles')->where('slug', 'admin-pool')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($operator);

        $this->getJson(route('api.admin.schedules.index'))
            ->assertOk()
            ->assertJsonCount(1, 'schedules')
            ->assertJsonPath('schedules.0.unit_label', 'Tenant Satu');

        $this->getJson(route('api.admin.segments.index'))
            ->assertOk()
            ->assertJsonCount(1, 'segments')
            ->assertJsonPath('segments.0.destination', 'MAROS');

        $this->getJson(route('api.admin.customers.index', ['q' => 'customer tenant']))
            ->assertOk()
            ->assertJsonCount(1, 'customers')
            ->assertJsonPath('customers.0.name', 'CUSTOMER TENANT SATU');
    }

    public function test_segments_customers_and_reports_endpoints_work(): void
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

        $segmentCreate = $this->postJson(route('api.admin.segments.save'), [
            'route_id' => $routeId,
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'jam_pickups' => ['07:30', '09:15'],
            'harga' => 75000,
        ])->assertCreated()->json();

        $segmentId = (int) ($segmentCreate['id'] ?? 0);
        $this->assertTrue($segmentId > 0);
        $segments = $this->getJson(route('api.admin.segments.index'))
            ->assertOk()
            ->json('segments');
        $segmentRow = collect($segments)->firstWhere('id', $segmentId);
        $this->assertTrue(is_array($segmentRow));
        $this->assertSame('PINRANG - PAREPARE', (string) ($segmentRow['rute'] ?? ''));
        $this->assertSame('07:30', (string) ($segmentRow['jam'] ?? ''));
        $this->assertSame(['07:30', '09:15'], $segmentRow['jam_pickups'] ?? []);
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
        ActivityLog::write(
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

    public function test_reports_include_active_unpaid_booking_pending_charter_and_luggage(): void
    {
        $this->actingAsSuperAdmin();

        $today = now()->toDateString();

        DB::table('bookings')->insert([
            [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $today,
                'jam' => '08:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'BOOKING LUNAS',
                'phone' => '08110001',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 120000,
                'discount' => 10000,
                'created_at' => now(),
            ],
            [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $today,
                'jam' => '08:00:00',
                'unit' => 1,
                'seat' => 'A2',
                'name' => 'BOOKING BELUM LUNAS',
                'phone' => '08110002',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 100000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $today,
                'jam' => '08:00:00',
                'unit' => 1,
                'seat' => 'A3',
                'name' => 'BOOKING CANCEL',
                'phone' => '08110003',
                'pickup_point' => 'Terminal',
                'pembayaran' => 'Lunas',
                'status' => 'canceled',
                'price' => 90000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $charterPayload = [
            'name' => 'CARTER AKTIF',
            'phone' => '08220001',
            'start_date' => $today,
            'end_date' => $today,
            'departure_time' => '09:00:00',
            'pickup_point' => 'PINRANG',
            'drop_point' => 'MAKASSAR',
            'price' => 350000,
            'bop_status' => 'pending',
            'payment_status' => 'Belum Bayar',
            'created_at' => now(),
        ];
        if (Schema::hasColumn('charters', 'status')) {
            $charterPayload['status'] = 'active';
        }
        DB::table('charters')->insert($charterPayload);

        $canceledCharterPayload = array_merge($charterPayload, [
            'name' => 'CARTER CANCEL',
            'phone' => '08220002',
            'price' => 500000,
            'payment_status' => 'Canceled',
        ]);
        if (Schema::hasColumn('charters', 'status')) {
            $canceledCharterPayload['status'] = 'canceled';
        }
        DB::table('charters')->insert($canceledCharterPayload);

        DB::table('luggages')->insert([
            [
                'sender_name' => 'BAGASI AKTIF',
                'sender_phone' => '08330001',
                'receiver_name' => 'PENERIMA',
                'receiver_phone' => '08330002',
                'quantity' => 1,
                'price' => 50000,
                'status' => 'Barang sudah diterima',
                'payment_status' => 'Belum Bayar',
                'created_at' => now(),
            ],
            [
                'sender_name' => 'BAGASI CANCEL',
                'sender_phone' => '08330003',
                'receiver_name' => 'PENERIMA',
                'receiver_phone' => '08330004',
                'quantity' => 1,
                'price' => 75000,
                'status' => 'canceled',
                'payment_status' => 'Lunas',
                'created_at' => now(),
            ],
        ]);

        $bookingReport = $this->getJson(route('api.admin.reports.summary', [
            'from' => $today,
            'to' => $today,
            'type' => 'booking',
        ]))->assertOk()->json();
        $this->assertSame(2, $bookingReport['summary']['total_rows'] ?? null);
        $this->assertEquals(210000.0, $bookingReport['summary']['revenue_total'] ?? 0);

        $charterReport = $this->getJson(route('api.admin.reports.summary', [
            'from' => $today,
            'to' => $today,
            'type' => 'charter',
        ]))->assertOk()->json();
        $this->assertSame(1, $charterReport['summary']['total_rows'] ?? null);
        $this->assertEquals(350000.0, $charterReport['summary']['revenue_total'] ?? 0);

        $luggageReport = $this->getJson(route('api.admin.reports.summary', [
            'from' => $today,
            'to' => $today,
            'type' => 'bagasi',
        ]))->assertOk()->json();
        $this->assertSame(1, $luggageReport['summary']['total_rows'] ?? null);
        $this->assertEquals(50000.0, $luggageReport['summary']['revenue_total'] ?? 0);
    }

    public function test_driver_and_armada_revenue_include_luggage_revenue_in_totals(): void
    {
        $this->actingAsSuperAdmin();

        $tenantId = $this->defaultTenantId();
        $today = now()->toDateString();
        $armadaNopol = 'DD 1234 UX';

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL PINRANG',
            'code' => 'PIN',
            'status' => 'active',
            'target_revenue' => 1000000,
            'fixed_cost' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $armadaPayload = [
            'tenant_id' => $tenantId,
            'merk' => 'ISUZU',
            'tahun' => 2024,
            'warna' => 'PUTIH',
            'nopol' => $armadaNopol,
            'kategori' => 'MICRO BUS',
            'ac_type' => 'AC',
            'target_bulanan' => 1000000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('armadas', 'pool_id')) {
            $armadaPayload['pool_id'] = $poolId;
        }

        if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
            $armadaPayload['nama_kendaraan'] = 'ARMADA BAGASI';
        }

        $armadaId = DB::table('armadas')->insertGetId($armadaPayload);

        $driverPayload = [
            'tenant_id' => $tenantId,
            'nama' => 'DRIVER BAGASI',
            'phone' => '08123456789',
            'target_revenue_bulanan' => 800000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('drivers', 'pool_id')) {
            $driverPayload['pool_id'] = $poolId;
        }

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
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('trip_assignments', 'pool_id')) {
            $assignmentPayload['pool_id'] = $poolId;
        }

        if (Schema::hasColumn('trip_assignments', 'route_id')) {
            $assignmentPayload['route_id'] = $routeId;
        }

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
            'tenant_id' => $tenantId,
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
            'tenant_id' => $tenantId,
            'sender_name' => 'PENGIRIM BAGASI',
            'sender_phone' => '0812000002',
            'receiver_name' => 'PENERIMA BAGASI',
            'receiver_phone' => '0812000003',
            'quantity' => 1,
            'price' => 50000,
            'status' => 'active',
            'payment_status' => 'Lunas',
            'trip_assignment_id' => $assignmentId,
            ...(Schema::hasColumn('luggages', 'pool_id') ? ['pool_id' => $poolId] : []),
            ...(Schema::hasColumn('luggages', 'rute_id') ? ['rute_id' => $routeId] : []),
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
            'tenant_id' => $tenantId,
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
            ...(Schema::hasColumn('luggages', 'pool_id') ? ['pool_id' => $poolId] : []),
            ...(Schema::hasColumn('luggages', 'rute_id') ? ['rute_id' => $routeId] : []),
            'created_at' => now(),
        ]);

        $charterPayload = [
            'tenant_id' => $tenantId,
            'name' => 'CHARTER BAGASI',
            'phone' => '0812000004',
            'start_date' => $today,
            'end_date' => $today,
            'departure_time' => '09:00:00',
            'pickup_point' => 'PINRANG',
            'drop_point' => 'MAKASSAR',
            'driver_name' => 'DRIVER BAGASI',
            'price' => 300000,
            'bop_price' => 40000,
            'payment_status' => 'Lunas',
            'bop_status' => 'done',
            'created_at' => now(),
        ];

        if (Schema::hasColumn('charters', 'pool_id')) {
            $charterPayload['pool_id'] = $poolId;
        }

        if (Schema::hasColumn('charters', 'armada_id')) {
            $charterPayload['armada_id'] = $armadaId;
        }

        if (Schema::hasColumn('charters', 'armada_nopol')) {
            $charterPayload['armada_nopol'] = $armadaNopol;
        }

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

    public function test_armada_detail_includes_manifest_charter_and_luggage_dates_and_financials(): void
    {
        $this->actingAsSuperAdmin();
        Cache::flush();

        $tenantId = $this->defaultTenantId();
        $today = now()->toDateString();
        $period = now()->format('Y-m');
        $armadaNopol = 'DD 7788 ZZ';

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
            $armadaPayload['nama_kendaraan'] = 'ARMADA DETAIL';
        }

        $armadaId = DB::table('armadas')->insertGetId($armadaPayload);

        $driverPayload = [
            'nama' => 'DRIVER DETAIL ARMADA',
            'phone' => '08129990001',
            'created_at' => now(),
        ];

        if (Schema::hasColumn('drivers', 'armada_id')) {
            $driverPayload['armada_id'] = $armadaId;
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
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'seat' => 'A1',
            'name' => 'PENUMPANG DETAIL',
            'phone' => '08129990002',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 100000,
            'discount' => 5000,
            'created_at' => now(),
        ]);

        DB::table('charters')->insert([
            'tenant_id' => $tenantId,
            'name' => 'CHARTER DETAIL',
            'phone' => '08129990003',
            'start_date' => $today,
            'end_date' => $today,
            'departure_time' => '09:00:00',
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'price' => 300000,
            'bop_price' => 40000,
            'payment_status' => 'Lunas',
            'bop_status' => 'done',
            'created_at' => now(),
            ...(Schema::hasColumn('charters', 'armada_id') ? ['armada_id' => $armadaId] : []),
            ...(Schema::hasColumn('charters', 'armada_nopol') ? ['armada_nopol' => $armadaNopol] : []),
            ...(Schema::hasColumn('charters', 'status') ? ['status' => 'done'] : []),
        ]);

        DB::table('luggages')->insert([
            'sender_name' => 'PENGIRIM DETAIL',
            'sender_phone' => '08129990004',
            'receiver_name' => 'PENERIMA DETAIL',
            'receiver_phone' => '08129990005',
            'quantity' => 1,
            'price' => 50000,
            'status' => 'active',
            'payment_status' => 'Lunas',
            'tanggal' => $today,
            'trip_assignment_id' => $assignmentId,
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.admin.armadas.show', [
            'id' => $armadaId,
            'period' => $period,
        ]))->assertOk();

        $armada = $response->json('armada');
        $this->assertNotNull($armada);
        $monthly = $armada['monthly']['summary'] ?? [];
        $this->assertSame($today, $armada['monthly']['bookings'][0]['departure_date'] ?? null);
        $this->assertSame(95000.0, (float) ($armada['monthly']['bookings'][0]['revenue'] ?? -1));
        $this->assertSame(0.0, (float) ($armada['monthly']['bookings'][0]['bop'] ?? -1));
        $this->assertSame($today, $armada['monthly']['charters'][0]['departure_date'] ?? null);
        $this->assertSame(300000.0, (float) ($armada['monthly']['charters'][0]['revenue'] ?? -1));
        $this->assertSame(40000.0, (float) ($armada['monthly']['charters'][0]['bop'] ?? -1));
        $this->assertSame($today, $armada['monthly']['bagasi'][0]['departure_date'] ?? null);
        $this->assertSame(50000.0, (float) ($armada['monthly']['bagasi'][0]['revenue'] ?? -1));
        $this->assertSame(0.0, (float) ($armada['monthly']['bagasi'][0]['bop'] ?? -1));
        $this->assertSame(1, (int) ($monthly['departure_count'] ?? 0));
        $this->assertSame(1, (int) ($monthly['charter_count'] ?? 0));
        $this->assertSame(1, (int) ($monthly['luggage_count'] ?? 0));
    }

    public function test_driver_and_armada_luggage_revenue_refresh_immediately_after_mapping_existing_luggage(): void
    {
        $this->actingAsSuperAdmin();
        Cache::flush();

        $tenantId = $this->defaultTenantId();
        $today = now()->toDateString();
        $armadaNopol = 'DD 4321 UX';

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL PINRANG CACHE',
            'code' => 'PIN-CACHE',
            'status' => 'active',
            'target_revenue' => 1000000,
            'fixed_cost' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $armadaPayload = [
            'tenant_id' => $tenantId,
            'merk' => 'ISUZU',
            'tahun' => 2024,
            'warna' => 'PUTIH',
            'nopol' => $armadaNopol,
            'kategori' => 'MICRO BUS',
            'ac_type' => 'AC',
            'target_bulanan' => 1000000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('armadas', 'pool_id')) {
            $armadaPayload['pool_id'] = $poolId;
        }

        if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
            $armadaPayload['nama_kendaraan'] = 'ARMADA CACHE BAGASI';
        }

        $armadaId = DB::table('armadas')->insertGetId($armadaPayload);

        $driverPayload = [
            'tenant_id' => $tenantId,
            'nama' => 'DRIVER CACHE BAGASI',
            'phone' => '08123456780',
            'target_revenue_bulanan' => 800000,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('drivers', 'pool_id')) {
            $driverPayload['pool_id'] = $poolId;
        }

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
            'tenant_id' => $tenantId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => $today,
            'jam' => '08:00:00',
            'unit' => 1,
            'driver_id' => $driverId,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('trip_assignments', 'pool_id')) {
            $assignmentPayload['pool_id'] = $poolId;
        }

        if (Schema::hasColumn('trip_assignments', 'route_id')) {
            $assignmentPayload['route_id'] = $routeId;
        }

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
            'tenant_id' => $tenantId,
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
            ...(Schema::hasColumn('luggages', 'pool_id') ? ['pool_id' => $poolId] : []),
            ...(Schema::hasColumn('luggages', 'rute_id') ? ['rute_id' => $routeId] : []),
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
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $unitId = DB::table('units')->insertGetId([
            'tenant_id' => $tenantId,
            'nopol' => 'DD 8899 ZZ',
            'merek' => 'Isuzu',
            'type' => 'Elf',
            'kapasitas' => 12,
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        $driverId = DB::table('drivers')->insertGetId([
            'tenant_id' => $tenantId,
            'nama' => 'DRIVER BARU',
            'phone' => '0812',
            'unit_id' => $unitId,
            'created_at' => now(),
        ]);

        $serviceId = DB::table('luggage_services')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Dokumen',
            'created_at' => now(),
        ]);
        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
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

    public function test_charter_save_survives_legacy_customer_phone_conflicts_in_other_tenant(): void
    {
        $this->actingAsSuperAdmin();

        $tenantId = $this->defaultTenantId();
        $otherTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Tenant Konflik',
            'slug' => 'tenant-konflik',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL CHARTER',
            'code' => 'CHT',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => DB::table('routes')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'PINRANG - MAKASSAR',
                'origin' => 'PINRANG',
                'destination' => 'MAKASSAR',
                'created_at' => now(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conflictPoolId = DB::table('pools')->insertGetId([
            'tenant_id' => $otherTenantId,
            'name' => 'POOL KONFLIK',
            'code' => 'CFK',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('customer_charter')->insert([
            'tenant_id' => $otherTenantId,
            'pool_id' => $conflictPoolId,
            'nama' => 'CHARTER KONFLIK',
            'no_hp' => '08128888000',
            'alamat' => 'Tenant Konflik',
            'company' => 'PT KONFLIK',
            'created_at' => now(),
        ]);

        if (Schema::hasTable('customer_charter')) {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS tmp_customer_charter_no_hp_unique ON customer_charter (no_hp)');
        }

        $charter = $this->postJson(route('api.admin.charters.save'), [
            'pool_id' => $poolId,
            'name' => 'ROMBONGAN KONFLIK',
            'company_name' => 'PT KONFLIK',
            'phone' => '08128888000',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'departure_time' => '09:00',
            'pickup_point' => 'Pinrang',
            'drop_point' => 'Makassar',
            'price' => 2500000,
            'layanan' => 'Regular',
            'bop_price' => 300000,
            'payment_status' => 'DP',
        ])->assertCreated()->json();

        $charterId = (int) ($charter['id'] ?? 0);
        $this->assertTrue($charterId > 0);

        $this->postJson(route('api.admin.charters.save'), [
            'id' => $charterId,
            'pool_id' => $poolId,
            'name' => 'ROMBONGAN KONFLIK UPDATE',
            'company_name' => 'PT KONFLIK UPDATE',
            'phone' => '08128888000',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'departure_time' => '10:00',
            'pickup_point' => 'Terminal Pinrang',
            'drop_point' => 'Makassar',
            'price' => 2750000,
            'layanan' => 'Regular',
            'bop_price' => 320000,
            'payment_status' => 'DP',
        ])->assertOk();

        $this->assertDatabaseHas('charters', [
            'id' => $charterId,
            'name' => 'ROMBONGAN KONFLIK UPDATE',
            'company_name' => 'PT KONFLIK UPDATE',
        ]);
    }

    public function test_admin_charter_rejects_pool_that_conflicts_with_mapped_route(): void
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
        $pinrangPoolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL PINRANG',
            'code' => 'PNR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $makassarPoolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL MAKASSAR',
            'code' => 'MKS',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            'pool_id' => $pinrangPoolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson(route('api.admin.charters.save'), [
            'pool_id' => $makassarPoolId,
            'name' => 'ROMBONGAN SALAH POOL',
            'company_name' => 'PT TEST',
            'phone' => '08129999',
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-05',
            'departure_time' => '09:00',
            'pickup_point' => 'PINRANG',
            'drop_point' => 'MAKASSAR',
            'driver_name' => 'DRIVER TEST',
            'price' => 3000000,
            'layanan' => 'VIP',
            'bop_price' => 500000,
            'bop_status' => 'pending',
            'payment_status' => 'DP',
        ])->assertStatus(422)
            ->assertJsonPath('error', 'Rute yang dipilih sudah dimapping ke pool lain.');

        $this->assertDatabaseMissing('charters', [
            'name' => 'ROMBONGAN SALAH POOL',
        ]);
    }

    public function test_customer_ops_crud_works(): void
    {
        $this->actingAsSuperAdmin();

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
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - PAREPARE',
            'origin' => 'PINRANG',
            'destination' => 'PAREPARE',
            'created_at' => now(),
        ]);
        $serviceId = DB::table('luggage_services')->insertGetId([
            'tenant_id' => $tenantId,
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
        $this->actingAsSuperAdmin();

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
        $this->actingAsSuperAdmin();

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
        $this->actingAsSuperAdmin();
        $tenantId = $this->defaultTenantId();

        $today = now()->toDateString();

        $charterId = DB::table('charters')->insertGetId([
            'tenant_id' => $tenantId,
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
            'tenant_id' => $tenantId,
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
            'tenant_id' => $tenantId,
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
            'tenant_id' => $tenantId,
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

    public function test_pools_index_exposes_regions_and_export_downloads_xlsx(): void
    {
        $this->actingAsSuperAdmin();

        $tenantId = $this->defaultTenantId();
        $routeId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'MAKASSAR TIMUR - MAKASSAR',
            'origin' => 'MAKASSAR TIMUR',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);

        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL MAKASSAR TIMUR',
            'code' => 'MKS-TMR',
            'phone' => '0411000001',
            'address' => 'Jl. Cabang No. 1',
            'target_revenue' => 150000000,
            'fixed_cost' => 25000000,
            'status' => 'active',
            'notes' => 'Cabang timur',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(route('api.admin.pools.index'));
        $response->assertOk();
        $response = $response->json();

        $this->assertSame('Makassar Timur', $response['pools'][0]['region'] ?? null);
        $this->assertContains('Makassar Timur', $response['regions'] ?? []);

        $export = $this->get(route('api.admin.pools.export'));
        $export->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $export->headers->get('content-type'),
        );
    }
}
