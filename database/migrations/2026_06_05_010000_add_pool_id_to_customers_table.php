<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (! Schema::hasColumn('customers', 'pool_id')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->unsignedBigInteger('pool_id')->nullable()->after('id');
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_customers_pool_id ON customers (pool_id)');

        $targetPoolId = $this->targetPinrangPoolId();
        if ($targetPoolId > 0) {
            DB::table('customers')->update(['pool_id' => $targetPoolId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'pool_id')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropIndex('idx_customers_pool_id');
            $table->dropColumn('pool_id');
        });
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
