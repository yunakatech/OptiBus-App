<?php

use App\Support\BookingScheduleBackfill;
use App\Support\LegacyBookingCoreImporter;
use App\Support\LegacyOperationsImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legacy:import-booking-core {--chunk=500} {--truncate} {--dry-run}', function () {
    $chunk = max(100, (int) $this->option('chunk'));
    $truncate = (bool) $this->option('truncate');
    $dryRun = (bool) $this->option('dry-run');

    $this->info('Menyiapkan import booking core dari database legacy...');
    $this->line('Target connection: '.config('database.default'));
    $this->line('Chunk size: '.$chunk);
    $this->line('Mode: '.($dryRun ? 'DRY RUN' : 'WRITE'));
    if ($truncate) {
        $this->warn('Opsi --truncate aktif: tabel target akan dikosongkan sebelum import.');
    }

    $startedAt = microtime(true);

    try {
        $summary = app(LegacyBookingCoreImporter::class)->import($truncate, $chunk, $dryRun);
    } catch (Throwable $e) {
        $this->error('Import gagal: '.$e->getMessage());
        return self::FAILURE;
    }

    $this->newLine();
    $this->info('Ringkasan source rows:');
    foreach ($summary as $table => $count) {
        $this->line("- {$table}: {$count}");
    }

    $durationMs = number_format((microtime(true) - $startedAt) * 1000, 2);
    $this->newLine();
    $this->info("Selesai dalam {$durationMs} ms.");

    return self::SUCCESS;
})->purpose('Import routes/units/schedules/customers/segments/bookings dari database legacy');

Artisan::command('legacy:import-operations {--chunk=500} {--truncate} {--dry-run}', function () {
    $chunk = max(100, (int) $this->option('chunk'));
    $truncate = (bool) $this->option('truncate');
    $dryRun = (bool) $this->option('dry-run');

    $this->info('Menyiapkan import data operasi dari database legacy...');
    $this->line('Target connection: '.config('database.default'));
    $this->line('Chunk size: '.$chunk);
    $this->line('Mode: '.($dryRun ? 'DRY RUN' : 'WRITE'));
    if ($truncate) {
        $this->warn('Opsi --truncate aktif: tabel target akan dikosongkan sebelum import.');
    }

    $startedAt = microtime(true);

    try {
        $summary = app(LegacyOperationsImporter::class)->import($truncate, $chunk, $dryRun);
    } catch (Throwable $e) {
        $this->error('Import gagal: '.$e->getMessage());
        return self::FAILURE;
    }

    $this->newLine();
    $this->info('Ringkasan source rows:');
    foreach ($summary as $table => $count) {
        $this->line("- {$table}: {$count}");
    }

    $durationMs = number_format((microtime(true) - $startedAt) * 1000, 2);
    $this->newLine();
    $this->info("Selesai dalam {$durationMs} ms.");

    return self::SUCCESS;
})->purpose('Import drivers/trip assignments/charter/luggage/cancellation/customer ops dari database legacy');

