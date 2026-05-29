<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (Schema::hasTable('luggages') && Schema::hasColumn('luggages', 'status')) {
            if ($driver !== 'sqlite') {
                DB::statement("ALTER TABLE luggages MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Barang sudah diterima'");
            }

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
            if ($driver !== 'sqlite') {
                DB::statement('ALTER TABLE bagasi_logs MODIFY status VARCHAR(40) NOT NULL');
            }

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

            if ($driver !== 'sqlite') {
                DB::statement('ALTER TABLE bagasi_logs MODIFY status VARCHAR(30) NOT NULL');
            }
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

            if ($driver !== 'sqlite') {
                DB::statement("ALTER TABLE luggages MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
        }
    }
};
