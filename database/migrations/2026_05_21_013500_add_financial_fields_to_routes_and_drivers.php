<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table) {
                if (! Schema::hasColumn('routes', 'bop')) {
                    $table->decimal('bop', 15, 2)->default(0)->after('destination');
                }
            });
        }

        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (! Schema::hasColumn('drivers', 'revenue')) {
                    $table->decimal('revenue', 15, 2)->default(0)->after('target_revenue_bulanan');
                }
                if (! Schema::hasColumn('drivers', 'bop')) {
                    $table->decimal('bop', 15, 2)->default(0)->after('revenue');
                }
                if (! Schema::hasColumn('drivers', 'fixed_cost')) {
                    $table->decimal('fixed_cost', 15, 2)->default(0)->after('bop');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (Schema::hasColumn('drivers', 'fixed_cost')) {
                    $table->dropColumn('fixed_cost');
                }
                if (Schema::hasColumn('drivers', 'bop')) {
                    $table->dropColumn('bop');
                }
                if (Schema::hasColumn('drivers', 'revenue')) {
                    $table->dropColumn('revenue');
                }
            });
        }

        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table) {
                if (Schema::hasColumn('routes', 'bop')) {
                    $table->dropColumn('bop');
                }
            });
        }
    }
};
