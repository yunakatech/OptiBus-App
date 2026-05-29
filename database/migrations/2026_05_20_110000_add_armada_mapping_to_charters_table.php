<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('charters')) {
            return;
        }

        Schema::table('charters', function (Blueprint $table): void {
            if (! Schema::hasColumn('charters', 'armada_id')) {
                $table->unsignedBigInteger('armada_id')->nullable()->after('unit_id');
            }

            if (! Schema::hasColumn('charters', 'armada_nopol')) {
                $table->string('armada_nopol', 50)->nullable()->after('armada_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('charters')) {
            return;
        }

        Schema::table('charters', function (Blueprint $table): void {
            if (Schema::hasColumn('charters', 'armada_nopol')) {
                $table->dropColumn('armada_nopol');
            }

            if (Schema::hasColumn('charters', 'armada_id')) {
                $table->dropColumn('armada_id');
            }
        });
    }
};
