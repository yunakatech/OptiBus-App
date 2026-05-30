<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyBookingCoreImporter
{
    private const LEGACY_CONNECTION = 'legacy_import';

    /**
     * @return array<string, int>
     */
    public function import(bool $truncate = false, int $chunk = 500, bool $dryRun = false): array
    {
        $this->configureLegacyConnection();

        $source = DB::connection(self::LEGACY_CONNECTION);
        $target = DB::connection(config('database.default'));

        $tables = $this->tablesMap();

        if ($truncate && ! $dryRun) {
            foreach (array_reverse(array_keys($tables)) as $table) {
                $target->table($table)->truncate();
            }
        }

        $summary = [];

        foreach ($tables as $table => $columns) {
            if (! $source->getSchemaBuilder()->hasTable($table)) {
                $summary[$table] = 0;
                continue;
            }

            $available = array_values(array_intersect($columns, $source->getSchemaBuilder()->getColumnListing($table)));
            if (empty($available) || ! in_array('id', $available, true)) {
                $summary[$table] = 0;
                continue;
            }

            $total = (int) $source->table($table)->count();
            $summary[$table] = $total;

            if ($dryRun || $total === 0) {
                continue;
            }

            $page = 1;
            while (true) {
                $rows = $source->table($table)
                    ->select($available)
                    ->orderBy('id')
                    ->forPage($page, $chunk)
                    ->get()
                    ->map(static function ($row) use ($table) {
                        $data = (array) $row;

                        if ($table === 'customers') {
                            if (! array_key_exists('gmaps', $data) || trim((string) ($data['gmaps'] ?? '')) === '') {
                                $data['gmaps'] = $data['address'] ?? null;
                            }

                            unset($data['address']);
                        }

                        return $data;
                    })
                    ->all();

                if (empty($rows)) {
                    break;
                }

                $updateColumns = array_values(array_filter(array_keys($rows[0]), static fn ($column) => $column !== 'id'));
                $target->table($table)->upsert($rows, ['id'], $updateColumns);

                $page++;
            }
        }

        return $summary;
    }

    private function configureLegacyConnection(): void
    {
        $url = env('LEGACY_DB_URL');
        $host = env('LEGACY_DB_HOST');

        if (! $url && ! $host) {
            throw new RuntimeException('Legacy DB belum diset. Isi LEGACY_DB_URL atau LEGACY_DB_HOST dkk di .env');
        }

        Config::set('database.connections.'.self::LEGACY_CONNECTION, [
            'driver' => env('LEGACY_DB_CONNECTION', 'pgsql'),
            'url' => $url,
            'host' => $host ?: '127.0.0.1',
            'port' => env('LEGACY_DB_PORT', '5432'),
            'database' => env('LEGACY_DB_DATABASE', 'cabomultibus_db'),
            'username' => env('LEGACY_DB_USERNAME', 'postgres'),
            'password' => env('LEGACY_DB_PASSWORD', ''),
            'charset' => env('LEGACY_DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('LEGACY_DB_SCHEMA', 'public'),
            'sslmode' => env('LEGACY_DB_SSLMODE', 'prefer'),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function tablesMap(): array
    {
        return [
            'routes' => [
                'id', 'name', 'origin', 'destination', 'distance_km', 'duration_minutes', 'created_at',
            ],
            'units' => [
                'id', 'nopol', 'merek', 'type', 'category', 'tahun', 'warna', 'kapasitas', 'status', 'layout', 'created_at',
            ],
            'schedules' => [
                'id', 'rute', 'dow', 'jam', 'units', 'seats', 'unit_id', 'layout', 'created_at',
            ],
            'customers' => [
                'id', 'name', 'phone', 'gmaps', 'address', 'pickup_point', 'created_at',
            ],
            'segments' => [
                'id', 'route_id', 'rute', 'origin', 'destination', 'harga', 'created_at',
            ],
            'bookings' => [
                'id', 'rute', 'tanggal', 'jam', 'unit', 'seat', 'name', 'phone', 'pickup_point',
                'pembayaran', 'status', 'segment_id', 'price', 'discount', 'created_by_user_id',
                'created_by_username', 'created_at',
            ],
        ];
    }
}
