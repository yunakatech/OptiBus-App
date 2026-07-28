<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table): void {
            if (! Schema::hasColumn('drivers', 'armada_id')) {
                $table->unsignedBigInteger('armada_id')->nullable()->after('unit_id');
            }

            if (! Schema::hasColumn('drivers', 'armada_nopol')) {
                $table->string('armada_nopol', 50)->nullable()->after('armada_id');
            }
        });

        $unitTable = Schema::hasTable('category_armada')
            ? 'category_armada'
            : (Schema::hasTable('units') ? 'units' : null);

        if ($unitTable === null || ! Schema::hasTable('armadas')) {
            return;
        }

        $unitColumn = Schema::hasColumn($unitTable, 'nama_kategori')
            ? 'nama_kategori'
            : (Schema::hasColumn($unitTable, 'nopol') ? 'nopol' : null);

        if ($unitColumn === null) {
            return;
        }

        DB::table('drivers as d')
            ->leftJoin($unitTable.' as u', 'd.unit_id', '=', 'u.id')
            ->whereNotNull('d.unit_id')
            ->whereNull('d.armada_id')
            ->select(['d.id', DB::raw('u.'.$unitColumn.' as unit_value')])
            ->orderBy('d.id')
            ->get()
            ->each(function ($row): void {
                $unitValue = strtoupper(trim((string) ($row->unit_value ?? '')));
                if ($unitValue === '') {
                    return;
                }

                $armada = DB::table('armadas')
                    ->whereRaw('UPPER(nopol) = ?', [$unitValue])
                    ->first(['id', 'nopol']);

                if (! $armada) {
                    return;
                }

                DB::table('drivers')
                    ->where('id', (int) $row->id)
                    ->update([
                        'armada_id' => (int) $armada->id,
                        'armada_nopol' => strtoupper(trim((string) $armada->nopol)),
                    ]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table): void {
            if (Schema::hasColumn('drivers', 'armada_nopol')) {
                $table->dropColumn('armada_nopol');
            }

            if (Schema::hasColumn('drivers', 'armada_id')) {
                $table->dropColumn('armada_id');
            }
        });
    }
};
