<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedules')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_schedules_dow_rute_jam ON schedules (dow, rute, jam)');
        }

        if (Schema::hasTable('trip_assignments')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_trip_assignments_tanggal_jam_unit ON trip_assignments (tanggal, jam, unit)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_trip_assignments_tanggal_jam_driver ON trip_assignments (tanggal, jam, driver_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_trip_assignments_rute_tanggal_jam ON trip_assignments (rute, tanggal, jam)');
        }

        if (Schema::hasTable('bookings')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_tanggal_jam_unit_status ON bookings (tanggal, jam, unit, status)');
        }

        if (Schema::hasTable('segments')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_segments_rute_harga ON segments (rute, harga)');
        }
    }

    public function down(): void
    {
        // Intentionally no-op: dropping conditional indexes differs across drivers.
    }
};

