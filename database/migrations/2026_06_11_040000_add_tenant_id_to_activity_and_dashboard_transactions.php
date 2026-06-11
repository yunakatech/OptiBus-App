<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transaction tables used by the dashboard need an explicit tenant fence.
     * Legacy rows are backfilled only when the tenant can be derived safely.
     */
    public function up(): void
    {
        foreach (['activity_logs', 'bookings', 'trip_assignments', 'charters', 'luggages'] as $table) {
            $this->addTenantColumn($table);
        }

        $this->backfillActivityLogTenants();
        $this->backfillBookingTenants();
        $this->backfillTripAssignmentTenants();
        $this->backfillCharterTenants();
        $this->backfillLuggageTenants();
    }

    public function down(): void
    {
        foreach (['activity_logs', 'bookings', 'trip_assignments', 'charters', 'luggages'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }
    }

    private function addTenantColumn(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $schema): void {
            $schema->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        DB::statement("CREATE INDEX IF NOT EXISTS idx_{$table}_tenant_id ON {$table} (tenant_id)");
    }

    private function backfillActivityLogTenants(): void
    {
        if (
            ! Schema::hasTable('activity_logs')
            || ! Schema::hasColumn('activity_logs', 'tenant_id')
            || ! Schema::hasColumn('activity_logs', 'extra')
        ) {
            return;
        }

        DB::table('activity_logs')
            ->whereNull('tenant_id')
            ->orderBy('id')
            ->select(['id', 'extra'])
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $extra = $this->jsonObject($row->extra ?? null);
                    $tenantId = $this->tenantIdFromExtra($extra);

                    if ($tenantId > 0) {
                        DB::table('activity_logs')->where('id', (int) $row->id)->update(['tenant_id' => $tenantId]);
                    }
                }
            });
    }

    private function backfillBookingTenants(): void
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasColumn('bookings', 'tenant_id')) {
            return;
        }

        $this->backfillTenantFromRelatedTable('bookings', 'created_by_user_id', 'users');
        $this->backfillTenantFromRelatedTable('bookings', 'route_id', 'routes');

        $this->backfillTenantByUniqueRouteLabel('bookings', 'rute');
    }

    private function backfillTripAssignmentTenants(): void
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasColumn('trip_assignments', 'tenant_id')) {
            return;
        }

        $this->backfillTenantFromRelatedTable('trip_assignments', 'driver_id', 'drivers');

        $this->backfillTenantByUniqueRouteLabel('trip_assignments', 'rute');
    }

    private function backfillCharterTenants(): void
    {
        if (! Schema::hasTable('charters') || ! Schema::hasColumn('charters', 'tenant_id')) {
            return;
        }

        $this->backfillTenantFromRelatedTable('charters', 'pool_id', 'pools');
        $this->backfillTenantFromRelatedTable('charters', 'master_carter_id', 'master_carter');

        $this->backfillTenantByUniqueRouteLabel('charters', 'pickup_point');
        $this->backfillTenantByUniqueRouteLabel('charters', 'drop_point');
    }

    private function backfillLuggageTenants(): void
    {
        if (! Schema::hasTable('luggages') || ! Schema::hasColumn('luggages', 'tenant_id')) {
            return;
        }

        $this->backfillTenantFromRelatedTable('luggages', 'pool_id', 'pools');
        $this->backfillTenantFromRelatedTable('luggages', 'rute_id', 'routes');

        $this->backfillTenantByUniqueRouteLabel('luggages', 'rute');
    }

    private function backfillTenantByUniqueRouteLabel(string $table, string $column): void
    {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'tenant_id')
            || ! Schema::hasColumn($table, $column)
            || ! Schema::hasTable('routes')
            || ! Schema::hasColumn('routes', 'tenant_id')
        ) {
            return;
        }

        $tenantByLabel = $this->uniqueTenantByRouteLabel();
        if ($tenantByLabel === []) {
            return;
        }

        DB::table($table)
            ->whereNull('tenant_id')
            ->whereNotNull($column)
            ->orderBy('id')
            ->select(['id', $column])
            ->chunkById(200, function ($rows) use ($table, $column, $tenantByLabel): void {
                foreach ($rows as $row) {
                    $tenantId = (int) ($tenantByLabel[$this->normalizeRouteLabel((string) ($row->{$column} ?? ''))] ?? 0);

                    if ($tenantId > 0) {
                        DB::table($table)->where('id', (int) $row->id)->update(['tenant_id' => $tenantId]);
                    }
                }
            });
    }

    private function backfillTenantFromRelatedTable(string $table, string $localColumn, string $relatedTable): void
    {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'tenant_id')
            || ! Schema::hasColumn($table, $localColumn)
            || ! Schema::hasTable($relatedTable)
            || ! Schema::hasColumn($relatedTable, 'tenant_id')
        ) {
            return;
        }

        $tenantById = DB::table($relatedTable)
            ->whereNotNull('tenant_id')
            ->pluck('tenant_id', 'id')
            ->mapWithKeys(static fn ($tenantId, $id): array => [(int) $id => (int) $tenantId])
            ->all();

        if ($tenantById === []) {
            return;
        }

        DB::table($table)
            ->whereNull('tenant_id')
            ->whereNotNull($localColumn)
            ->orderBy('id')
            ->select(['id', $localColumn])
            ->chunkById(200, function ($rows) use ($table, $localColumn, $tenantById): void {
                foreach ($rows as $row) {
                    $tenantId = (int) ($tenantById[(int) ($row->{$localColumn} ?? 0)] ?? 0);

                    if ($tenantId > 0) {
                        DB::table($table)->where('id', (int) $row->id)->update(['tenant_id' => $tenantId]);
                    }
                }
            });
    }

    /**
     * @return array<string, int>
     */
    private function uniqueTenantByRouteLabel(): array
    {
        $map = [];
        $ambiguous = [];

        foreach (DB::table('routes')->get(['tenant_id', 'name', 'origin', 'destination']) as $route) {
            $tenantId = (int) ($route->tenant_id ?? 0);
            if ($tenantId <= 0) {
                continue;
            }

            $labels = [
                (string) ($route->name ?? ''),
                (string) ($route->origin ?? ''),
                (string) ($route->destination ?? ''),
            ];

            $origin = trim((string) ($route->origin ?? ''));
            $destination = trim((string) ($route->destination ?? ''));
            if ($origin !== '' && $destination !== '') {
                $labels[] = $origin.' - '.$destination;
            }

            foreach ($labels as $label) {
                $key = $this->normalizeRouteLabel($label);
                if ($key === '') {
                    continue;
                }

                if (isset($map[$key]) && $map[$key] !== $tenantId) {
                    $ambiguous[$key] = true;
                    continue;
                }

                $map[$key] = $tenantId;
            }
        }

        foreach (array_keys($ambiguous) as $key) {
            unset($map[$key]);
        }

        return $map;
    }

    private function tenantIdFromExtra(array $extra): int
    {
        $tenantId = $this->firstPositiveInt($extra['tenant_id'] ?? null);
        if ($tenantId > 0) {
            return $tenantId;
        }

        $userId = $this->firstPositiveInt($extra['user_id'] ?? null);
        if ($userId > 0 && Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            return (int) (DB::table('users')->where('id', $userId)->value('tenant_id') ?? 0);
        }

        $poolId = $this->firstPositiveInt($extra['pool_id'] ?? ($extra['pool_ids'] ?? null));
        if ($poolId > 0 && Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
            return (int) (DB::table('pools')->where('id', $poolId)->value('tenant_id') ?? 0);
        }

        $routeId = $this->firstPositiveInt($extra['route_id'] ?? ($extra['rute_id'] ?? null));
        if ($routeId > 0 && Schema::hasTable('routes') && Schema::hasColumn('routes', 'tenant_id')) {
            return (int) (DB::table('routes')->where('id', $routeId)->value('tenant_id') ?? 0);
        }

        return 0;
    }

    private function firstPositiveInt(mixed $value): int
    {
        $values = is_array($value) ? $value : [$value];
        foreach ($values as $item) {
            $id = (int) $item;
            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }

    private function jsonObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeRouteLabel(string $value): string
    {
        $normalized = mb_strtoupper(trim($value));
        $normalized = str_replace(['=>', '->', 'TO'], '-', $normalized);

        return preg_replace('/\s+/', '', $normalized) ?? '';
    }
};
