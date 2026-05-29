<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        // Old index depends on nama_kendaraan, drop it first.
        DB::statement('DROP INDEX IF EXISTS idx_armadas_kategori_nama');

        if (Schema::hasColumn('armadas', 'nama_kendaraan')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->dropColumn('nama_kendaraan');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        if (! Schema::hasColumn('armadas', 'nama_kendaraan')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->string('nama_kendaraan', 120)->nullable()->after('id');
            });
        }
    }
};
