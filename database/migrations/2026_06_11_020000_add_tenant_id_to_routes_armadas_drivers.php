<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultTenantId = DB::table('tenants')->where('slug', 'qbus-default')->value('id') ?? 1;

        // --- routes ---
        if (Schema::hasTable('routes') && ! Schema::hasColumn('routes', 'tenant_id')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'idx_routes_tenant_id');
            });
            DB::table('routes')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }

        // --- armadas ---
        if (Schema::hasTable('armadas') && ! Schema::hasColumn('armadas', 'tenant_id')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'idx_armadas_tenant_id');
            });
            DB::table('armadas')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }

        // --- drivers ---
        if (Schema::hasTable('drivers') && ! Schema::hasColumn('drivers', 'tenant_id')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'idx_drivers_tenant_id');
            });
            DB::table('drivers')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }
    }

    public function down(): void
    {
        foreach (['routes', 'armadas', 'drivers'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('tenant_id');
                });
            }
        }
    }
};
