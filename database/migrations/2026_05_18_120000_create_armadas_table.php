<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addColumnIfMissing(string $table, string $column, callable $callback): void
    {
        if (! Schema::hasColumn($table, $column)) {
            Schema::table($table, $callback);
        }
    }

    public function up(): void
    {
        if (! Schema::hasTable('armadas')) {
            Schema::create('armadas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kendaraan', 120);
                $table->string('merk', 120)->nullable();
                $table->unsignedInteger('tahun')->default(0);
                $table->string('warna', 80)->nullable();
                $table->string('nopol', 50)->unique();
                $table->string('kategori', 120)->nullable();
                $table->string('ac_type', 20)->default('AC');
                $table->decimal('harga_mobil', 15, 2)->default(0);
                $table->decimal('target_bulanan', 15, 2)->default(0);
                $table->decimal('target_tahunan', 15, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('armadas', 'nama_kendaraan', fn (Blueprint $table) => $table->string('nama_kendaraan', 120)->default(''));
            $this->addColumnIfMissing('armadas', 'merk', fn (Blueprint $table) => $table->string('merk', 120)->nullable());
            $this->addColumnIfMissing('armadas', 'tahun', fn (Blueprint $table) => $table->unsignedInteger('tahun')->default(0));
            $this->addColumnIfMissing('armadas', 'warna', fn (Blueprint $table) => $table->string('warna', 80)->nullable());
            $this->addColumnIfMissing('armadas', 'nopol', fn (Blueprint $table) => $table->string('nopol', 50)->nullable());
            $this->addColumnIfMissing('armadas', 'kategori', fn (Blueprint $table) => $table->string('kategori', 120)->nullable());
            $this->addColumnIfMissing('armadas', 'ac_type', fn (Blueprint $table) => $table->string('ac_type', 20)->default('AC'));
            $this->addColumnIfMissing('armadas', 'harga_mobil', fn (Blueprint $table) => $table->decimal('harga_mobil', 15, 2)->default(0));
            $this->addColumnIfMissing('armadas', 'target_bulanan', fn (Blueprint $table) => $table->decimal('target_bulanan', 15, 2)->default(0));
            $this->addColumnIfMissing('armadas', 'target_tahunan', fn (Blueprint $table) => $table->decimal('target_tahunan', 15, 2)->default(0));
            $this->addColumnIfMissing('armadas', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_armadas_kategori_nama ON armadas (kategori, nama_kendaraan)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_armadas_ac_type ON armadas (ac_type)');
    }

    public function down(): void
    {
        Schema::dropIfExists('armadas');
    }
};
