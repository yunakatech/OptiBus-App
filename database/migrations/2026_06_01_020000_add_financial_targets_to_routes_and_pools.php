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
                if (! Schema::hasColumn('routes', 'target_revenue')) {
                    $table->decimal('target_revenue', 15, 2)->default(0);
                }

                if (! Schema::hasColumn('routes', 'fixed_cost')) {
                    $table->decimal('fixed_cost', 15, 2)->default(0);
                }
            });
        }

        if (Schema::hasTable('pools')) {
            Schema::table('pools', function (Blueprint $table) {
                if (! Schema::hasColumn('pools', 'fixed_cost')) {
                    $table->decimal('fixed_cost', 15, 2)->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'fixed_cost')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->dropColumn('fixed_cost');
            });
        }

        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table) {
                if (Schema::hasColumn('routes', 'fixed_cost')) {
                    $table->dropColumn('fixed_cost');
                }

                if (Schema::hasColumn('routes', 'target_revenue')) {
                    $table->dropColumn('target_revenue');
                }
            });
        }
    }
};
