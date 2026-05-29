<?php

namespace App\Support;

use Carbon\Carbon;

class BookingCode
{
    public static function departureCode(?string $tanggal, ?string $jam, int $unit = 1, ?string $rute = null): string
    {
        $dateToken = self::formatDateToken($tanggal);
        $timeToken = self::formatTimeToken($jam);
        $unitToken = str_pad((string) max(1, $unit), 2, '0', STR_PAD_LEFT);
        $routeHash = strtoupper(substr(md5((string) $rute), 0, 3));

        return "DEP-{$dateToken}-{$timeToken}-U{$unitToken}-{$routeHash}";
    }

    public static function ticketCode(int $bookingId, ?string $tanggal = null): string
    {
        $dateToken = self::formatDateToken($tanggal);
        $idToken = str_pad((string) max(1, $bookingId), 6, '0', STR_PAD_LEFT);

        return "TKT-{$dateToken}-{$idToken}";
    }

    private static function formatDateToken(?string $tanggal): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', (string) $tanggal)->format('ymd');
        } catch (\Throwable) {
            return '000000';
        }
    }

    private static function formatTimeToken(?string $jam): string
    {
        $clean = substr((string) $jam, 0, 5);

        if (preg_match('/^\d{2}:\d{2}$/', $clean) !== 1) {
            return '0000';
        }

        return str_replace(':', '', $clean);
    }
}
