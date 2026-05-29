<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trip_assignments')) {
            return;
        }

        Schema::table('trip_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('trip_assignments', 'armada_id')) {
                $table->unsignedBigInteger('armada_id')->nullable()->after('driver_id');
            }

            if (! Schema::hasColumn('trip_assignments', 'armada_nopol')) {
                $table->string('armada_nopol', 50)->nullable()->after('armada_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trip_assignments')) {
            return;
        }

        Schema::table('trip_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('trip_assignments', 'armada_nopol')) {
                $table->dropColumn('armada_nopol');
            }

            if (Schema::hasColumn('trip_assignments', 'armada_id')) {
                $table->dropColumn('armada_id');
            }
        });
    }
};
