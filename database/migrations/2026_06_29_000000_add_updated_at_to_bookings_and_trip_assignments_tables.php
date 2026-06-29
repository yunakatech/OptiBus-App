<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'trip_assignments'] as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'updated_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->timestamp('updated_at')->nullable();
            });

            DB::table($table)->update([
                'updated_at' => DB::raw('created_at'),
            ]);
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'trip_assignments'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'updated_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('updated_at');
            });
        }
    }
};
