<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        $phone = '081234567890';
        if (DB::table('customers')->where('phone', $phone)->exists()) {
            return;
        }

        DB::table('customers')->insert([
            'name' => 'CUSTOMER DUMMY QBUS',
            'phone' => $phone,
            'pickup_point' => 'Terminal Kayuringin',
            'address' => 'https://maps.google.com/?q=Terminal+Kayuringin',
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        DB::table('customers')
            ->where('phone', '081234567890')
            ->where('name', 'CUSTOMER DUMMY QBUS')
            ->delete();
    }
};
