<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('bookings', 'idx_bookings_latest_departure', ['tanggal', 'jam', 'id'], 'tanggal DESC, jam DESC, id DESC');
        $this->addIndex('bookings', 'idx_bookings_report_payment_date', ['tanggal', 'pembayaran', 'status'], 'tanggal, pembayaran, status');
        $this->addPostgresPartialIndex('bookings', 'idx_bookings_active_report_trip', ['tanggal', 'rute', 'jam', 'unit', 'pembayaran', 'status'], 'tanggal, rute, jam, unit, pembayaran', "status <> 'canceled'");

        $this->addIndex('customers', 'idx_customers_name', ['name'], 'name');
        $this->addIndex('customers', 'idx_customers_pickup_point', ['pickup_point'], 'pickup_point');

        $this->addIndex('schedules', 'idx_schedules_route_id_dow_jam', ['route_id', 'dow', 'jam'], 'route_id, dow, jam');
        $this->addIndex('schedules', 'idx_schedules_dow_jam', ['dow', 'jam'], 'dow, jam');
        $this->addIndex('schedules', 'idx_schedules_unit_id', ['unit_id'], 'unit_id');
        $this->addIndex('schedule_units', 'idx_schedule_units_schedule_unit', ['schedule_id', 'unit_no'], 'schedule_id, unit_no');

        $this->addIndex('segments', 'idx_segments_route_id_rute', ['route_id', 'rute'], 'route_id, rute');
        $this->addIndex('routes', 'idx_routes_name', ['name'], 'name');

        $this->addIndex('trip_assignments', 'idx_trip_assignments_status_tanggal', ['status', 'tanggal'], 'status, tanggal');
        $this->addIndex('trip_assignments', 'idx_trip_assignments_driver_tanggal_jam', ['driver_id', 'tanggal', 'jam'], 'driver_id, tanggal, jam');
        $this->addIndex('trip_assignments', 'idx_trip_assignments_armada_tanggal', ['armada_id', 'tanggal'], 'armada_id, tanggal');
        $this->addIndex('trip_assignments', 'idx_trip_assignments_armada_nopol_tanggal', ['armada_nopol', 'tanggal'], 'armada_nopol, tanggal');

        $this->addIndex('charters', 'idx_charters_status_start_id', ['status', 'start_date', 'id'], 'status, start_date DESC, id DESC');
        $this->addIndex('charters', 'idx_charters_payment_start', ['payment_status', 'start_date'], 'payment_status, start_date DESC');
        $this->addIndex('charters', 'idx_charters_bop_start', ['bop_status', 'start_date'], 'bop_status, start_date DESC');
        $this->addIndex('charters', 'idx_charters_unit_start', ['unit_id', 'start_date'], 'unit_id, start_date DESC');
        $this->addIndex('charters', 'idx_charters_armada_start', ['armada_id', 'start_date'], 'armada_id, start_date DESC');
        $this->addIndex('charters', 'idx_charters_armada_nopol_start', ['armada_nopol', 'start_date'], 'armada_nopol, start_date DESC');

        $this->addIndex('luggages', 'idx_luggages_created_id', ['created_at', 'id'], 'created_at DESC, id DESC');
        $this->addIndex('luggages', 'idx_luggages_payment_created', ['payment_status', 'created_at'], 'payment_status, created_at DESC');
        $this->addIndex('luggages', 'idx_luggages_status_payment_created', ['status', 'payment_status', 'created_at'], 'status, payment_status, created_at DESC');
        $this->addIndex('luggages', 'idx_luggages_kode_resi', ['kode_resi'], 'kode_resi');
        $this->addIndex('luggages', 'idx_luggages_tanggal_status', ['tanggal', 'status'], 'tanggal, status');
        $this->addIndex('luggages', 'idx_luggages_sender_phone', ['sender_phone'], 'sender_phone');
        $this->addIndex('luggages', 'idx_luggages_receiver_phone', ['receiver_phone'], 'receiver_phone');

        $this->addIndex('customer_bagasi', 'idx_customer_bagasi_nama', ['nama'], 'nama');
        $this->addIndex('customer_bagasi', 'idx_customer_bagasi_tipe_nama', ['tipe', 'nama'], 'tipe, nama');
        $this->addIndex('customer_charter', 'idx_customer_charter_nama', ['nama'], 'nama');
        $this->addIndex('customer_charter', 'idx_customer_charter_company', ['company'], 'company');
        $this->addIndex('master_carter', 'idx_master_carter_name', ['name'], 'name');
        $this->addIndex('armadas', 'idx_armadas_kategori_ac_nopol', ['kategori', 'ac_type', 'nopol'], 'kategori, ac_type, nopol');
        $this->addIndex('units', 'idx_units_category_status_nopol', ['category', 'status', 'nopol'], 'category, status, nopol');
    }

    public function down(): void
    {
        // No-op by design: these indexes are additive and safe to leave in place.
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
    private function addPostgresPartialIndex(string $table, string $name, array $columns, string $definition, string $where): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! $this->tableHasColumns($table, $columns)) {
            return;
        }

        DB::statement("CREATE INDEX IF NOT EXISTS {$name} ON {$table} ({$definition}) WHERE {$where}");
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
