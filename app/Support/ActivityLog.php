<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityLog
{
    public static function write(string $tag, string $title, string $meta = '', ?string $actor = null, array $extra = []): void
    {
        $payload = [
            'tag' => strtoupper(trim($tag)) !== '' ? strtoupper(trim($tag)) : 'INFO',
            'title' => trim($title),
            'meta' => trim($meta),
            'actor' => trim((string) ($actor ?? '')),
            'created_at' => now()->toDateTimeString(),
            'extra' => $extra,
        ];

        Log::channel('activity')->info('activity_event', $payload);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function recent(int $limit = 50, int $offset = 0): array
    {
        $path = storage_path('logs/activity.log');
        if (! is_file($path)) {
            return [];
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines) || $lines === []) {
            return [];
        }

        $items = [];
        for ($i = count($lines) - 1; $i >= 0; $i -= 1) {
            $row = self::parseJsonLine($lines[$i]);
            if ($row === null) {
                continue;
            }
            $items[] = $row;
            if (count($items) >= ($offset + $limit)) {
                break;
            }
        }

        return array_slice($items, $offset, $limit);
    }

    public static function count(): int
    {
        $path = storage_path('logs/activity.log');
        if (! is_file($path)) {
            return 0;
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines) || $lines === []) {
            return 0;
        }

        $count = 0;
        foreach ($lines as $line) {
            if (self::parseJsonLine($line) !== null) {
                $count += 1;
            }
        }

        return $count;
    }

    /**
     * @return array<string, string>|null
     */
    private static function parseJsonLine(string $line): ?array
    {
        $decoded = json_decode($line, true);
        if (! is_array($decoded)) {
            return null;
        }

        $context = isset($decoded['context']) && is_array($decoded['context']) ? $decoded['context'] : [];
        $title = trim((string) ($context['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        $createdAt = trim((string) ($context['created_at'] ?? ''));
        if ($createdAt === '' && isset($decoded['datetime']) && is_string($decoded['datetime'])) {
            try {
                $createdAt = Carbon::parse($decoded['datetime'])->toDateTimeString();
            } catch (\Throwable) {
                $createdAt = '';
            }
        }

        return [
            'tag' => trim((string) ($context['tag'] ?? 'INFO')),
            'title' => $title,
            'meta' => trim((string) ($context['meta'] ?? '')),
            'actor' => trim((string) ($context['actor'] ?? '')),
            'created_at' => $createdAt,
        ];
    }
}