Artisan::command('booking:cleanup-layout-bagasi {--dry-run} {--include-schedules}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $includeSchedules = (bool) $this->option('include-schedules');

    $this->info('Menyiapkan pembersihan data layout bagasi...');
    $this->line('Mode: '.($dryRun ? 'DRY RUN' : 'WRITE'));
    $this->line('Include schedules: '.($includeSchedules ? 'yes' : 'no'));

    $cleanupLayout = function (mixed $decoded): array {
        if (! is_array($decoded)) {
            return ['changed' => false, 'layout' => $decoded];
        }

        $changed = false;
        $resultRows = [];

        foreach ($decoded as $row) {
            if (! is_array($row)) {
                $resultRows[] = $row;
                continue;
            }

            $rowAllBagasi = true;
            $cleanRow = [];

            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $rawType = strtolower(trim((string) ($cell['type'] ?? '')));
                    if ($rawType === 'bagasi' || $rawType === 'bagasi-custom') {
                        $changed = true;
                        $cleanRow[] = [
                            'type' => 'empty',
                            'label' => '',
                        ];
                        continue;
                    }

                    if ($rawType !== '' && ! in_array($rawType, ['seat', 'empty', 'driver'], true)) {
                        $changed = true;
                        $cell['type'] = 'empty';
                        $cell['label'] = '';
                    }

                    if (($cell['type'] ?? '') !== 'empty') {
                        $rowAllBagasi = false;
                    }

                    $cleanRow[] = $cell;
                    continue;
                }

                $token = strtoupper(trim((string) $cell));
                if ($token === 'BAGASI') {
                    $changed = true;
                    $cleanRow[] = '';
                    continue;
                }

                if ($token !== '') {
                    $rowAllBagasi = false;
                }
                $cleanRow[] = $cell;
            }

            if ($rowAllBagasi) {
                $changed = true;
                continue;
            }

            $resultRows[] = $cleanRow;
        }

        return ['changed' => $changed, 'layout' => $resultRows];
    };

    $processTable = function (string $table) use ($dryRun, $cleanupLayout) {
        if (! Schema::hasTable($table)) {
            return [
                'table' => $table,
                'total' => 0,
                'changed' => 0,
                'updated' => 0,
                'skipped_invalid_json' => 0,
            ];
        }

        $rows = DB::table($table)
            ->whereNotNull('layout')
            ->where('layout', '!=', '')
            ->get(['id', 'layout']);

        $changed = 0;
        $updated = 0;
        $invalidJson = 0;

        foreach ($rows as $row) {
            $raw = (string) $row->layout;
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $invalidJson += 1;
                continue;
            }

            $clean = $cleanupLayout($decoded);
            if (! $clean['changed']) {
                continue;
            }

            $changed += 1;
            if ($dryRun) {
                continue;
            }

            $encoded = json_encode($clean['layout'], JSON_UNESCAPED_UNICODE);
            if (! is_string($encoded)) {
                continue;
            }

            DB::table($table)
                ->where('id', $row->id)
                ->update(['layout' => $encoded]);
            $updated += 1;
        }

        return [
            'table' => $table,
            'total' => $rows->count(),
            'changed' => $changed,
            'updated' => $updated,
            'skipped_invalid_json' => $invalidJson,
        ];
    };

    $tables = ['units'];
    if ($includeSchedules) {
        $tables[] = 'schedules';
    }

    $summaries = [];
    foreach ($tables as $table) {
        $summaries[] = $processTable($table);
    }

    $this->newLine();
    $this->info('Ringkasan:');
    foreach ($summaries as $summary) {
        $this->line("- {$summary['table']} | total: {$summary['total']} | changed: {$summary['changed']} | updated: {$summary['updated']} | invalid_json: {$summary['skipped_invalid_json']}");
    }

    if ($dryRun) {
        $this->newLine();
        $this->warn('Dry run selesai. Jalankan tanpa --dry-run untuk menulis perubahan.');
    } else {
        $this->newLine();
        $this->info('Pembersihan layout bagasi selesai.');
    }

    return self::SUCCESS;
})->purpose('Bersihkan elemen bagasi dari layout JSON units/schedules');

Artisan::command('booking:backfill-schedules {--write} {--route=} {--jam=}', function () {
    $write = (bool) $this->option('write');
    $route = trim((string) $this->option('route'));
    $jam = trim((string) $this->option('jam'));

    $this->info('Menyiapkan backfill jadwal dari data booking lama...');
    $this->line('Mode: '.($write ? 'WRITE' : 'DRY RUN'));
    if ($route !== '') {
        $this->line('Filter rute: '.$route);
    }
    if ($jam !== '') {
        $this->line('Filter jam: '.$jam);
    }

    $startedAt = microtime(true);

    try {
        $summary = app(BookingScheduleBackfill::class)->run(
            $write,
            $route !== '' ? $route : null,
            $jam !== '' ? $jam : null,
        );
    } catch (Throwable $e) {
        $this->error('Backfill gagal: '.$e->getMessage());

        return self::FAILURE;
    }

    $this->newLine();
    $this->info('Ringkasan:');
    $this->line('- Dibuat / direncanakan: '.(int) ($summary['created'] ?? 0));
    $this->line('- Sudah ada: '.(int) ($summary['skipped'] ?? 0));
    $this->line('- Masih ambigu: '.(int) ($summary['unresolved'] ?? 0));

    $items = is_array($summary['items'] ?? null) ? $summary['items'] : [];
    foreach ($items as $item) {
        $status = strtoupper((string) ($item['status'] ?? 'info'));
        $routeName = (string) ($item['route'] ?? '-');
        $dow = (string) ($item['dow'] ?? '-');
        $jamValue = (string) ($item['jam'] ?? '-');
        $units = (string) ($item['units'] ?? '-');
        $message = trim((string) ($item['message'] ?? ''));

        $this->line("[{$status}] {$routeName} | DOW {$dow} | {$jamValue} | Unit {$units}");
        if ($message !== '') {
            $this->line("  {$message}");
        }
    }

    $durationMs = number_format((microtime(true) - $startedAt) * 1000, 2);
    $this->newLine();
    $this->info("Selesai dalam {$durationMs} ms.");

    return self::SUCCESS;
})->purpose('Buat jadwal yang hilang dari histori booking lama');
