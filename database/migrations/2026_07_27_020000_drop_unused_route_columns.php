<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table): void {
                if (Schema::hasColumn('routes', 'distance_km')) {
                    $table->dropColumn('distance_km');
                }

                if (Schema::hasColumn('routes', 'duration_minutes')) {
                    $table->dropColumn('duration_minutes');
                }
            });
        }

    }

    public function down(): void
    {
        if (Schema::hasTable('routes')) {
            Schema::table('routes', function (Blueprint $table): void {
                if (! Schema::hasColumn('routes', 'distance_km')) {
                    $table->decimal('distance_km', 10, 2)->nullable()->after('destination');
                }

                if (! Schema::hasColumn('routes', 'duration_minutes')) {
                    $table->unsignedInteger('duration_minutes')->nullable()->after('distance_km');
                }
            });
        }

    }
};
