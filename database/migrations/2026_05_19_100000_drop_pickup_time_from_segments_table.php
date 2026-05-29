<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('segments') && Schema::hasColumn('segments', 'pickup_time')) {
            Schema::table('segments', function (Blueprint $table) {
                $table->dropColumn('pickup_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('segments') && ! Schema::hasColumn('segments', 'pickup_time')) {
            Schema::table('segments', function (Blueprint $table) {
                $table->string('pickup_time', 5)->nullable()->after('destination');
            });
        }
    }
};

