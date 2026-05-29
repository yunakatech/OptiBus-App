<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('luggages')) {
            return;
        }

        Schema::table('luggages', function (Blueprint $table): void {
            if (! Schema::hasColumn('luggages', 'trip_assignment_id')) {
                $table->unsignedBigInteger('trip_assignment_id')->nullable()->after('unit_id');
            }
        });

        DB::statement('CREATE INDEX IF NOT EXISTS idx_luggages_trip_assignment_id ON luggages (trip_assignment_id)');
    }

    public function down(): void
    {
        if (! Schema::hasTable('luggages')) {
            return;
        }

        try {
            DB::statement('DROP INDEX IF EXISTS idx_luggages_trip_assignment_id');
        } catch (\Throwable) {
            // no-op
        }

        if (! Schema::hasColumn('luggages', 'trip_assignment_id')) {
            return;
        }

        Schema::table('luggages', function (Blueprint $table): void {
            $table->dropColumn('trip_assignment_id');
        });
    }
};
