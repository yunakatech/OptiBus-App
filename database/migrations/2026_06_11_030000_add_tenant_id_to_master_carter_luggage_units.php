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

        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            $currentTable = $table;
            if (Schema::hasTable($currentTable) && ! Schema::hasColumn($currentTable, 'tenant_id')) {
                Schema::table($currentTable, function (Blueprint $t) use ($currentTable): void {
                    $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $t->index("idx_{$currentTable}_tenant_id");
                });
                DB::table($currentTable)->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            }
        }
    }

    public function down(): void
    {
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('tenant_id');
                });
            }
        }
    }
};
