<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('harga_bagasi');
    }

    public function down(): void
    {
        if (! Schema::hasTable('harga_bagasi')) {
            Schema::create('harga_bagasi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rute_id');
                $table->unsignedBigInteger('layanan_id');
                $table->decimal('harga', 15, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_harga_bagasi_pair ON harga_bagasi (rute_id, layanan_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_harga_bagasi_rute ON harga_bagasi (rute_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_harga_bagasi_layanan ON harga_bagasi (layanan_id)');
    }
};