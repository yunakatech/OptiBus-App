<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trip_assignments')) {
            return;
        }

        Schema::table('trip_assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('trip_assignments', 'status')) {
                $table->string('status', 20)->default('active')->after('driver_id');
            }
        });

        DB::table('trip_assignments')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasColumn('trip_assignments', 'status')) {
            return;
        }

        Schema::table('trip_assignments', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
