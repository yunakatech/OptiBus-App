<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')
            || ! Schema::hasTable('permissions')
            || ! Schema::hasTable('role_permission')) {
            return;
        }

        $roleId = (int) (DB::table('roles')
            ->where('slug', 'admin-pool')
            ->value('id') ?? 0);
        $permissionId = (int) (DB::table('permissions')
            ->where('slug', 'booking.delete')
            ->value('id') ?? 0);

        if ($roleId <= 0 || $permissionId <= 0) {
            return;
        }

        $now = now();
        DB::table('role_permission')->updateOrInsert(
            [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ],
            [
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        // Keep manually granted access intact when rolling back migrations.
    }
};
