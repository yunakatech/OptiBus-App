<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManifestLifecycle
{
    public static function normalizeStatus(mixed $status): string
    {
        $normalized = strtolower(trim((string) ($status ?? '')));

        return match ($normalized) {
            'departed', 'arrived', 'canceled', 'closed' => $normalized,
            default => $normalized !== '' ? $normalized : 'active',
        };
    }

    public static function isClosedStatus(mixed $status): bool
    {
        return self::normalizeStatus($status) === 'closed';
    }

    public static function isCanceledStatus(mixed $status): bool
    {
        return self::normalizeStatus($status) === 'canceled';
    }

    public static function departureAt(string $tanggal, string $jam): ?CarbonImmutable
    {
        $datePart = substr(trim($tanggal), 0, 10);
        $timePart = self::normalizeTime($jam);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePart)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d H:i:s', $datePart.' '.$timePart);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function manifestCloseAt(string $tanggal, string $jam): ?CarbonImmutable
    {
        $departureAt = self::departureAt($tanggal, $jam);
        if ($departureAt === null) {
            return null;
        }

        return $departureAt->addDay();
    }

    public static function isAutoCloseDue(string $tanggal, string $jam, ?CarbonInterface $now = null): bool
    {
        $closeAt = self::manifestCloseAt($tanggal, $jam);
        if ($closeAt === null) {
            return false;
        }

        return ($now ?? now())->greaterThanOrEqualTo($closeAt);
    }

    public static function shouldAutoClose(mixed $status, string $tanggal, string $jam, ?CarbonInterface $now = null): bool
    {
        $normalized = self::normalizeStatus($status);

        if ($normalized !== 'arrived') {
            return false;
        }

        return self::isAutoCloseDue($tanggal, $jam, $now);
    }

    public static function shouldAutoCloseAssignment(?object $assignment, ?CarbonInterface $now = null): bool
    {
        if (! $assignment) {
            return false;
        }

        return self::shouldAutoClose(
            $assignment->status ?? 'active',
            (string) ($assignment->tanggal ?? ''),
            (string) ($assignment->jam ?? ''),
            $now,
        ) && self::hasRequiredAssignment($assignment) && self::activeBookingsArePaid($assignment);
    }

    public static function syncTripAssignmentStatus(?object $assignment, ?CarbonInterface $now = null): string
    {
        if (! $assignment) {
            return 'active';
        }

        $status = self::normalizeStatus($assignment->status ?? 'active');
        $tanggal = (string) ($assignment->tanggal ?? '');
        $jam = (string) ($assignment->jam ?? '');

        if (! self::shouldAutoCloseAssignment($assignment, $now)) {
            $assignment->status = $status;

            return $status;
        }

        $assignmentId = (int) ($assignment->id ?? 0);
        if (
            $assignmentId > 0
            && Schema::hasTable('trip_assignments')
            && Schema::hasColumn('trip_assignments', 'status')
        ) {
            DB::table('trip_assignments')
                ->where('id', $assignmentId)
                ->update([
                    'status' => 'closed',
                    'updated_at' => now(),
                ]);
        }

        $assignment->status = 'closed';

        return 'closed';
    }

    public static function lockStatusLabel(): string
    {
        return 'closed';
    }

    private static function normalizeTime(string $jam): string
    {
        $value = trim($jam);

        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }

        return substr($value, 0, 8);
    }

    public static function hasRequiredAssignment(object $assignment): bool
    {
        $id = (int) ($assignment->id ?? 0);
        if ($id <= 0 || ! Schema::hasTable('drivers') || ! Schema::hasColumn('trip_assignments', 'driver_id')) {
            return false;
        }

        $query = DB::table('trip_assignments as t')->where('t.id', $id)
            ->leftJoin('drivers as d', 't.driver_id', '=', 'd.id');
        $columns = ['d.nama as driver_name'];
        if (Schema::hasColumn('trip_assignments', 'armada_nopol')) {
            $columns[] = 't.armada_nopol';
        }
        if (Schema::hasColumn('trip_assignments', 'armada_id') && Schema::hasTable('armadas')) {
            $query->leftJoin('armadas as a', 't.armada_id', '=', 'a.id');
            $columns[] = 'a.nopol as armada_nopol_fallback';
        }
        $row = $query->first($columns);
        if (! $row) {
            return false;
        }
        $valid = static fn (mixed $value): bool => ! in_array(strtolower(trim((string) $value)),
            ['', '-', 'null', 'undefined', 'n/a', 'na', 'belum diisi', 'belum ada', 'belum dipilih'], true);

        return $valid($row->driver_name ?? null)
            && ($valid($row->armada_nopol ?? null) || $valid($row->armada_nopol_fallback ?? null));
    }

    public static function activeBookingsArePaid(object $assignment): bool
    {
        if (
            ! Schema::hasTable('bookings')
            || ! Schema::hasColumn('bookings', 'pembayaran')
        ) {
            return false;
        }

        $query = DB::table('bookings')
            ->where('rute', (string) ($assignment->rute ?? ''))
            ->where('tanggal', (string) ($assignment->tanggal ?? ''))
            ->where('jam', self::normalizeTime((string) ($assignment->jam ?? '')))
            ->where('unit', max(1, (int) ($assignment->unit ?? 1)));

        if (Schema::hasColumn('bookings', 'status')) {
            $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) != ?", ['canceled']);
        }

        if (
            isset($assignment->tenant_id)
            && Schema::hasColumn('bookings', 'tenant_id')
        ) {
            $query->where('tenant_id', (int) $assignment->tenant_id);
        }

        return ! $query
            ->whereRaw('LOWER(TRIM(COALESCE(pembayaran, \'\'))) != ?', ['lunas'])
            ->exists();
    }
}
