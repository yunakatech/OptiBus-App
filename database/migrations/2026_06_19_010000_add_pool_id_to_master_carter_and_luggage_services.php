<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['master_carter', 'luggage_services'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'pool_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('pool_id')->nullable()->after('tenant_id');
                });
            }

            DB::statement("CREATE INDEX IF NOT EXISTS idx_{$tableName}_pool_id ON {$tableName} (pool_id)");
        }

        $this->backfillPools('master_carter');
        $this->backfillPools('luggage_services');
    }

    public function down(): void
    {
        foreach (['master_carter', 'luggage_services'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'pool_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex("idx_{$tableName}_pool_id");
                $table->dropColumn('pool_id');
            });
        }
    }

    private function backfillPools(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'pool_id')) {
            return;
        }

        if (! Schema::hasTable('pools')) {
            return;
        }

        if (Schema::hasColumn($tableName, 'tenant_id')) {
            $tenantIds = DB::table($tableName)
                ->whereNull('pool_id')
                ->whereNotNull('tenant_id')
                ->distinct()
                ->pluck('tenant_id')
                ->map(static fn ($value): int => (int) $value)
                ->filter(static fn (int $tenantId): bool => $tenantId > 0)
                ->values()
                ->all();

            foreach ($tenantIds as $tenantId) {
                $poolId = $this->defaultPoolIdForTenant($tenantId);
                if ($poolId <= 0) {
                    continue;
                }

                DB::table($tableName)
                    ->whereNull('pool_id')
                    ->where('tenant_id', $tenantId)
                    ->update(['pool_id' => $poolId]);
            }
        }

        $remaining = (int) DB::table($tableName)->whereNull('pool_id')->count();
        if ($remaining <= 0) {
            return;
        }

        $fallbackPoolId = $this->firstActivePoolId();
        if ($fallbackPoolId <= 0) {
            return;
        }

        DB::table($tableName)
            ->whereNull('pool_id')
            ->update(['pool_id' => $fallbackPoolId]);
    }

    private function defaultPoolIdForTenant(int $tenantId): int
    {
        if ($tenantId <= 0 || ! Schema::hasTable('pools')) {
            return 0;
        }

        $poolId = (int) (DB::table('pools')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id') ?? 0);
        if ($poolId > 0) {
            return $poolId;
        }

        return (int) (DB::table('pools')
            ->where('tenant_id', $tenantId)
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    private function firstActivePoolId(): int
    {
        if (! Schema::hasTable('pools')) {
            return 0;
        }

        return (int) (DB::table('pools')
            ->where('status', 'active')
            ->orderBy('id')
            ->value('id') ?? 0);
    }
};
