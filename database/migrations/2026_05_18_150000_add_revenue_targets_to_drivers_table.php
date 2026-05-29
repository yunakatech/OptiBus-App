<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'target_revenue_bulanan')) {
                $table->decimal('target_revenue_bulanan', 15, 2)->default(0)->after('unit_id');
            }
            if (! Schema::hasColumn('drivers', 'target_revenue_tahunan')) {
                $table->decimal('target_revenue_tahunan', 15, 2)->default(0)->after('target_revenue_bulanan');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'target_revenue_tahunan')) {
                $table->dropColumn('target_revenue_tahunan');
            }
            if (Schema::hasColumn('drivers', 'target_revenue_bulanan')) {
                $table->dropColumn('target_revenue_bulanan');
            }
        });
    }
};
