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

        if (in_array($normalized, ['canceled', 'closed'], true)) {
            return false;
        }

        return self::isAutoCloseDue($tanggal, $jam, $now);
    }

    public static function syncTripAssignmentStatus(?object $assignment, ?CarbonInterface $now = null): string
    {
        if (! $assignment) {
            return 'active';
        }

        $status = self::normalizeStatus($assignment->status ?? 'active');
        $tanggal = (string) ($assignment->tanggal ?? '');
        $jam = (string) ($assignment->jam ?? '');

        if (! self::shouldAutoClose($status, $tanggal, $jam, $now)) {
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
}
