<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Schema::table($table, function (Blueprint $schema) use ($column, $scopedIndex): void {
            $schema->unique(['tenant_id', 'pool_id', $column], $scopedIndex);
        });
    }

    private function restoreLegacyUnique(string $table, string $column, string $scopedIndex): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropUniqueIfExists($table, $scopedIndex);

        Schema::table($table, function (Blueprint $schema) use ($column, $table): void {
            $schema->unique($column, $this->legacyUniqueName($table, $column));
        });
    }

    private function dropUniqueIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $schema) use ($index): void {
                $schema->dropUnique($index);
            });
        } catch (Throwable) {
            // Ignore missing indexes and legacy drivers that cannot introspect the current state.
        }
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
