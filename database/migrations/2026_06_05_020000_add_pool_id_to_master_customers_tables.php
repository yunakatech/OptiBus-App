<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customer_bagasi', 'customer_charter'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'pool_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('pool_id')->nullable()->after('id');
                });
            }

            DB::statement("CREATE INDEX IF NOT EXISTS idx_{$tableName}_pool_id ON {$tableName} (pool_id)");
        }

        $targetPoolId = $this->targetPinrangPoolId();
        if ($targetPoolId <= 0) {
            return;
        }

        foreach (['customer_bagasi', 'customer_charter'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'pool_id')) {
                DB::table($tableName)->update(['pool_id' => $targetPoolId]);
            }
        }
    }

    public function down(): void
    {
        foreach (['customer_bagasi', 'customer_charter'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'pool_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex("idx_{$tableName}_pool_id");
                $table->dropColumn('pool_id');
            });
        }
    }

    private function targetPinrangPoolId(): int
    {
        if (! Schema::hasTable('pools')) {
            return 0;
        }

        $pools = DB::table('pools')
            ->where('status', 'active')
            ->get(['id', 'name']);
        $priorities = [
            'POOL PERWAKILAN PINRANG',
            'PERWAKILAN PINRANG',
            'POOL PINRANG',
        ];

        foreach ($priorities as $targetName) {
            foreach ($pools as $pool) {
                if ($this->normalizeName((string) ($pool->name ?? '')) === $targetName) {
                    return (int) ($pool->id ?? 0);
                }
            }
        }

        $pinrangPools = $pools->filter(
            fn ($pool): bool => str_contains($this->normalizeName((string) ($pool->name ?? '')), 'PINRANG'),
        );

        return $pinrangPools->count() === 1 ? (int) ($pinrangPools->first()->id ?? 0) : 0;
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', ' ', mb_strtoupper(trim($name))) ?? '';
    }
};
