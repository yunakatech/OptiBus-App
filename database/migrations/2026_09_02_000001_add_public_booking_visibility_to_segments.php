<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('segments') || Schema::hasColumn('segments', 'public_booking_enabled')) {
            return;
        }

        Schema::table('segments', function (Blueprint $table): void {
            $table->boolean('public_booking_enabled')->default(true)->after('harga');
            $table->index(
                ['route_id', 'public_booking_enabled'],
                'idx_segments_public_booking_visibility',
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('segments') || ! Schema::hasColumn('segments', 'public_booking_enabled')) {
            return;
        }

        Schema::table('segments', function (Blueprint $table): void {
            $table->dropIndex('idx_segments_public_booking_visibility');
            $table->dropColumn('public_booking_enabled');
        });
    }
};
