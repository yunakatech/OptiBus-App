<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOpsScopeAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_route_filter_does_not_fall_back_to_accessible_routes_when_foreign_route_is_requested(): void
    {
        AccessControl::syncDefaults();
        $today = now()->toDateString();

        [$tenantA, $poolA] = $this->tenantWithSinglePool('audit-tenant-a', 100000);
        [$tenantB, $poolB] = $this->tenantWithSinglePool('audit-tenant-b', 200000);
        $routeA = $this->createRouteForPool('audit-tenant-a', $poolA, 'PINRANG - MAKASSAR', 100000);
        $routeB = $this->createRouteForPool('audit-tenant-b', $poolB, 'PAREPARE - MAKASSAR', 200000);

        DB::table('bookings')->insert([
            [
                'tenant_id' => $tenantA,
                'route_id' => $routeA,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $today,
                'jam' => '08:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'BOOKING TENANT A',
                'phone' => '081111111111',
                'pickup_point' => 'Pinrang',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 100000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'tenant_id' => $tenantB,
                'route_id' => $routeB,
                'rute' => 'PAREPARE - MAKASSAR',
                'tanggal' => $today,
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'B1',
                'name' => 'BOOKING TENANT B',
                'phone' => '082222222222',
                'pickup_point' => 'Parepare',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 200000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $operator = User::factory()->create([
            'tenant_id' => $tenantA,
            'is_super_admin' => false,
        ]);
        $this->assignRole($operator, 'admin-pool');
        DB::table('pool_user')->insert([
            'pool_id' => $poolA,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($operator)
            ->getJson(route('api.admin.reports.summary', [
                'from' => $today,
                'to' => $today,
                'type' => 'booking',
                'route_id' => $routeB,
            ]))
            ->assertOk()
            ->assertJsonPath('summary.total_rows', 0)
            ->assertJsonPath('summary.revenue_total', 0)
            ->assertJsonCount(0, 'rows');
    }

    public function test_booking_report_csv_route_filter_stays_empty_for_foreign_route(): void
    {
        AccessControl::syncDefaults();
        $today = now()->toDateString();

        [$tenantA, $poolA] = $this->tenantWithSinglePool('audit-export-a', 100000);
        [$tenantB, $poolB] = $this->tenantWithSinglePool('audit-export-b', 200000);
        $routeA = $this->createRouteForPool('audit-export-a', $poolA, 'PINRANG - MAKASSAR', 100000);
        $routeB = $this->createRouteForPool('audit-export-b', $poolB, 'PAREPARE - MAKASSAR', 200000);

        DB::table('bookings')->insert([
            [
                'tenant_id' => $tenantA,
                'route_id' => $routeA,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => $today,
                'jam' => '08:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'BOOKING EXPORT A',
                'phone' => '081111111112',
                'pickup_point' => 'Pinrang',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 100000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'tenant_id' => $tenantB,
                'route_id' => $routeB,
                'rute' => 'PAREPARE - MAKASSAR',
                'tanggal' => $today,
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'B1',
                'name' => 'BOOKING EXPORT B',
                'phone' => '082222222223',
                'pickup_point' => 'Parepare',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 200000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $operator = User::factory()->create([
            'tenant_id' => $tenantA,
            'is_super_admin' => false,
        ]);
        $this->assignRole($operator, 'admin-pusat');
        DB::table('pool_user')->insert([
            'pool_id' => $poolA,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($operator)->get(route('api.admin.reports.bookings-csv', [
            'from' => $today,
            'to' => $today,
            'route_id' => $routeB,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = trim($response->streamedContent());
        $lines = preg_split('/\r\n|\r|\n/', $csv) ?: [];

        $this->assertCount(1, $lines);
        $this->assertStringContainsString('id,rute,tanggal,jam', $lines[0]);
        $this->assertStringNotContainsString('BOOKING EXPORT A', $csv);
        $this->assertStringNotContainsString('BOOKING EXPORT B', $csv);
    }

    public function test_customer_master_write_paths_do_not_reuse_other_pool_records_in_same_tenant(): void
    {
        AccessControl::syncDefaults();
        if (! Schema::hasTable('customer_bagasi') || ! Schema::hasTable('customer_charter')) {
            $this->markTestSkipped('Customer master tables are not available.');
        }

        $tenantId = $this->tenantIdBySlug('audit-customer-tenant');
        $poolA = $this->createPool($tenantId, 'POOL A', 'AUD-A', 100000);
        $poolB = $this->createPool($tenantId, 'POOL B', 'AUD-B', 100000);
        $this->createRouteForPool('audit-customer-tenant', $poolA, 'A - MAKASSAR', 100000);
        $this->createRouteForPool('audit-customer-tenant', $poolB, 'B - MAKASSAR', 100000);

        $regularOtherId = (int) DB::table('customers')->insertGetId([
            'tenant_id' => $tenantId,
            'pool_id' => $poolB,
            'name' => 'CUSTOMER POOL B',
            'phone' => '081230000001',
            'pickup_point' => 'Pool B',
            'created_at' => now(),
        ]);
        $bagasiOtherId = (int) DB::table('customer_bagasi')->insertGetId([
            'tenant_id' => $tenantId,
            'pool_id' => $poolB,
            'nama' => 'BAGASI POOL B',
            'no_hp' => '081230000002',
            'alamat' => 'Pool B',
            'tipe' => 'pengirim',
            'created_at' => now(),
        ]);
        $charterOtherId = (int) DB::table('customer_charter')->insertGetId([
            'tenant_id' => $tenantId,
            'pool_id' => $poolB,
            'nama' => 'CHARTER POOL B',
            'no_hp' => '081230000003',
            'alamat' => 'Pool B',
            'company' => 'Tenant B',
            'created_at' => now(),
        ]);

        $operator = User::factory()->create([
            'tenant_id' => $tenantId,
            'is_super_admin' => false,
        ]);
        $this->assignRole($operator, 'admin-pusat');
        DB::table('pool_user')->insert([
            'pool_id' => $poolA,
            'user_id' => $operator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($operator);

        $this->postJson(route('api.admin.customers.save'), [
            'name' => 'CUSTOMER POOL A',
            'phone' => '081230000001',
            'pickup_point' => 'Pool A',
        ])->assertCreated();
        $this->assertDatabaseHas('customers', [
            'id' => $regularOtherId,
            'pool_id' => $poolB,
            'name' => 'CUSTOMER POOL B',
        ]);
        $this->assertSame(2, DB::table('customers')->where('phone', '081230000001')->count());

        $this->postJson(route('api.admin.customer-bagasi.save'), [
            'nama' => 'BAGASI POOL A',
            'no_hp' => '081230000002',
            'alamat' => 'Pool A',
            'tipe' => 'penerima',
        ])->assertCreated();
        $this->assertDatabaseHas('customer_bagasi', [
            'id' => $bagasiOtherId,
            'pool_id' => $poolB,
            'nama' => 'BAGASI POOL B',
        ]);
        $this->assertSame(2, DB::table('customer_bagasi')->where('no_hp', '081230000002')->count());

        $this->postJson(route('api.admin.customer-charter.save'), [
            'nama' => 'CHARTER POOL A',
            'no_hp' => '081230000003',
            'alamat' => 'Pool A',
            'company' => 'Tenant A',
        ])->assertCreated();
        $this->assertDatabaseHas('customer_charter', [
            'id' => $charterOtherId,
            'pool_id' => $poolB,
            'nama' => 'CHARTER POOL B',
        ]);
        $this->assertSame(2, DB::table('customer_charter')->where('no_hp', '081230000003')->count());

        $this->deleteJson(route('api.admin.customers.delete', ['id' => $regularOtherId]))->assertStatus(404);
        $this->deleteJson(route('api.admin.customer-bagasi.delete', ['id' => $bagasiOtherId]))->assertStatus(404);
        $this->deleteJson(route('api.admin.customer-charter.delete', ['id' => $charterOtherId]))->assertStatus(404);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function tenantWithSinglePool(string $slug, int $targetRevenue): array
    {
        $tenantId = $this->tenantIdBySlug($slug);
        $poolId = $this->createPool($tenantId, strtoupper($slug).' POOL', $slug.'-pool', $targetRevenue);

        return [$tenantId, $poolId];
    }

    private function tenantIdBySlug(string $slug): int
    {
        $existing = (int) (DB::table('tenants')->where('slug', $slug)->value('id') ?? 0);
        if ($existing > 0) {
            return $existing;
        }

        return (int) DB::table('tenants')->insertGetId([
            'name' => strtoupper(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPool(int $tenantId, string $name, string $code, int $targetRevenue): int
    {
        return (int) DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => $code,
            'status' => 'active',
            'target_revenue' => $targetRevenue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRouteForPool(string $tenantSlug, int $poolId, string $name, int $targetRevenue): int
    {
        $routeId = (int) DB::table('routes')->insertGetId([
            'tenant_id' => $this->tenantIdBySlug($tenantSlug),
            'name' => $name,
            'origin' => explode(' - ', $name)[0] ?? 'ASAL',
            'destination' => explode(' - ', $name)[1] ?? 'TUJUAN',
            'target_revenue' => $targetRevenue,
            'created_at' => now(),
        ]);

        DB::table('pool_route')->insert([
            'pool_id' => $poolId,
            'route_id' => $routeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $routeId;
    }

    private function assignRole(User $user, string $slug): void
    {
        $roleId = (int) (DB::table('roles')->where('slug', $slug)->value('id') ?? 0);
        if ($roleId <= 0) {
            return;
        }

        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
