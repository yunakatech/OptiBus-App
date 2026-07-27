<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('armadas') && Schema::hasColumn('armadas', 'harga_mobil')) {
            Schema::table('armadas', function (Blueprint $table): void {
                $table->dropColumn('harga_mobil');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('armadas') && ! Schema::hasColumn('armadas', 'harga_mobil')) {
            Schema::table('armadas', function (Blueprint $table): void {
                $table->decimal('harga_mobil', 15, 2)->default(0)->after('ac_type');
            });
        }
    }
};
