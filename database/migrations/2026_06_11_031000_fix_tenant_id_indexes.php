<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix: drop improperly named indexes from previous failed migration
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            try {
                $badIndex = '"master_carter_idx_master_carter_tenant_id_index"';
                // Try to drop the bad index that PostgreSQL created from wrong syntax
                DB::statement("DROP INDEX IF EXISTS {$badIndex}");
            } catch (\Throwable) {
                // Index might not exist — ignore
            }
        }

        // Re-add indexes correctly
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            $indexName = "idx_{$table}_tenant_id";
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) use ($indexName): void {
                    $t->index('tenant_id', $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            $indexName = "idx_{$table}_tenant_id";
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) use ($indexName): void {
                    $t->dropIndex($indexName);
                });
            }
        }
    }
};
