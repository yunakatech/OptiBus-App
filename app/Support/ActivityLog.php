<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

        if (self::usesDatabase()) {
            try {
                DB::table('activity_logs')->insert([
                    'tag' => $payload['tag'],
                    'title' => $payload['title'],
                    'meta' => $payload['meta'],
                    'actor' => $payload['actor'],
                    'extra' => $extra !== [] ? json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                    'created_at' => $payload['created_at'],
                ]);

                return;
            } catch (\Throwable) {
                // Keep activity writes non-blocking if the database is mid-migration.
            }
        }

        Log::channel('activity')->info('activity_event', $payload);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function recent(int $limit = 50, int $offset = 0): array
    {
        if (self::usesDatabase()) {
            return DB::table('activity_logs')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->offset(max(0, $offset))
                ->limit(max(1, $limit))
                ->get(['tag', 'title', 'meta', 'actor', 'created_at'])
                ->map(static fn ($row): array => [
                    'tag' => trim((string) ($row->tag ?? 'INFO')),
                    'title' => trim((string) ($row->title ?? '')),
                    'meta' => trim((string) ($row->meta ?? '')),
                    'actor' => trim((string) ($row->actor ?? '')),
                    'created_at' => trim((string) ($row->created_at ?? '')),
                ])
                ->filter(static fn (array $row): bool => $row['title'] !== '')
                ->values()
                ->all();
        }

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
        if (self::usesDatabase()) {
            return (int) DB::table('activity_logs')->count();
        }

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

    private static function usesDatabase(): bool
    {
        try {
            return Schema::hasTable('activity_logs');
        } catch (\Throwable) {
            return false;
        }
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
