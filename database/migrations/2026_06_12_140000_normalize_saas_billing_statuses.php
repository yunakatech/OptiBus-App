<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_subscriptions') && Schema::hasColumn('invoice_subscriptions', 'payment_proof')) {
            DB::table('invoice_subscriptions')
                ->where('status', 'pending')
                ->whereNotNull('payment_proof')
                ->where('payment_proof', '!=', '')
                ->update([
                    'status' => 'verification',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_subscriptions')) {
            DB::table('invoice_subscriptions')
                ->where('status', 'verification')
                ->update([
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
        }
    }
};
