<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On PostgreSQL, the previous failed migration actually created the index
        // despite the error. On SQLite local, the index might not exist.
        // Safely attempt creation in a way that won't fail if already present.
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            $indexName = "idx_{$table}_tenant_id";
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($indexName): void {
                        $t->index('tenant_id', $indexName);
                    });
                } catch (\Throwable) {
                    // Index may already exist — that's fine
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['master_carter', 'luggage_services', 'units'] as $table) {
            $indexName = "idx_{$table}_tenant_id";
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($indexName): void {
                        $t->dropIndex($indexName);
                    });
                } catch (\Throwable) {
                    // May not exist
                }
            }
        }
    }
};
