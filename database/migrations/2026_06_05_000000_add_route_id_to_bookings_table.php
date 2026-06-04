<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        if (! Schema::hasColumn('bookings', 'route_id')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->unsignedBigInteger('route_id')->nullable()->after('id');
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_route_id_trip ON bookings (route_id, tanggal, jam, unit)');

        $routeMap = $this->routeMap();
        if ($routeMap === []) {
            return;
        }

        DB::table('bookings')
            ->whereNull('route_id')
            ->select(['id', 'rute'])
            ->orderBy('id')
            ->chunkById(200, function ($bookings) use ($routeMap): void {
                foreach ($bookings as $booking) {
                    $routeId = (int) ($routeMap[$this->normalizeRouteName((string) ($booking->rute ?? ''))] ?? 0);

                    if ($routeId > 0) {
                        DB::table('bookings')->where('id', (int) $booking->id)->update(['route_id' => $routeId]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasColumn('bookings', 'route_id')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('idx_bookings_route_id_trip');
            $table->dropColumn('route_id');
        });
    }

    /**
     * @return array<string, int>
     */
    private function routeMap(): array
    {
        if (! Schema::hasTable('routes')) {
            return [];
        }

        $routeMap = [];
        $ambiguous = [];

        foreach (DB::table('routes')->get(['id', 'name', 'origin', 'destination']) as $route) {
            $candidates = [(string) ($route->name ?? '')];
            $origin = trim((string) ($route->origin ?? ''));
            $destination = trim((string) ($route->destination ?? ''));

            if ($origin !== '' && $destination !== '') {
                $candidates[] = $origin.' - '.$destination;
            }

            foreach ($candidates as $candidate) {
                $key = $this->normalizeRouteName($candidate);
                $routeId = (int) ($route->id ?? 0);

                if ($key === '' || $routeId <= 0) {
                    continue;
                }
                if (isset($routeMap[$key]) && $routeMap[$key] !== $routeId) {
                    $ambiguous[$key] = true;

                    continue;
                }

                $routeMap[$key] = $routeId;
            }
        }

        foreach (array_keys($ambiguous) as $key) {
            unset($routeMap[$key]);
        }

        return $routeMap;
    }

    private function normalizeRouteName(string $routeName): string
    {
        $normalized = mb_strtoupper(trim($routeName));
        $normalized = str_replace(['=>', '->', '→', '–', '—'], '-', $normalized);

        return preg_replace('/\s+/', '', $normalized) ?? $normalized;
    }
};
