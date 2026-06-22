<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('segments') && ! Schema::hasColumn('segments', 'jam_pickups')) {
            Schema::table('segments', function (Blueprint $table): void {
                $table->json('jam_pickups')->nullable()->after('jam');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('segments') && Schema::hasColumn('segments', 'jam_pickups')) {
            Schema::table('segments', function (Blueprint $table): void {
                $table->dropColumn('jam_pickups');
            });
        }
    }
};
