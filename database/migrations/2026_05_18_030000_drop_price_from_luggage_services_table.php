<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('luggage_services') && Schema::hasColumn('luggage_services', 'price')) {
            Schema::table('luggage_services', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('luggage_services') && ! Schema::hasColumn('luggage_services', 'price')) {
            Schema::table('luggage_services', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->default(0)->after('name');
            });
        }
    }
};