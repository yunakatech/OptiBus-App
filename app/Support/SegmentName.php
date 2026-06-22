<?php

namespace App\Support;

final class SegmentName
{
    public static function display(?string $origin, ?string $destination, ?string $fallback = ''): string
    {
        $originValue = trim((string) $origin);
        $destinationValue = trim((string) $destination);

        if ($originValue !== '' && $destinationValue !== '') {
            return $originValue.' - '.$destinationValue;
        }

        return trim((string) $fallback);
    }

    public static function jam(?string $value): string
    {
        return substr(trim((string) $value), 0, 5);
    }
}
