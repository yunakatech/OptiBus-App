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

    /**
     * @return array<int, string>
     */
    public static function jamList(mixed $value, mixed $fallback = null): array
    {
        $items = self::jamItems($value);

        if ($items === [] && $fallback !== null) {
            $items = self::jamItems($fallback);
        }

        return array_values(array_unique($items));
    }

    public static function jamSummary(mixed $value, mixed $fallback = null): string
    {
        return implode(', ', self::jamList($value, $fallback));
    }

    /**
     * @return array<int, string>
     */
    private static function jamItems(mixed $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/[,\s|]+/', $trimmed) ?: [];
            }
        } else {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            $jam = self::jam((string) $item);
            if ($jam !== '') {
                $normalized[] = $jam;
            }
        }

        return $normalized;
    }
}
