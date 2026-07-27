<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        $hasOldColumn = Schema::hasColumn('units', 'nopol');
        $hasNewColumn = Schema::hasColumn('units', 'nama_kategori');

        if ($hasOldColumn && ! $hasNewColumn) {
            DB::statement('ALTER TABLE units RENAME COLUMN nopol TO nama_kategori');
            $hasNewColumn = true;
            $hasOldColumn = false;
        } elseif ($hasOldColumn && $hasNewColumn) {
            DB::statement('UPDATE units SET nama_kategori = COALESCE(NULLIF(nama_kategori, \'\'), nopol)');
            Schema::table('units', function (Blueprint $table): void {
                $table->dropColumn('nopol');
            });
            $hasOldColumn = false;
        }

        if ($hasOldColumn) {
            return;
        }

        if (Schema::hasIndex('units', 'idx_units_status_nopol', 'index')) {
            DB::statement('DROP INDEX IF EXISTS idx_units_status_nopol');
        }
        if (Schema::hasIndex('units', 'idx_units_category_status_nopol', 'index')) {
            DB::statement('DROP INDEX IF EXISTS idx_units_category_status_nopol');
        }

        if ($hasNewColumn) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_units_status_nama_kategori ON units (status, nama_kategori)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_units_category_status_nama_kategori ON units (category, status, nama_kategori)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        $hasOldColumn = Schema::hasColumn('units', 'nopol');
        $hasNewColumn = Schema::hasColumn('units', 'nama_kategori');

        if ($hasNewColumn && ! $hasOldColumn) {
            DB::statement('ALTER TABLE units RENAME COLUMN nama_kategori TO nopol');
            $hasOldColumn = true;
            $hasNewColumn = false;
        } elseif ($hasNewColumn && $hasOldColumn) {
            DB::statement('UPDATE units SET nopol = COALESCE(NULLIF(nopol, \'\'), nama_kategori)');
            Schema::table('units', function (Blueprint $table): void {
                $table->dropColumn('nama_kategori');
            });
            $hasNewColumn = false;
        }

        if (Schema::hasIndex('units', 'idx_units_status_nama_kategori', 'index')) {
            DB::statement('DROP INDEX IF EXISTS idx_units_status_nama_kategori');
        }
        if (Schema::hasIndex('units', 'idx_units_category_status_nama_kategori', 'index')) {
            DB::statement('DROP INDEX IF EXISTS idx_units_category_status_nama_kategori');
        }

        if ($hasOldColumn) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_units_status_nopol ON units (status, nopol)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_units_category_status_nopol ON units (category, status, nopol)');
        }
    }
};
