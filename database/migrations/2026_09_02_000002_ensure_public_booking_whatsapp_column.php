<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair deployments where the original public-booking migration was
     * recorded as ran before the WhatsApp column was present.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tenants') || Schema::hasColumn('tenants', 'public_booking_whatsapp')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('public_booking_whatsapp', 50)->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'public_booking_whatsapp')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn('public_booking_whatsapp');
        });
    }
};
