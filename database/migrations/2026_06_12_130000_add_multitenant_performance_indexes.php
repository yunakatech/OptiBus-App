<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('activity_logs', 'idx_activity_logs_tenant_created_id', ['tenant_id', 'created_at', 'id'], 'tenant_id, created_at DESC, id DESC');
        $this->addIndex('activity_logs', 'idx_activity_logs_created_id', ['created_at', 'id'], 'created_at DESC, id DESC');

        $this->addIndex('bookings', 'idx_bookings_tenant_date_status', ['tenant_id', 'tanggal', 'status'], 'tenant_id, tanggal, status');
        $this->addIndex('bookings', 'idx_bookings_tenant_route_trip', ['tenant_id', 'route_id', 'tanggal', 'jam', 'unit'], 'tenant_id, route_id, tanggal, jam, unit');
        $this->addIndex('bookings', 'idx_bookings_tenant_rute_trip', ['tenant_id', 'rute', 'tanggal', 'jam', 'unit'], 'tenant_id, rute, tanggal, jam, unit');

        $this->addIndex('charters', 'idx_charters_tenant_start_status', ['tenant_id', 'start_date', 'status'], 'tenant_id, start_date DESC, status');
        $this->addIndex('charters', 'idx_charters_pool_start_status', ['pool_id', 'start_date', 'status'], 'pool_id, start_date DESC, status');

        $this->addIndex('luggages', 'idx_luggages_tenant_created_status_payment', ['tenant_id', 'created_at', 'status', 'payment_status'], 'tenant_id, created_at DESC, status, payment_status');
        $this->addIndex('luggages', 'idx_luggages_pool_created_status', ['pool_id', 'created_at', 'status'], 'pool_id, created_at DESC, status');

        $this->addIndex('invoice_subscriptions', 'idx_invoice_subs_status_due', ['status', 'due_date'], 'status, due_date');
        $this->addIndex('invoice_subscriptions', 'idx_invoice_subs_tenant_status_due', ['tenant_id', 'status', 'due_date'], 'tenant_id, status, due_date');

        $this->addIndex('subscriptions', 'idx_subscriptions_tenant_status_created', ['tenant_id', 'status', 'created_at'], 'tenant_id, status, created_at DESC');
        $this->addIndex('pools', 'idx_pools_tenant_status_name', ['tenant_id', 'status', 'name'], 'tenant_id, status, name');
        $this->addIndex('pool_user', 'idx_pool_user_user_pool', ['user_id', 'pool_id'], 'user_id, pool_id');
    }

    public function down(): void
    {
        // Additive performance indexes are safe to leave in place.
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function addIndex(string $table, string $name, array $columns, string $definition): void
    {
        if (! $this->tableHasColumns($table, $columns)) {
            return;
        }

        DB::statement("CREATE INDEX IF NOT EXISTS {$name} ON {$table} ({$definition})");
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function tableHasColumns(string $table, array $columns): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
};
