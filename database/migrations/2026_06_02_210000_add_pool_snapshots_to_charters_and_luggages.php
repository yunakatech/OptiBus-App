<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('charters')) {
            Schema::table('charters', function (Blueprint $table): void {
                if (! Schema::hasColumn('charters', 'pool_id')) {
                    $table->unsignedBigInteger('pool_id')->nullable()->after('id');
                }

                if (! Schema::hasColumn('charters', 'master_carter_id')) {
                    $table->unsignedBigInteger('master_carter_id')->nullable()->after('pool_id');
                }
            });

            DB::statement('CREATE INDEX IF NOT EXISTS idx_charters_pool_start ON charters (pool_id, start_date)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_charters_master_carter ON charters (master_carter_id)');
        }

        if (Schema::hasTable('luggages')) {
            Schema::table('luggages', function (Blueprint $table): void {
                if (! Schema::hasColumn('luggages', 'pool_id')) {
                    $table->unsignedBigInteger('pool_id')->nullable()->after('id');
                }
            });

            DB::statement('CREATE INDEX IF NOT EXISTS idx_luggages_pool_created ON luggages (pool_id, created_at)');
        }

        $this->backfillLuggagePools();
        $this->backfillCharterPools();
    }

    public function down(): void
    {
        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'pool_id')) {
            Schema::table('luggages', function (Blueprint $table): void {
                $table->dropColumn('pool_id');
            });
        }

        if (Schema::hasTable('charters')) {
            Schema::table('charters', function (Blueprint $table): void {
                if (Schema::hasColumn('charters', 'master_carter_id')) {
                    $table->dropColumn('master_carter_id');
                }

                if (Schema::hasColumn('charters', 'pool_id')) {
                    $table->dropColumn('pool_id');
                }
            });
        }
    }

    private function backfillLuggagePools(): void
    {
        if (
            ! Schema::hasTable('luggages')
            || ! Schema::hasTable('pool_route')
            || ! Schema::hasColumn('luggages', 'pool_id')
            || ! Schema::hasColumn('luggages', 'rute_id')
        ) {
            return;
        }

        $poolByRoute = DB::table('pool_route')
            ->pluck('pool_id', 'route_id')
            ->mapWithKeys(static fn ($poolId, $routeId): array => [(int) $routeId => (int) $poolId])
            ->all();

        if ($poolByRoute === []) {
            return;
        }

        DB::table('luggages')
            ->whereNull('pool_id')
            ->whereNotNull('rute_id')
            ->select(['id', 'rute_id'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($poolByRoute): void {
                foreach ($rows as $row) {
                    $poolId = (int) ($poolByRoute[(int) ($row->rute_id ?? 0)] ?? 0);

                    if ($poolId > 0) {
                        DB::table('luggages')->where('id', (int) $row->id)->update(['pool_id' => $poolId]);
                    }
                }
            });
    }

    private function backfillCharterPools(): void
    {
        if (
            ! Schema::hasTable('charters')
            || ! Schema::hasTable('pool_route')
            || ! Schema::hasTable('routes')
            || ! Schema::hasColumn('charters', 'pool_id')
        ) {
            return;
        }

        $poolByLabel = [];
        $routes = DB::table('pool_route as pr')
            ->join('routes as r', 'pr.route_id', '=', 'r.id')
            ->get(['pr.pool_id', 'r.name', 'r.origin', 'r.destination']);

        foreach ($routes as $route) {
            foreach (['name', 'origin', 'destination'] as $field) {
                $label = $this->normalizeLabel((string) ($route->{$field} ?? ''));

                if ($label !== '') {
                    $poolByLabel[$label] = (int) ($route->pool_id ?? 0);
                }
            }
        }

        if ($poolByLabel === []) {
            return;
        }

        DB::table('charters')
            ->whereNull('pool_id')
            ->select(['id', 'pickup_point', 'drop_point'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($poolByLabel): void {
                foreach ($rows as $row) {
                    $poolId = (int) ($poolByLabel[$this->normalizeLabel((string) ($row->pickup_point ?? ''))] ?? 0);

                    if ($poolId <= 0) {
                        $poolId = (int) ($poolByLabel[$this->normalizeLabel((string) ($row->drop_point ?? ''))] ?? 0);
                    }

                    if ($poolId > 0) {
                        DB::table('charters')->where('id', (int) $row->id)->update(['pool_id' => $poolId]);
                    }
                }
            });
    }

    private function normalizeLabel(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? '';
    }
};

