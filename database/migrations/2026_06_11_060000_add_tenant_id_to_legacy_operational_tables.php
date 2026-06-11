<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $this->addTenantColumn($table);
        }

        foreach ($this->tenantScopedTables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            DB::table($table)->update(['tenant_id' => 1]);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tenantScopedTables()) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $indexName = "idx_{$table}_tenant_id";
            try {
                DB::statement("DROP INDEX IF EXISTS {$indexName}");
            } catch (Throwable) {
                try {
                    Schema::table($table, function (Blueprint $schema) use ($indexName): void {
                        $schema->dropIndex($indexName);
                    });
                } catch (Throwable) {
                    // Index may not exist on this database driver.
                }
            }

            Schema::table($table, function (Blueprint $schema): void {
                $schema->dropColumn('tenant_id');
            });
        }
    }

    private function addTenantColumn(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'tenant_id')) {
            Schema::table($table, function (Blueprint $schema): void {
                $schema->unsignedBigInteger('tenant_id')->nullable()->after('id');
            });
        }

        $indexName = "idx_{$table}_tenant_id";
        try {
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} (tenant_id)");
        } catch (Throwable) {
            try {
                Schema::table($table, function (Blueprint $schema) use ($indexName): void {
                    $schema->index('tenant_id', $indexName);
                });
            } catch (Throwable) {
                // Index likely already exists or the current driver cannot add it online.
            }
        }
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
            'schedules',
            'schedule_units',
            'segments',
            'customers',
            'customer_bagasi',
            'customer_charter',
            'cancellations',
            'bagasi_logs',
        ];
    }
};
