<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair deployments where the request extension migration was recorded
     * as ran before all segment snapshot columns were present.
     */
    public function up(): void
    {
        if (! Schema::hasTable('public_booking_requests')) {
            return;
        }

        Schema::table('public_booking_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('public_booking_requests', 'segment_id')) {
                $table->unsignedBigInteger('segment_id')->nullable();
            }
            if (! Schema::hasColumn('public_booking_requests', 'price')) {
                $table->decimal('price', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('public_booking_requests', 'pickup_time')) {
                $table->string('pickup_time', 5)->nullable();
            }
        });

        if (! Schema::hasIndex('public_booking_requests', 'idx_public_booking_request_segment')) {
            Schema::table('public_booking_requests', function (Blueprint $table): void {
                $table->index('segment_id', 'idx_public_booking_request_segment');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('public_booking_requests')) {
            return;
        }

        if (Schema::hasIndex('public_booking_requests', 'idx_public_booking_request_segment')) {
            Schema::table('public_booking_requests', function (Blueprint $table): void {
                $table->dropIndex('idx_public_booking_request_segment');
            });
        }

        Schema::table('public_booking_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('public_booking_requests', 'segment_id')) {
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
};
