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

        if (Schema::hasIndex('units', 'units_nopol_unique', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->dropUnique('units_nopol_unique');
            });
        }

        if (! Schema::hasIndex('units', 'uniq_units_tenant_nopol', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->unique(['tenant_id', 'nopol'], 'uniq_units_tenant_nopol');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'tenant_id')) {
            return;
        }

        if (Schema::hasIndex('units', 'uniq_units_tenant_nopol', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->dropUnique('uniq_units_tenant_nopol');
            });
        }

        if (! Schema::hasIndex('units', 'units_nopol_unique', 'unique')) {
            Schema::table('units', function (Blueprint $table): void {
                $table->unique('nopol', 'units_nopol_unique');
            });
        }
    }
};
