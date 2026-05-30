<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function changeStatusColumn(string $table, int $length, ?string $default, string $driver): void
    {
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN status TYPE VARCHAR({$length})");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN status SET NOT NULL");

            if ($default === null) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN status DROP DEFAULT");
            } else {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN status SET DEFAULT ".DB::getPdo()->quote($default));
            }

            return;
        }

        $definition = "VARCHAR({$length}) NOT NULL";
        if ($default !== null) {
            $definition .= ' DEFAULT '.DB::getPdo()->quote($default);
        }

        DB::statement("ALTER TABLE {$table} MODIFY status {$definition}");
    }

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'status')) {
            $this->changeStatusColumn('luggages', 40, 'Barang sudah diterima', $driver);

            if (Schema::hasColumn('luggages', 'trip_assignment_id')) {
                DB::table('luggages')
                    ->whereNotNull('trip_assignment_id')
                    ->whereIn('status', ['pending', 'done', 'diterima', 'active', 'sent', 'Barang sudah diterima'])
                    ->update(['status' => 'Barang sudah dipickup']);
            }

            DB::table('luggages')
                ->whereIn('status', ['pending', 'done', 'diterima'])
                ->update(['status' => 'Barang sudah diterima']);

            DB::table('luggages')
                ->whereIn('status', ['active', 'sent'])
                ->update(['status' => 'Barang sudah dipickup']);
        }

        if (Schema::hasTable('bagasi_logs') && Schema::hasColumn('bagasi_logs', 'status')) {
            $this->changeStatusColumn('bagasi_logs', 40, null, $driver);

            DB::table('bagasi_logs')
                ->whereIn('status', ['pending', 'done', 'diterima'])
                ->update(['status' => 'Barang sudah diterima']);

            DB::table('bagasi_logs')
                ->whereIn('status', ['active', 'sent'])
                ->update(['status' => 'Barang sudah dipickup']);
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (Schema::hasTable('bagasi_logs') && Schema::hasColumn('bagasi_logs', 'status')) {
            DB::table('bagasi_logs')
                ->where('status', 'Barang sudah diterima')
                ->update(['status' => 'pending']);

            DB::table('bagasi_logs')
                ->where('status', 'Barang sudah dipickup')
                ->update(['status' => 'active']);

            DB::table('bagasi_logs')
                ->where('status', 'Barang sudah tiba')
                ->update(['status' => 'done']);

            $this->changeStatusColumn('bagasi_logs', 30, null, $driver);
        }

        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'status')) {
            DB::table('luggages')
                ->where('status', 'Barang sudah diterima')
                ->update(['status' => 'pending']);

            DB::table('luggages')
                ->where('status', 'Barang sudah dipickup')
                ->update(['status' => 'active']);

            DB::table('luggages')
                ->where('status', 'Barang sudah tiba')
                ->update(['status' => 'done']);

            $this->changeStatusColumn('luggages', 20, 'pending', $driver);
        }
    }
};
