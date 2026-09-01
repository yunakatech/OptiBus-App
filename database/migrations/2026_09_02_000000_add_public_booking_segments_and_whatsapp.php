<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants') && ! Schema::hasColumn('tenants', 'public_booking_whatsapp')) {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->string('public_booking_whatsapp', 50)->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('public_booking_requests')) {
            Schema::table('public_booking_requests', function (Blueprint $table): void {
                if (! Schema::hasColumn('public_booking_requests', 'segment_id')) {
                    $table->unsignedBigInteger('segment_id')->nullable()->after('route_id');
                    $table->index('segment_id', 'idx_public_booking_request_segment');
                }
                if (! Schema::hasColumn('public_booking_requests', 'price')) {
                    $table->decimal('price', 15, 2)->default(0)->after('unit');
                }
                if (! Schema::hasColumn('public_booking_requests', 'pickup_time')) {
                    $table->string('pickup_time', 5)->nullable()->after('price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('public_booking_requests')) {
            Schema::table('public_booking_requests', function (Blueprint $table): void {
                if (Schema::hasColumn('public_booking_requests', 'segment_id')) {
                    $table->dropIndex('idx_public_booking_request_segment');
                    $table->dropColumn('segment_id');
                }
                if (Schema::hasColumn('public_booking_requests', 'price')) {
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('public_booking_requests', 'pickup_time')) {
                    $table->dropColumn('pickup_time');
                }
            });
        }

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'public_booking_whatsapp')) {
            Schema::table('tenants', fn (Blueprint $table) => $table->dropColumn('public_booking_whatsapp'));
        }
    }
};
