<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOpsDriverTest extends TestCase
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

    public function test_driver_save_and_index_tolerate_legacy_schema_without_optional_columns(): void
    {
        $this->actingAsSuperAdmin();

        foreach ([
            'target_revenue_tahunan',
            'target_revenue_bulanan',
            'revenue',
            'bop',
            'fixed_cost',
            'armada_nopol',
            'armada_id',
        ] as $column) {
            $this->dropDriverColumnIfExists($column);
        }

        $driver = $this->postJson(route('api.admin.drivers.save'), [
            'nama' => 'Supir Legacy',
            'phone' => '081230000001',
            'target_revenue_bulanan' => 5000000,
            'target_revenue_tahunan' => 60000000,
            'revenue' => 1000000,
            'bop' => 150000,
            'fixed_cost' => 25000,
        ])->assertCreated()->json();

        $driverId = (int) ($driver['id'] ?? 0);
        $this->assertTrue($driverId > 0);
        $this->assertDatabaseHas('drivers', [
            'id' => $driverId,
            'nama' => 'SUPIR LEGACY',
            'phone' => '081230000001',
        ]);

        $rows = collect($this->getJson(route('api.admin.drivers.index'))
            ->assertOk()
            ->json('drivers'));
        $row = $rows->firstWhere('id', $driverId);

        $this->assertNotNull($row);
        $this->assertSame(0.0, (float) ($row['target_revenue_bulanan'] ?? -1));
        $this->assertSame(0.0, (float) ($row['target_revenue_tahunan'] ?? -1));
        $this->assertSame(0.0, (float) ($row['revenue'] ?? -1));
        $this->assertSame(0.0, (float) ($row['bop'] ?? -1));
        $this->assertSame(0.0, (float) ($row['fixed_cost'] ?? -1));
    }

    public function test_driver_save_returns_422_for_unknown_armada_nopol(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(route('api.admin.drivers.save'), [
            'nama' => 'Supir Salah Nopol',
            'armada_nopol' => 'DD 0000 NO',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Nopol armada tidak ditemukan.');
    }

    private function dropDriverColumnIfExists(string $column): void
    {
        if (! Schema::hasColumn('drivers', $column)) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) use ($column): void {
            $table->dropColumn($column);
        });
    }
}
