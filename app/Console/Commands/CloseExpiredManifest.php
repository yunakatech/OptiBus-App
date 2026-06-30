<?php

namespace App\Console\Commands;

use App\Support\ManifestLifecycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CloseExpiredManifest extends Command
{
    protected $signature = 'booking:close-expired-manifests';

    protected $description = 'Auto close manifests 24 hours after departure time';

    public function handle(): int
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasColumn('trip_assignments', 'status')) {
            $this->warn('trip_assignments table is not ready.');

            return self::FAILURE;
        }

        $rows = DB::table('trip_assignments')
            ->whereNotIn('status', ['canceled', 'closed'])
            ->get(['id', 'tanggal', 'jam', 'status']);

        $closed = 0;

        foreach ($rows as $row) {
            if (! ManifestLifecycle::shouldAutoClose(
                $row->status ?? 'active',
                (string) ($row->tanggal ?? ''),
                (string) ($row->jam ?? ''),
            )) {
                continue;
            }

            $closed += DB::table('trip_assignments')
                ->where('id', (int) ($row->id ?? 0))
                ->update([
                    'status' => 'closed',
                    'updated_at' => now(),
                ]);
        }

        if ($closed > 0) {
            $this->info("{$closed} manifest closed automatically.");
        } else {
            $this->info('No manifest needed to be closed.');
        }

        return self::SUCCESS;
    }
}
