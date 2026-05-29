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
            if (! Schema::hasColumn('armadas', 'revenue')) {
                $table->decimal('revenue', 15, 2)->default(0)->after('api_gps');
            }
            if (! Schema::hasColumn('armadas', 'bop')) {
                $table->decimal('bop', 15, 2)->default(0)->after('revenue');
            }
            if (! Schema::hasColumn('armadas', 'fixed_cost')) {
                $table->decimal('fixed_cost', 15, 2)->default(0)->after('bop');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        Schema::table('armadas', function (Blueprint $table) {
            if (Schema::hasColumn('armadas', 'fixed_cost')) {
                $table->dropColumn('fixed_cost');
            }
            if (Schema::hasColumn('armadas', 'bop')) {
                $table->dropColumn('bop');
            }
            if (Schema::hasColumn('armadas', 'revenue')) {
                $table->dropColumn('revenue');
            }
        });
    }
};
