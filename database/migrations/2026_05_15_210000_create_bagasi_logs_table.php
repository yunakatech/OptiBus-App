<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bagasi_logs')) {
            Schema::create('bagasi_logs', function (Blueprint $table) {
                $table->id();
                $table->string('kode_resi', 50);
                $table->string('status', 30);
                $table->text('notes')->nullable();
                $table->string('created_by_username', 120)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_bagasi_logs_resi ON bagasi_logs (kode_resi)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_bagasi_logs_created ON bagasi_logs (created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bagasi_logs');
    }
};

