<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceLegacyUnique('customers', 'phone', 'uniq_customers_tenant_pool_phone');
        $this->replaceLegacyUnique('customer_bagasi', 'no_hp', 'uniq_customer_bagasi_tenant_pool_no_hp');
        $this->replaceLegacyUnique('customer_charter', 'no_hp', 'uniq_customer_charter_tenant_pool_no_hp');
    }

    public function down(): void
    {
        $this->restoreLegacyUnique('customers', 'phone', 'uniq_customers_tenant_pool_phone');
        $this->restoreLegacyUnique('customer_bagasi', 'no_hp', 'uniq_customer_bagasi_tenant_pool_no_hp');
        $this->restoreLegacyUnique('customer_charter', 'no_hp', 'uniq_customer_charter_tenant_pool_no_hp');
    }

    private function replaceLegacyUnique(string $table, string $column, string $scopedIndex): void
    {
        if (! $this->supportsScopedUnique($table, $column)) {
            return;
        }

        $legacyIndex = $this->legacyUniqueName($table, $column);

        $this->dropUniqueIfExists($table, $scopedIndex);
        $this->dropUniqueIfExists($table, $legacyIndex);
        $this->cleanupScopedDuplicates($table, $column);

        $this->addUniqueIfMissing($table, ['tenant_id', 'pool_id', $column], $scopedIndex);
    }

    private function restoreLegacyUnique(string $table, string $column, string $scopedIndex): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropUniqueIfExists($table, $scopedIndex);
        $this->addUniqueIfMissing($table, [$column], $this->legacyUniqueName($table, $column));
    }

    private function dropUniqueIfExists(string $table, string $index): void
    {
        if (! Schema::hasIndex($table, $index, 'unique')) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($index): void {
            $schema->dropUnique($index);
        });
    }

    private function addUniqueIfMissing(string $table, array $columns, string $index): void
    {
        if (Schema::hasIndex($table, $index, 'unique')) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($columns, $index): void {
            $schema->unique($columns, $index);
        });
    }

    private function cleanupScopedDuplicates(string $table, string $column): void
    {
        if (! $this->supportsScopedUnique($table, $column)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select([
                'tenant_id',
                'pool_id',
                $column,
                DB::raw('COUNT(*) as total'),
            ])
            ->whereNotNull('tenant_id')
            ->whereNotNull('pool_id')
            ->whereNotNull($column)
            ->whereRaw("TRIM(COALESCE($column, '')) <> ''")
            ->groupBy('tenant_id', 'pool_id', $column)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $rows = DB::table($table)
                ->where('tenant_id', $duplicate->tenant_id)
                ->where('pool_id', $duplicate->pool_id)
                ->where($column, $duplicate->{$column})
                ->orderBy('id')
                ->get();

            if ($rows->count() < 2) {
                continue;
            }

            $keepId = null;
            $keepScore = -1;

            foreach ($rows as $row) {
                $score = 0;
                foreach (get_object_vars($row) as $key => $value) {
                    if (in_array($key, ['id', 'tenant_id', 'pool_id', $column, 'created_at', 'updated_at'], true)) {
                        continue;
                    }

                    if ($this->isMeaningfulValue($value)) {
                        $score += 1;
                    }
                }

                $rowId = (int) ($row->id ?? 0);
                if ($rowId <= 0) {
                    continue;
                }

                if ($score > $keepScore || ($score === $keepScore && ($keepId === null || $rowId < $keepId))) {
                    $keepId = $rowId;
                    $keepScore = $score;
                }
            }

            if ($keepId === null) {
                continue;
            }

            $deleteIds = $rows
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->filter(static fn (int $id) => $id > 0 && $id !== $keepId)
                ->values()
                ->all();

            if ($deleteIds !== []) {
                DB::table($table)->whereIn('id', $deleteIds)->delete();
            }
        }
    }

    private function isMeaningfulValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function supportsScopedUnique(string $table, string $column): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, $column)
            && Schema::hasColumn($table, 'tenant_id')
            && Schema::hasColumn($table, 'pool_id');
    }

    private function legacyUniqueName(string $table, string $column): string
    {
        return "{$table}_{$column}_unique";
    }
};
