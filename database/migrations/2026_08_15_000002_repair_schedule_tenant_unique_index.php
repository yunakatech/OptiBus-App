<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'tenant_id')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS "uniq_schedules_rute_dow_jam"');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS "uniq_schedules_tenant_rute_dow_jam" ON "schedules" ("tenant_id", "rute", "dow", "jam")');

            return;
        }

        if (Schema::hasIndex('schedules', 'uniq_schedules_rute_dow_jam', 'unique')) {
            Schema::table('schedules', function (Blueprint $table): void {
                $table->dropUnique('uniq_schedules_rute_dow_jam');
            });
        }

        if (! Schema::hasIndex('schedules', 'uniq_schedules_tenant_rute_dow_jam', 'unique')) {
            Schema::table('schedules', function (Blueprint $table): void {
                $table->unique(
                    ['tenant_id', 'rute', 'dow', 'jam'],
                    'uniq_schedules_tenant_rute_dow_jam',
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS "uniq_schedules_tenant_rute_dow_jam"');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS "uniq_schedules_rute_dow_jam" ON "schedules" ("rute", "dow", "jam")');

            return;
        }

        if (Schema::hasIndex('schedules', 'uniq_schedules_tenant_rute_dow_jam', 'unique')) {
            Schema::table('schedules', function (Blueprint $table): void {
                $table->dropUnique('uniq_schedules_tenant_rute_dow_jam');
            });
        }
    }
};
