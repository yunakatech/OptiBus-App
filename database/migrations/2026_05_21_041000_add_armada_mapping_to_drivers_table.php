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

        if (! Schema::hasTable('units') || ! Schema::hasTable('armadas')) {
            return;
        }

        DB::table('drivers as d')
            ->leftJoin('units as u', 'd.unit_id', '=', 'u.id')
            ->whereNotNull('d.unit_id')
            ->whereNull('d.armada_id')
            ->select(['d.id', 'u.nopol'])
            ->orderBy('d.id')
            ->get()
            ->each(function ($row): void {
                $nopol = strtoupper(trim((string) ($row->nopol ?? '')));
                if ($nopol === '') {
                    return;
                }

                $armada = DB::table('armadas')
                    ->whereRaw('UPPER(nopol) = ?', [$nopol])
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
