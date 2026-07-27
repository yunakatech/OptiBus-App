<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'tenant_id')) {
            return;
        }

        foreach (['units_nama_kategori_unique', 'units_nopol_unique'] as $indexName) {
            if (Schema::hasIndex('units', $indexName, 'unique')) {
                Schema::table('units', function (Blueprint $table) use ($indexName): void {
                    $table->dropUnique($indexName);
                });
            }
        }

        if (! Schema::hasIndex('units', 'uniq_units_tenant_nama_kategori', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->unique(['tenant_id', 'nama_kategori'], 'uniq_units_tenant_nama_kategori');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'tenant_id')) {
            return;
        }

        if (Schema::hasIndex('units', 'uniq_units_tenant_nama_kategori', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->dropUnique('uniq_units_tenant_nama_kategori');
            });
        }

        foreach (['units_nama_kategori_unique', 'units_nopol_unique'] as $indexName) {
            if (Schema::hasIndex('units', $indexName, 'unique')) {
                Schema::table('units', function (Blueprint $table) use ($indexName): void {
                    $table->dropUnique($indexName);
                });
            }
        }

        if (! Schema::hasIndex('units', 'units_nama_kategori_unique', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->unique('nama_kategori', 'units_nama_kategori_unique');
            });
        }
    }
};
