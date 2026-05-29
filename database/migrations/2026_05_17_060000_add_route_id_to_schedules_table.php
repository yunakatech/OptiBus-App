<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        if (! Schema::hasColumn('schedules', 'route_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('route_id')->nullable();
                $table->index(['route_id', 'dow', 'jam'], 'idx_schedules_route_dow_jam');
            });
        }

        $routeMap = DB::table('routes')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($row) => [strtoupper(trim((string) $row->name)) => (int) $row->id]);

        if ($routeMap->isEmpty()) {
            return;
        }

        DB::table('schedules')
            ->whereNull('route_id')
            ->orderBy('id')
            ->get(['id', 'rute'])
            ->each(function ($row) use ($routeMap) {
                $key = strtoupper(trim((string) $row->rute));
                $routeId = (int) ($routeMap[$key] ?? 0);
                if ($routeId <= 0) {
                    return;
                }
                DB::table('schedules')->where('id', (int) $row->id)->update(['route_id' => $routeId]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'route_id')) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_route_dow_jam');
            $table->dropColumn('route_id');
        });
    }
};
