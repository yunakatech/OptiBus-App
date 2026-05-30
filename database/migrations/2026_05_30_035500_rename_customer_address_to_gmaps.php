<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        $hasAddress = Schema::hasColumn('customers', 'address');
        $hasGmaps = Schema::hasColumn('customers', 'gmaps');

        if ($hasAddress && ! $hasGmaps) {
            Schema::table('customers', function (Blueprint $table) {
                $table->renameColumn('address', 'gmaps');
            });

            return;
        }

        if (! $hasGmaps) {
            Schema::table('customers', function (Blueprint $table) {
                $table->text('gmaps')->nullable();
            });
            $hasGmaps = true;
        }

        if ($hasAddress && $hasGmaps) {
            DB::table('customers')
                ->where(function ($query) {
                    $query->whereNull('gmaps')->orWhere('gmaps', '');
                })
                ->whereNotNull('address')
                ->update(['gmaps' => DB::raw('address')]);

            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        $hasAddress = Schema::hasColumn('customers', 'address');
        $hasGmaps = Schema::hasColumn('customers', 'gmaps');

        if ($hasGmaps && ! $hasAddress) {
            Schema::table('customers', function (Blueprint $table) {
                $table->renameColumn('gmaps', 'address');
            });

            return;
        }

        if (! $hasAddress) {
            Schema::table('customers', function (Blueprint $table) {
                $table->text('address')->nullable();
            });
        }

        if ($hasGmaps) {
            DB::table('customers')
                ->where(function ($query) {
                    $query->whereNull('address')->orWhere('address', '');
                })
                ->whereNotNull('gmaps')
                ->update(['address' => DB::raw('gmaps')]);
        }
    }
};
