<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_payment_page_is_scoped_to_operator_pool(): void
    {
        [$operator, $pinrangRouteId, $makassarRouteId, $pinrangPoolId, $makassarPoolId] = $this->seedPoolOperator();

        DB::table('bookings')->insert([
            [
                'route_id' => $pinrangRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-06-05',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'BOOKING PINRANG',
                'phone' => '081100001',
                'pickup_point' => 'Pinrang',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'route_id' => $makassarRouteId,
                'rute' => 'MAKASSAR - PAREPARE',
                'tanggal' => '2026-06-05',
                'jam' => '10:00:00',
                'unit' => 1,
                'seat' => 'A2',
                'name' => 'BOOKING MAKASSAR',
                'phone' => '081100002',
                'pickup_point' => 'Makassar',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 140000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        DB::table('charters')->insert([
            [
                'pool_id' => $pinrangPoolId,
                'name' => 'CARTER PINRANG',
                'start_date' => '2026-06-05',
                'end_date' => '2026-06-05',
                'pickup_point' => 'PINRANG',
                'drop_point' => 'MAKASSAR',
                'price' => 2500000,
                'down_payment' => 500000,
                'payment_status' => 'DP',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'pool_id' => $makassarPoolId,
                'name' => 'CARTER MAKASSAR',
                'start_date' => '2026-06-05',
                'end_date' => '2026-06-05',
                'pickup_point' => 'MAKASSAR',
                'drop_point' => 'PAREPARE',
                'price' => 2400000,
                'down_payment' => 400000,
                'payment_status' => 'DP',
                'status' => 'active',
                'created_at' => now(),
            ],
        ]);

        DB::table('luggages')->insert([
            [
                'pool_id' => $pinrangPoolId,
                'sender_name' => 'BAGASI PINRANG',
                'sender_phone' => '082100001',
                'receiver_name' => 'PENERIMA PINRANG',
                'receiver_phone' => '083100001',
                'rute_id' => $pinrangRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-06-05',
                'quantity' => 1,
                'price' => 50000,
                'payment_status' => 'Belum Bayar',
                'status' => 'Diterima',
                'created_at' => now(),
            ],
            [
                'pool_id' => $makassarPoolId,
                'sender_name' => 'BAGASI MAKASSAR',
                'sender_phone' => '082100002',
                'receiver_name' => 'PENERIMA MAKASSAR',
                'receiver_phone' => '083100002',
                'rute_id' => $makassarRouteId,
                'rute' => 'MAKASSAR - PAREPARE',
                'tanggal' => '2026-06-05',
                'quantity' => 1,
                'price' => 45000,
                'payment_status' => 'Belum Bayar',
                'status' => 'Diterima',
                'created_at' => now(),
            ],
        ]);

        $this->actingAs($operator);

        $this->get(route('payments.index', ['status' => 'unpaid']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Payments')
                ->where('filters.status', 'unpaid')
                ->missing('paymentData')
                ->loadDeferredProps('payment-data', fn (Assert $reload) => $reload
                    ->where('paymentData.rows', fn (mixed $rows): bool => collect($rows)->pluck('customer_name')->contains('BOOKING PINRANG')
                        && collect($rows)->pluck('customer_name')->contains('BAGASI PINRANG')
                        && ! collect($rows)->pluck('customer_name')->contains('BOOKING MAKASSAR')
                        && ! collect($rows)->pluck('customer_name')->contains('BAGASI MAKASSAR'))
                    ->where('paymentData.summary.by_status.dp.count', 1)),
            );
    }

    public function test_payment_update_respects_pool_scope(): void
    {
        [$operator, $pinrangRouteId, $makassarRouteId] = $this->seedPoolOperator();

        $insideBookingId = DB::table('bookings')->insertGetId([
            'route_id' => $pinrangRouteId,
            'rute' => 'PINRANG - MAKASSAR',
            'tanggal' => '2026-06-05',
            'jam' => '09:00:00',
            'unit' => 1,
            'seat' => 'A1',
            'name' => 'BOOKING PINRANG',
            'phone' => '081100001',
            'pickup_point' => 'Pinrang',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'price' => 150000,
            'discount' => 0,
            'created_at' => now(),
        ]);
        $outsideBookingId = DB::table('bookings')->insertGetId([
            'route_id' => $makassarRouteId,
            'rute' => 'MAKASSAR - PAREPARE',
            'tanggal' => '2026-06-05',
            'jam' => '10:00:00',
            'unit' => 1,
            'seat' => 'A2',
            'name' => 'BOOKING MAKASSAR',
            'phone' => '081100002',
            'pickup_point' => 'Makassar',
            'pembayaran' => 'Belum Lunas',
            'status' => 'active',
            'price' => 140000,
            'discount' => 0,
            'created_at' => now(),
        ]);

        $this->actingAs($operator);

        $this->postJson(route('api.admin.payments.update', ['source' => 'booking', 'id' => $outsideBookingId]), [
            'payment_status' => 'Lunas',
        ])->assertNotFound();

        $this->postJson(route('api.admin.payments.update', ['source' => 'booking', 'id' => $insideBookingId]), [
            'payment_status' => 'DP',
        ])->assertOk()
            ->assertJsonPath('payment_status', 'DP');

        $this->assertDatabaseHas('bookings', [
            'id' => $insideBookingId,
            'pembayaran' => 'DP',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $outsideBookingId,
            'pembayaran' => 'Belum Lunas',
        ]);
    }

    public function test_payment_export_csv_respects_filter_and_pool_scope(): void
    {
        [$operator, $pinrangRouteId, $makassarRouteId] = $this->seedPoolOperator();

        DB::table('bookings')->insert([
            [
                'route_id' => $pinrangRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-06-05',
                'jam' => '09:00:00',
                'unit' => 1,
                'seat' => 'A1',
                'name' => 'BOOKING PINRANG EXPORT',
                'phone' => '081100001',
                'pickup_point' => 'Pinrang',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 150000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'route_id' => $makassarRouteId,
                'rute' => 'MAKASSAR - PAREPARE',
                'tanggal' => '2026-06-05',
                'jam' => '10:00:00',
                'unit' => 1,
                'seat' => 'A2',
                'name' => 'BOOKING MAKASSAR EXPORT',
                'phone' => '081100002',
                'pickup_point' => 'Makassar',
                'pembayaran' => 'Belum Lunas',
                'status' => 'active',
                'price' => 140000,
                'discount' => 0,
                'created_at' => now(),
            ],
            [
                'route_id' => $pinrangRouteId,
                'rute' => 'PINRANG - MAKASSAR',
                'tanggal' => '2026-06-05',
                'jam' => '11:00:00',
                'unit' => 1,
                'seat' => 'A3',
                'name' => 'BOOKING PINRANG LUNAS EXPORT',
                'phone' => '081100003',
                'pickup_point' => 'Pinrang',
                'pembayaran' => 'Lunas',
                'status' => 'active',
                'price' => 160000,
                'discount' => 0,
                'created_at' => now(),
            ],
        ]);

        $this->actingAs($operator);

        $response = $this->get(route('payments.export', [
            'status' => 'unpaid',
            'source' => 'booking',
            'q' => 'export',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('BOOKING PINRANG EXPORT', $csv);
        $this->assertStringNotContainsString('BOOKING MAKASSAR EXPORT', $csv);
        $this->assertStringNotContainsString('BOOKING PINRANG LUNAS EXPORT', $csv);
        $this->assertStringContainsString('Sumber,Kode,Customer', $csv);
    }

    /**
     * @return array{0: User, 1: int, 2: int, 3: int, 4: int}
     */
    private function seedPoolOperator(): array
    {
        AccessControl::syncDefaults();
        $tenantId = $this->defaultTenantId();

        $pinrangRouteId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'PINRANG - MAKASSAR',
            'origin' => 'PINRANG',
            'destination' => 'MAKASSAR',
            'created_at' => now(),
        ]);
        $makassarRouteId = DB::table('routes')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'MAKASSAR - PAREPARE',
            'origin' => 'MAKASSAR',
            'destination' => 'PAREPARE',
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
            [
                'pool_id' => $pinrangPoolId,
                'route_id' => $pinrangRouteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pool_id' => $makassarPoolId,
                'route_id' => $makassarRouteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $operator = User::factory()->create(['is_super_admin' => false, 'tenant_id' => $tenantId]);
        DB::table('pool_user')->insert([
            'pool_id' => $pinrangPoolId,
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

        return [$operator, $pinrangRouteId, $makassarRouteId, $pinrangPoolId, $makassarPoolId];
    }
}
