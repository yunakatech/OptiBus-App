<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules') || Schema::hasColumn('schedules', 'bop')) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->decimal('bop', 15, 2)->default(0)->after('units');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'bop')) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('bop');
        });
    }
};
