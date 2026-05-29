<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        Schema::table('armadas', function (Blueprint $table) {
            if (! Schema::hasColumn('armadas', 'platform_gps')) {
                $table->string('platform_gps', 120)->nullable()->after('ac_type');
            }
            if (! Schema::hasColumn('armadas', 'api_gps')) {
                $table->string('api_gps', 255)->nullable()->after('platform_gps');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        Schema::table('armadas', function (Blueprint $table) {
            if (Schema::hasColumn('armadas', 'api_gps')) {
                $table->dropColumn('api_gps');
            }
            if (Schema::hasColumn('armadas', 'platform_gps')) {
                $table->dropColumn('platform_gps');
            }
        });
    }
};
