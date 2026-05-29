<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyOperationsImporter
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
                    ->map(static fn ($row) => (array) $row)
                    ->all();

                if (empty($rows)) {
                    break;
                }

                $updateColumns = array_values(array_filter($available, static fn ($column) => $column !== 'id'));
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
            'drivers' => [
                'id', 'nama', 'phone', 'unit_id', 'created_at',
            ],
            'trip_assignments' => [
                'id', 'rute', 'tanggal', 'jam', 'unit', 'driver_id', 'created_at',
            ],
            'cancellations' => [
                'id', 'booking_id', 'admin_user', 'reason', 'created_at',
            ],
            'luggage_services' => [
                'id', 'name', 'created_at',
            ],
            'master_carter' => [
                'id', 'name', 'origin', 'destination', 'duration', 'rental_price', 'bop_price', 'notes', 'created_at',
            ],
            'charters' => [
                'id', 'name', 'company_name', 'phone', 'start_date', 'end_date', 'departure_time', 'pickup_point',
                'drop_point', 'unit_id', 'driver_name', 'price', 'layanan', 'bop_price', 'bop_status',
                'down_payment', 'payment_status', 'created_at',
            ],
            'luggages' => [
                'id', 'sender_name', 'sender_phone', 'sender_address', 'receiver_name', 'receiver_phone',
                'receiver_address', 'service_id', 'quantity', 'notes', 'price', 'status', 'payment_status',
                'rute', 'tanggal', 'unit_id', 'kode_resi', 'pengirim_id', 'penerima_id', 'rute_id',
                'layanan_id', 'created_at',
            ],
            'customer_bagasi' => [
                'id', 'nama', 'no_hp', 'alamat', 'tipe', 'created_at',
            ],
            'customer_charter' => [
                'id', 'nama', 'no_hp', 'alamat', 'company', 'created_at',
            ],
        ];
    }
}
