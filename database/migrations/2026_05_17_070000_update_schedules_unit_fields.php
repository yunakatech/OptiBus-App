<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('schedules', 'unit_label')) {
                $table->string('unit_label', 120)->nullable()->after('units');
            }
        });

        if (Schema::hasColumn('schedules', 'seats')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('seats');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        if (! Schema::hasColumn('schedules', 'seats')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedInteger('seats')->default(8)->after('units');
            });
        }

        if (Schema::hasColumn('schedules', 'unit_label')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('unit_label');
            });
        }
    }
};
