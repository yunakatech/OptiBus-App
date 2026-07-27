<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        $columns = [];

        foreach (['merek', 'type', 'tahun', 'warna'] as $column) {
            if (Schema::hasColumn('units', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns === []) {
            return;
        }

        Schema::table('units', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('units')) {
            return;
        }

        Schema::table('units', function (Blueprint $table): void {
            if (! Schema::hasColumn('units', 'merek')) {
                $table->string('merek', 120)->nullable()->after('nama_kategori');
            }

            if (! Schema::hasColumn('units', 'type')) {
                $table->string('type', 120)->nullable()->after('merek');
            }

            if (! Schema::hasColumn('units', 'tahun')) {
                $table->unsignedInteger('tahun')->default(0)->after('category');
            }

            if (! Schema::hasColumn('units', 'warna')) {
                $table->string('warna', 120)->nullable()->after('tahun');
            }
        });
    }
};
