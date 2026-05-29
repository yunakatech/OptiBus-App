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
            if (! Schema::hasColumn('armadas', 'nomor_rangka')) {
                $table->string('nomor_rangka', 120)->nullable()->after('nopol');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('armadas')) {
            return;
        }

        if (Schema::hasColumn('armadas', 'nomor_rangka')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->dropColumn('nomor_rangka');
            });
        }
    }
};
