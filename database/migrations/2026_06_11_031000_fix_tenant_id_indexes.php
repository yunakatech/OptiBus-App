<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $indexName = "idx_{$table}_tenant_id";

            // Use PostgreSQL's IF NOT EXISTS to avoid transaction abort
            // Schema builder throws on duplicate index, aborting the transaction
            try {
                DB::statement('CREATE INDEX IF NOT EXISTS "'.$indexName.'" ON "'.$table.'" ("tenant_id")');
            } catch (\Throwable) {
                // Non-PostgreSQL driver may not support IF NOT EXISTS
            }
        }
    }

    public function down(): void
    {
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            try {
                DB::statement('DROP INDEX IF EXISTS "idx_'.$table.'_tenant_id"');
            } catch (\Throwable) {
                // May not exist
            }
        }
    }
};
