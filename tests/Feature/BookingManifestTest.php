<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_print_includes_mapped_luggage_and_cancel_history(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $route = 'PINRANG - MAKASSAR';
        $date = '2026-05-15';
        $jam = '09:00:00';
        $unit = 1;

        $activeBookingId = DB::table('bookings')->insertGetId([
            'rute' => $route,
            'tanggal' => $date,
            'jam' => $jam,
            'unit' => $unit,
            'seat' => '1',
            'name' => 'RIDWAN',
            'phone' => '081234567890',
            'pickup_point' => 'Terminal',
            'pembayaran' => 'Lunas',
            'status' => 'active',
            'price' => 150000,
            'discount' => 10000,
            'ticket_code' => 'TKT-001',
            'departure_code' => 'DEP-001',
            'created_at' => now(),
        ]);

        $canceledBookingId = DB::table('bookings')->insertGetId([
            'rute' => $route,
            'tanggal' => $date,
            'jam' => $jam,
            'unit' => $unit,
            'seat' => '2',
            'name' => 'SALSABILA',
            'phone' => '081234567891',
            'pickup_point' => 'Mall',
            'pembayaran' => 'Belum Lunas',
            'status' => 'canceled',
            'price' => 120000,
            'discount' => 0,
            'ticket_code' => 'TKT-002',
            'departure_code' => 'DEP-001',
            'created_at' => now(),
        ]);

        DB::table('cancellations')->insert([
            'booking_id' => $canceledBookingId,
            'admin_user' => 'ops@cabooq.test',
            'reason' => 'Penumpang batal berangkat',
            'created_at' => now()->subMinutes(15),
        ]);

        $assignmentId = DB::table('trip_assignments')->insertGetId([
            'rute' => $route,
            'tanggal' => $date,
            'jam' => $jam,
            'unit' => $unit,
            'status' => 'active',
            'created_at' => now(),
        ]);

        DB::table('luggages')->insert([
            'sender_name' => 'Pengirim A',
            'sender_phone' => '0811111111',
            'receiver_name' => 'Penerima B',
            'receiver_phone' => '0822222222',
            'quantity' => 3,
            'notes' => '3 kardus makanan',
            'price' => 75000,
            'status' => 'sent',
            'payment_status' => 'Lunas',
            'rute' => $route,
            'tanggal' => $date,
            'unit_id' => 1,
            'trip_assignment_id' => $assignmentId,
            'kode_resi' => 'RESI-001',
            'created_at' => now(),
        ]);

        $groupKey = $this->manifestGroupKey($route, $date, $jam, $unit);
        $response = $this->get(route('bookings.manifest.print', ['groupKey' => $groupKey]));

        $response->assertOk();
        $response->assertSee('Bagasi Terpasang');
        $response->assertSee('RESI-001');
        $response->assertSee('Pengirim A');
        $response->assertSee('Penerima B');
        $response->assertSee('Dalam Perjalanan');
        $response->assertSee('History Cancel');
        $response->assertSee('Penumpang batal berangkat');
        $response->assertSee('ops@cabooq.test');
        $response->assertSee('SALSABILA');

        $this->assertDatabaseHas('bookings', [
            'id' => $activeBookingId,
            'status' => 'active',
        ]);
    }

    private function manifestGroupKey(string $route, string $date, string $jam, int $unit): string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $route) ?? ''));
        $normalized = str_replace([' => ', ' -> ', ' - '], ' TO ', $normalized);
        $normalized = str_replace(['=>', '->'], ' TO ', $normalized);
        $normalized = preg_replace('/\s*-\s*/', ' TO ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);

        return md5($normalized.'|'.$date.'|'.substr($jam, 0, 5).'|'.max(1, $unit));
    }
}
