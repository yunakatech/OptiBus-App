<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Add tenant_id to pools ---
        if (Schema::hasTable('pools') && ! Schema::hasColumn('pools', 'tenant_id')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'idx_pools_tenant_id');
            });

            // Backfill all existing pools to the default tenant
            $defaultTenantId = DB::table('tenants')->where('slug', 'qbus-default')->value('id');
            if ($defaultTenantId) {
                DB::table('pools')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            }

            // Now make it NOT NULL (after backfill)
            Schema::table('pools', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            });
        }

        // --- Add tenant_id to users ---
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'idx_users_tenant_id');
            });

            // Backfill non-super-admin users to the default tenant
            $defaultTenantId = DB::table('tenants')->where('slug', 'qbus-default')->value('id');
            if ($defaultTenantId) {
                DB::table('users')
                    ->whereNull('tenant_id')
                    ->where(function ($q) {
                        $q->where('is_super_admin', false)
                          ->orWhereNull('is_super_admin');
                    })
                    ->update(['tenant_id' => $defaultTenantId]);
            }
            // Super admins remain NULL (platform-wide access)
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->dropIndex('idx_pools_tenant_id');
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_tenant_id');
                $table->dropColumn('tenant_id');
            });
        }
    }
};
