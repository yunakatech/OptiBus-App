<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table): void {
            if (! Schema::hasColumn('pools', 'phone')) {
                $table->string('phone', 30)->nullable()->after('code');
            }

            if (! Schema::hasColumn('pools', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table): void {
            if (Schema::hasColumn('pools', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('pools', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
