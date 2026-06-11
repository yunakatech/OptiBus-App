<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        $this->ensureQbusDefaultTenant();

        foreach ($this->tenantScopedTables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            DB::table($table)->update(['tenant_id' => 1]);
        }
    }

    public function down(): void
    {
        // Data normalization is intentionally not reverted.
    }

    private function ensureQbusDefaultTenant(): void
    {
        $now = now();
        $tenantOne = DB::table('tenants')->where('id', 1)->first();
        $slugOwner = DB::table('tenants')->where('slug', 'qbus-default')->first();

        if ($slugOwner && (int) $slugOwner->id !== 1) {
            DB::table('tenants')
                ->where('id', (int) $slugOwner->id)
                ->update([
                    'slug' => 'qbus-default-'.$slugOwner->id,
                    'updated_at' => $now,
                ]);
        }

        if ($tenantOne) {
            DB::table('tenants')->where('id', 1)->update([
                'name' => 'Qbus Default',
                'slug' => 'qbus-default',
                'status' => 'active',
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'Qbus Default',
            'slug' => 'qbus-default',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function tenantScopedTables(): array
    {
        return [
            'users',
            'pools',
            'routes',
            'armadas',
            'drivers',
            'units',
            'luggage_services',
            'master_carter',
            'bookings',
            'trip_assignments',
            'charters',
            'luggages',
            'activity_logs',
            'subscriptions',
            'invoice_subscriptions',
        ];
    }
};
