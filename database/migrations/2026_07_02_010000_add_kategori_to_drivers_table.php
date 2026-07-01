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

        if (! Schema::hasColumn('drivers', 'kategori')) {
            Schema::table('drivers', function (Blueprint $table): void {
                $table->string('kategori', 30)->nullable()->after('phone');
            });
        }

        if (! Schema::hasColumn('drivers', 'kategori')) {
            return;
        }

        $armadaCategories = collect();
        if (
            Schema::hasTable('armadas')
            && Schema::hasColumn('drivers', 'armada_id')
            && Schema::hasColumn('armadas', 'kategori')
        ) {
            $armadaCategories = DB::table('armadas')
                ->select(['id', 'kategori'])
                ->get()
                ->keyBy('id');
        }

        DB::table('drivers')
            ->select([
                'id',
                Schema::hasColumn('drivers', 'armada_id') ? 'armada_id' : DB::raw('NULL as armada_id'),
            ])
            ->where(function ($query): void {
                $query
                    ->whereNull('kategori')
                    ->orWhere('kategori', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($drivers) use ($armadaCategories): void {
                foreach ($drivers as $driver) {
                    $armadaCategory = $armadaCategories->get((int) ($driver->armada_id ?? 0));
                    $category = $this->normalizeDriverCategory($armadaCategory->kategori ?? null);

                    DB::table('drivers')
                        ->where('id', (int) $driver->id)
                        ->update(['kategori' => $category]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('drivers') || ! Schema::hasColumn('drivers', 'kategori')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table): void {
            $table->dropColumn('kategori');
        });
    }

    private function normalizeDriverCategory(mixed $value): string
    {
        $normalized = strtolower(preg_replace('/\s+/', '', trim((string) $value)) ?? '');

        return match ($normalized) {
            'mediumbus', 'medium' => 'Mediumbus',
            'bigbus', 'bigbun', 'big' => 'Bigbus',
            default => 'Minibus',
        };
    }
};
