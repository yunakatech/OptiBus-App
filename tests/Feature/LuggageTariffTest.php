<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LuggageTariffTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_and_segment_keep_the_rate_matrix_in_sync(): void
    {
        [$tenantId, $poolId, $routeId] = $this->tenantRouteContext();

        $firstSegmentId = $this->createSegment($tenantId, $routeId, 'PINRANG - PAREPARE');
        $category = $this->postJson(route('api.admin.luggage-services.save'), [
            'name' => 'Koper',
            'description' => 'Bagasi penumpang',
        ])->assertCreated()->json();
        $serviceId = (int) $category['id'];

        $this->assertDatabaseHas('luggage_segment_rates', [
            'tenant_id' => $tenantId,
            'segment_id' => $firstSegmentId,
            'service_id' => $serviceId,
            'unit_price' => 0,
            'is_active' => true,
            'configured_at' => null,
        ]);
        $this->getJson(route('api.master.luggage-options', ['route_id' => $routeId]))
            ->assertOk()
            ->assertJsonPath('segments.0.categories.0.service_id', $serviceId)
            ->assertJsonPath('segments.0.categories.0.configured', false);
        $this->putJson(route('api.admin.luggage-rates.save', ['segmentId' => $firstSegmentId]), [
            'route_id' => $routeId,
            'rates' => [['service_id' => $serviceId, 'unit_price' => 0]],
        ])->assertOk();
        $this->assertNotNull(
            DB::table('luggage_segment_rates')
                ->where('segment_id', $firstSegmentId)
                ->where('service_id', $serviceId)
                ->value('configured_at'),
        );

        $secondSegment = $this->postJson(route('api.admin.segments.save'), [
            'route_id' => $routeId,
            'rute' => 'PAREPARE - MAKASSAR',
            'origin' => 'PAREPARE',
            'destination' => 'MAKASSAR',
            'jam_pickups' => ['10:00'],
            'harga' => 100000,
        ])->assertCreated()->json();

        $this->assertDatabaseHas('luggage_segment_rates', [
            'tenant_id' => $tenantId,
            'segment_id' => (int) $secondSegment['id'],
            'service_id' => $serviceId,
            'unit_price' => 0,
        ]);
        $this->assertNull(DB::table('luggage_services')->where('id', $serviceId)->value('pool_id'));
        $this->assertSame($poolId, (int) session('active_pool_id'));
    }

    public function test_transaction_uses_segment_rate_and_keeps_a_price_snapshot(): void
    {
        [$tenantId, $poolId, $routeId] = $this->tenantRouteContext();
        $segmentId = $this->createSegment($tenantId, $routeId, 'PINRANG - MAKASSAR');
        $serviceId = $this->createCategory($tenantId, 'Kardus');
        $rateId = $this->createRate($tenantId, $segmentId, $serviceId, 25000);

        $response = $this->postJson(route('api.admin.luggages.save'), $this->luggagePayload(
            $poolId,
            $routeId,
            $segmentId,
            $serviceId,
            3,
        ))->assertCreated()->json();

        $luggageId = (int) $response['id'];
        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'tenant_id' => $tenantId,
            'segment_id' => $segmentId,
            'luggage_segment_rate_id' => $rateId,
            'unit_price' => 25000,
            'price' => 75000,
            'pricing_source' => 'rate',
        ]);

        DB::table('luggage_segment_rates')->where('id', $rateId)->update(['unit_price' => 40000]);
        $this->postJson(route('api.admin.luggages.save'), array_merge(
            $this->luggagePayload($poolId, $routeId, $segmentId, $serviceId, 3),
            ['id' => $luggageId, 'sender_name' => 'Pengirim Diperbarui'],
        ))->assertOk();
        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'unit_price' => 25000,
            'price' => 75000,
            'sender_name' => 'PENGIRIM DIPERBARUI',
        ]);
    }

    public function test_override_requires_a_reason_and_is_audited(): void
    {
        [$tenantId, $poolId, $routeId] = $this->tenantRouteContext();
        $segmentId = $this->createSegment($tenantId, $routeId, 'PINRANG - MAKASSAR');
        $serviceId = $this->createCategory($tenantId, 'Sepeda');
        $this->createRate($tenantId, $segmentId, $serviceId, 50000);
        $payload = $this->luggagePayload($poolId, $routeId, $segmentId, $serviceId, 2);

        $this->postJson(route('api.admin.luggages.save'), $payload + [
            'unit_price_override' => 35000,
        ])->assertUnprocessable()->assertJsonPath('error', 'Alasan perubahan tarif wajib diisi.');

        $response = $this->postJson(route('api.admin.luggages.save'), $payload + [
            'unit_price_override' => 35000,
            'price_override_reason' => 'Diskon penanganan komplain',
        ])->assertCreated()->json();

        $this->assertDatabaseHas('luggages', [
            'id' => (int) $response['id'],
            'unit_price' => 35000,
            'price' => 70000,
            'pricing_source' => 'override',
            'price_override_reason' => 'Diskon penanganan komplain',
        ]);
        $this->assertDatabaseHas('bagasi_logs', [
            'tenant_id' => $tenantId,
            'kode_resi' => DB::table('luggages')->where('id', $response['id'])->value('kode_resi'),
        ]);
        $this->assertDatabaseHas('activity_logs', ['tenant_id' => $tenantId, 'tag' => 'BAGASI']);
    }

    public function test_operator_without_override_permission_cannot_replace_the_rate(): void
    {
        [$tenantId, $poolId, $routeId] = $this->tenantRouteContext();
        $segmentId = $this->createSegment($tenantId, $routeId, 'PINRANG - MAKASSAR');
        $serviceId = $this->createCategory($tenantId, 'Elektronik');
        $this->createRate($tenantId, $segmentId, $serviceId, 45000);
        AccessControl::syncDefaults();
        $operator = User::factory()->create(['tenant_id' => $tenantId, 'is_super_admin' => false]);
        $roleId = (int) DB::table('roles')->where('slug', 'operator-bagasi')->value('id');
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_user')->insert([
            'user_id' => $operator->id,
            'pool_id' => $poolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($operator)
            ->withSession(['active_tenant_id' => $tenantId, 'active_pool_id' => $poolId])
            ->postJson(route('api.admin.luggages.save'), $this->luggagePayload(
                $poolId,
                $routeId,
                $segmentId,
                $serviceId,
                1,
            ) + [
                'unit_price_override' => 10000,
                'price_override_reason' => 'Ubah manual',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error', 'Anda tidak memiliki izin untuk mengubah tarif bagasi.');

        $this->assertDatabaseCount('luggages', 0);
    }

    public function test_pool_admin_cannot_read_or_update_rates_for_another_pool_route(): void
    {
        [$tenantId, $poolA] = $this->tenantRouteContext();
        $poolB = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL TARIF B',
            'code' => 'TRB',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeB = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PAREPARE - MAKASSAR KHUSUS',
            'origin' => 'PAREPARE',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        DB::table('pool_route')->insert([
            'pool_id' => $poolB,
            'route_id' => $routeB,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $segmentB = $this->createSegment($tenantId, $routeB, 'PAREPARE - MAKASSAR');
        $serviceId = $this->createCategory($tenantId, 'Barang Pool B');
        $this->createRate($tenantId, $segmentB, $serviceId, 20000);
        AccessControl::syncDefaults();
        $admin = User::factory()->create(['tenant_id' => $tenantId, 'is_super_admin' => false]);
        $roleId = (int) DB::table('roles')->where('slug', 'admin-pool')->value('id');
        DB::table('user_role')->insert([
            'user_id' => $admin->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pool_user')->insert([
            'user_id' => $admin->id,
            'pool_id' => $poolA,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($admin)->withSession([
            'active_tenant_id' => $tenantId,
            'active_pool_id' => $poolA,
        ]);

        $this->getJson(route('api.master.luggage-options', ['route_id' => $routeB]))->assertNotFound();
        $this->getJson(route('api.admin.luggage-rates.index', [
            'route_id' => $routeB,
            'segment_id' => $segmentB,
        ]))->assertUnprocessable();
        $this->putJson(route('api.admin.luggage-rates.save', ['segmentId' => $segmentB]), [
            'route_id' => $routeB,
            'rates' => [['service_id' => $serviceId, 'unit_price' => 99999]],
        ])->assertUnprocessable();
        $this->assertDatabaseHas('luggage_segment_rates', [
            'segment_id' => $segmentB,
            'service_id' => $serviceId,
            'unit_price' => 20000,
        ]);
    }

    /** @return array{int, int, int} */
    private function tenantRouteContext(): array
    {
        $tenantId = (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'POOL TARIF',
            'code' => 'TRF',
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
        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withSession(['active_tenant_id' => $tenantId, 'active_pool_id' => $poolId]);

        return [$tenantId, $poolId, $routeId];
    }

    private function createSegment(int $tenantId, int $routeId, string $name): int
    {
        return DB::table('segments')->insertGetId([
            'tenant_id' => $tenantId,
            'route_id' => $routeId,
            'rute' => $name,
            'origin' => str($name)->before(' - ')->toString(),
            'destination' => str($name)->after(' - ')->toString(),
            'jam' => '09:00:00',
            'jam_pickups' => json_encode(['09:00']),
            'harga' => 100000,
            'created_at' => now(),
        ]);
    }

    private function createCategory(int $tenantId, string $name): int
    {
        return DB::table('luggage_services')->insertGetId([
            'tenant_id' => $tenantId,
            'pool_id' => null,
            'name' => $name,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRate(int $tenantId, int $segmentId, int $serviceId, float $unitPrice): int
    {
        return DB::table('luggage_segment_rates')->insertGetId([
            'tenant_id' => $tenantId,
            'segment_id' => $segmentId,
            'service_id' => $serviceId,
            'unit_price' => $unitPrice,
            'is_active' => true,
            'configured_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function luggagePayload(
        int $poolId,
        int $routeId,
        int $segmentId,
        int $serviceId,
        int $quantity,
    ): array {
        return [
            'pool_id' => $poolId,
            'sender_name' => 'Pengirim Tarif',
            'sender_phone' => '081211111111',
            'receiver_name' => 'Penerima Tarif',
            'receiver_phone' => '082211111111',
            'rute_id' => $routeId,
            'segment_id' => $segmentId,
            'service_id' => $serviceId,
            'quantity' => $quantity,
            'payment_status' => 'Belum Bayar',
        ];
    }
}
