<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LuggageIncidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_incidents_update_condition_without_changing_payment(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-partial');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);

        $luggageId = $this->luggage($tenantId, $poolId, 3, 'Lunas');

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'damaged',
            'quantity' => 1,
            'description' => 'Koper rusak pada roda.',
        ])->assertCreated();

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'lost',
            'quantity' => 2,
            'description' => 'Dua barang tidak ditemukan.',
        ])->assertCreated();

        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'damaged_quantity' => 1,
            'lost_quantity' => 2,
            'condition_status' => 'damaged_and_lost',
            'payment_status' => 'Lunas',
        ]);

        $this->getJson(route('api.admin.luggages.index', ['condition' => 'lost']))
            ->assertOk()
            ->assertJsonPath('luggages.0.id', $luggageId)
            ->assertJsonPath('luggages.0.condition_status', 'damaged_and_lost');

        foreach (['damaged', 'lost', 'damaged_and_lost'] as $condition) {
            $this->getJson(route('api.admin.luggages.index', ['condition' => $condition]))
                ->assertOk()
                ->assertJsonPath('luggages.0.id', $luggageId)
                ->assertJsonPath('luggages.0.condition_status', 'damaged_and_lost');
        }

        $this->assertDatabaseCount('luggage_incidents', 2);
        $this->assertDatabaseCount('bagasi_logs', 2);
    }

    public function test_incident_quantity_cannot_exceed_luggage_quantity(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-quantity');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 2);

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'lost',
            'quantity' => 3,
            'description' => 'Jumlah tidak valid.',
        ])->assertStatus(422);

        $this->assertDatabaseCount('luggage_incidents', 0);
        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'condition_status' => 'normal',
            'lost_quantity' => 0,
        ]);
    }

    public function test_condition_filters_include_legacy_condition_status_values(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-legacy-filter');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 2);

        DB::table('luggages')->where('id', $luggageId)->update([
            'condition_status' => 'damaged_and_lost',
            'damaged_quantity' => 0,
            'lost_quantity' => 0,
        ]);

        foreach (['damaged', 'lost', 'damaged_and_lost'] as $condition) {
            $this->getJson(route('api.admin.luggages.index', ['condition' => $condition]))
                ->assertOk()
                ->assertJsonPath('luggages.0.id', $luggageId)
                ->assertJsonPath('luggages.0.condition_status', 'damaged_and_lost');
        }

        $this->getJson(route('api.admin.luggages.index', ['condition' => 'normal']))
            ->assertOk()
            ->assertJsonCount(0, 'luggages');
    }

    public function test_canceled_luggage_cannot_receive_new_incident_but_existing_incident_can_be_claimed(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-canceled');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 1);

        $incident = $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'damaged',
            'quantity' => 1,
            'description' => 'Barang rusak sebelum dibatalkan.',
        ])->assertCreated()->json('incident');

        DB::table('luggages')->where('id', $luggageId)->update(['status' => 'canceled']);

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'lost',
            'quantity' => 1,
            'description' => 'Tidak boleh dibuat setelah canceled.',
        ])->assertStatus(422);

        $this->patchJson(route('api.admin.luggages.incidents.update', [
            'incidentId' => $incident['id'],
        ]), [
            'status' => 'investigating',
        ])->assertOk();

        $this->patchJson(route('api.admin.luggages.incidents.update', [
            'incidentId' => $incident['id'],
        ]), [
            'status' => 'approved',
        ])->assertOk();

        $this->postJson(route('api.admin.luggages.incidents.claim', [
            'incidentId' => $incident['id'],
        ]), [
            'claim_status' => 'paid',
            'claim_amount' => 150000,
            'approved_amount' => 100000,
            'resolution_note' => 'Kompensasi dibayarkan.',
        ])->assertOk();

        $this->patchJson(route('api.admin.luggages.incidents.update', [
            'incidentId' => $incident['id'],
        ]), [
            'status' => 'resolved',
        ])->assertOk();

        $this->assertDatabaseHas('luggage_incidents', [
            'id' => $incident['id'],
            'status' => 'resolved',
            'claim_status' => 'paid',
            'approved_amount' => 100000,
        ]);
        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'status' => 'canceled',
            'payment_status' => 'Belum Bayar',
        ]);

        $this->getJson(route('api.admin.luggages.index', ['condition' => 'damaged']))
            ->assertOk()
            ->assertJsonPath('luggages.0.id', $luggageId)
            ->assertJsonPath('luggages.0.condition_status', 'damaged')
            ->assertJsonPath('luggages.0.has_incident_history', true)
            ->assertJsonPath('luggages.0.latest_incident_status', 'resolved')
            ->assertJsonPath('luggages.0.latest_claim_status', 'paid')
            ->assertJsonPath('luggages.0.incident_history_label', 'Rusak - Klaim Dibayar');
    }

    public function test_final_claim_cancels_delivery_and_keeps_condition_history(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-final-claim');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 1, 'Lunas');

        $incident = $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'lost',
            'quantity' => 1,
            'description' => 'Barang tidak ditemukan setelah pemeriksaan.',
        ])->assertCreated()->json('incident');

        foreach (['investigating', 'approved'] as $status) {
            $this->patchJson(route('api.admin.luggages.incidents.update', [
                'incidentId' => $incident['id'],
            ]), ['status' => $status])->assertOk();
        }

        $this->postJson(route('api.admin.luggages.incidents.claim', [
            'incidentId' => $incident['id'],
        ]), [
            'claim_status' => 'paid',
            'claim_amount' => 250000,
            'approved_amount' => 200000,
        ])->assertOk()->assertJsonPath('claim.luggage_status', 'canceled');

        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'status' => 'canceled',
            'condition_status' => 'lost',
            'lost_quantity' => 1,
            'payment_status' => 'Lunas',
        ]);

        $this->getJson(route('api.admin.luggages.index', ['condition' => 'lost']))
            ->assertOk()
            ->assertJsonPath('luggages.0.id', $luggageId)
            ->assertJsonPath('luggages.0.status', 'canceled')
            ->assertJsonPath('luggages.0.condition_status', 'lost')
            ->assertJsonPath('luggages.0.latest_claim_status', 'paid')
            ->assertJsonPath('luggages.0.incident_history_label', 'Hilang - Klaim Dibayar');
    }

    public function test_new_incident_validation_uses_active_incidents_not_closed_history(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-active-history');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 1);

        $incident = $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'damaged',
            'quantity' => 1,
            'description' => 'Insiden pertama selesai.',
        ])->assertCreated()->json('incident');

        foreach (['investigating', 'approved', 'resolved'] as $status) {
            $this->patchJson(route('api.admin.luggages.incidents.update', [
                'incidentId' => $incident['id'],
            ]), ['status' => $status])->assertOk();
        }

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $luggageId]), [
            'type' => 'lost',
            'quantity' => 1,
            'description' => 'Insiden kedua setelah history selesai.',
        ])->assertCreated();

        $this->assertDatabaseHas('luggages', [
            'id' => $luggageId,
            'damaged_quantity' => 1,
            'lost_quantity' => 1,
            'condition_status' => 'damaged_and_lost',
        ]);
    }

    public function test_incidents_are_scoped_to_active_tenant_and_pool(): void
    {
        [$tenantId, $poolId] = $this->tenantAndPool('incident-scope');
        $this->actingAsSuperAdminWithTenantContext($tenantId);
        $this->withSession(['active_pool_id' => $poolId]);
        $luggageId = $this->luggage($tenantId, $poolId, 1);

        $otherTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Incident Other Tenant',
            'slug' => 'incident-other-tenant',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherPoolId = DB::table('pools')->insertGetId([
            'tenant_id' => $otherTenantId,
            'name' => 'Incident Other Pool',
            'code' => 'INC-OTHER',
            'status' => 'active',
            'target_revenue' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherLuggageId = $this->luggage($otherTenantId, $otherPoolId, 1);

        $this->getJson(route('api.admin.luggages.incidents.index', ['id' => $otherLuggageId]))
            ->assertStatus(404);

        $this->postJson(route('api.admin.luggages.incidents.store', ['id' => $otherLuggageId]), [
            'type' => 'lost',
            'quantity' => 1,
            'description' => 'Tidak boleh lintas tenant.',
        ])->assertStatus(404);

        $this->getJson(route('api.admin.luggages.incidents.index', ['id' => $luggageId]))
            ->assertOk()
            ->assertJsonCount(0, 'incidents');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function tenantAndPool(string $suffix): array
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Tenant '.$suffix,
            'slug' => 'tenant-'.$suffix,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $poolId = DB::table('pools')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Pool '.$suffix,
            'code' => 'INC-'.strtoupper(substr(md5($suffix), 0, 6)),
            'status' => 'active',
            'target_revenue' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$tenantId, $poolId];
    }

    private function luggage(int $tenantId, int $poolId, int $quantity, string $paymentStatus = 'Belum Bayar'): int
    {
        return (int) DB::table('luggages')->insertGetId([
            'tenant_id' => $tenantId,
            'pool_id' => $poolId,
            'sender_name' => 'PENGIRIM INSIDEN',
            'sender_phone' => '081234567890',
            'receiver_name' => 'PENERIMA INSIDEN',
            'receiver_phone' => '081234567891',
            'quantity' => $quantity,
            'price' => 50000,
            'status' => 'pending',
            'payment_status' => $paymentStatus,
            'created_at' => now(),
        ]);
    }
}
