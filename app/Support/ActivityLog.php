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
        $extra = self::withActorContext($extra);
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
                    'extra' => $payload['extra'] !== [] ? json_encode($payload['extra'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
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
        $scope = PoolScope::forCurrentUser();

        if (self::usesDatabase()) {
            return self::recentFromDatabase(max(1, $limit), max(0, $offset), $scope);
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
            if (! self::isVisibleForScope($row, $scope)) {
                continue;
            }
            $items[] = $row;
            if (count($items) >= ($offset + $limit)) {
                break;
            }
        }

        return array_map(
            static fn (array $row): array => self::publicRow($row),
            array_slice($items, $offset, $limit),
        );
    }

    public static function count(): int
    {
        $scope = PoolScope::forCurrentUser();

        if (self::usesDatabase()) {
            if ($scope['all']) {
                return (int) DB::table('activity_logs')->count();
            }

            return self::countFromDatabase($scope);
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
            $row = self::parseJsonLine($line);
            if ($row !== null && self::isVisibleForScope($row, $scope)) {
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
     * @return array<string, mixed>|null
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
            'extra' => self::normalizeExtra($context['extra'] ?? []),
        ];
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<int, array<string, string>>
     */
    private static function recentFromDatabase(int $limit, int $offset, array $scope): array
    {
        if ($scope['all']) {
            return DB::table('activity_logs')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->offset($offset)
                ->limit($limit)
                ->get(self::activitySelectColumns())
                ->map(static fn ($row): array => self::publicRow(self::databaseRow($row)))
                ->filter(static fn (array $row): bool => $row['title'] !== '')
                ->values()
                ->all();
        }

        $needed = $offset + $limit;
        $items = [];
        $scanOffset = 0;
        $batchSize = max(100, min(500, $needed * 4));

        while (true) {
            $rows = DB::table('activity_logs')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->offset($scanOffset)
                ->limit($batchSize)
                ->get(self::activitySelectColumns());

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                $item = self::databaseRow($row);
                if ($item['title'] !== '' && self::isVisibleForScope($item, $scope)) {
                    $items[] = $item;
                }

                if (count($items) >= $needed) {
                    break 2;
                }
            }

            $scanned = $rows->count();
            $scanOffset += $scanned;
            if ($scanned < $batchSize) {
                break;
            }
        }

        return array_map(
            static fn (array $row): array => self::publicRow($row),
            array_slice($items, $offset, $limit),
        );
    }

    /**
     * @param array<string, mixed> $scope
     */
    private static function countFromDatabase(array $scope): int
    {
        $count = 0;
        $offset = 0;
        $batchSize = 500;

        while (true) {
            $rows = DB::table('activity_logs')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->offset($offset)
                ->limit($batchSize)
                ->get(self::activitySelectColumns());

            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                $item = self::databaseRow($row);
                if ($item['title'] !== '' && self::isVisibleForScope($item, $scope)) {
                    $count += 1;
                }
            }

            $scanned = $rows->count();
            $offset += $scanned;
            if ($scanned < $batchSize) {
                break;
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private static function activitySelectColumns(): array
    {
        $columns = ['tag', 'title', 'meta', 'actor', 'created_at'];
        if (Schema::hasColumn('activity_logs', 'extra')) {
            $columns[] = 'extra';
        }

        return $columns;
    }

    /**
     * @return array<string, mixed>
     */
    private static function databaseRow(object $row): array
    {
        return [
            'tag' => trim((string) ($row->tag ?? 'INFO')),
            'title' => trim((string) ($row->title ?? '')),
            'meta' => trim((string) ($row->meta ?? '')),
            'actor' => trim((string) ($row->actor ?? '')),
            'created_at' => trim((string) ($row->created_at ?? '')),
            'extra' => self::normalizeExtra($row->extra ?? []),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, string>
     */
    private static function publicRow(array $row): array
    {
        return [
            'tag' => trim((string) ($row['tag'] ?? 'INFO')),
            'title' => trim((string) ($row['title'] ?? '')),
            'meta' => trim((string) ($row['meta'] ?? '')),
            'actor' => trim((string) ($row['actor'] ?? '')),
            'created_at' => trim((string) ($row['created_at'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private static function withActorContext(array $extra): array
    {
        try {
            $user = auth()->user();
            if ($user) {
                $extra['user_id'] ??= (int) $user->id;
                $extra['user_email'] ??= (string) ($user->email ?? '');
                $extra['user_name'] ??= (string) ($user->name ?? '');
            }
        } catch (\Throwable) {
            // Logging must stay non-blocking even outside an authenticated request.
        }

        return $extra;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeExtra(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $scope
     */
    private static function isVisibleForScope(array $row, array $scope): bool
    {
        if (($scope['all'] ?? false) === true) {
            return true;
        }

        $extra = self::normalizeExtra($row['extra'] ?? []);
        if (self::extraMatchesScope($extra, $scope)) {
            return true;
        }

        if (self::hasBusinessScopeHints($extra)) {
            return false;
        }

        return self::actorMatchesCurrentUser((string) ($row['actor'] ?? ''), $extra);
    }

    /**
     * @param array<string, mixed> $extra
     * @param array<string, mixed> $scope
     */
    private static function extraMatchesScope(array $extra, array $scope): bool
    {
        $poolIds = self::integerList($scope['pool_ids'] ?? []);
        $routeIds = self::integerList($scope['route_ids'] ?? []);
        $routeNames = self::stringList($scope['route_names'] ?? []);
        $labels = self::stringList($scope['labels'] ?? []);

        if (self::intersects(self::integerList($extra['pool_id'] ?? []), $poolIds)) {
            return true;
        }

        if (self::intersects(self::integerList($extra['pool_ids'] ?? []), $poolIds)) {
            return true;
        }

        if (self::intersects(self::integerList($extra['route_id'] ?? []), $routeIds)) {
            return true;
        }

        if (self::intersects(self::integerList($extra['rute_id'] ?? []), $routeIds)) {
            return true;
        }

        if (self::intersects(self::stringList($extra['rute'] ?? []), $routeNames)) {
            return true;
        }

        if (self::bookingIdsMatchScope(self::integerList($extra['booking_id'] ?? []), $routeNames)) {
            return true;
        }

        if (self::bookingIdsMatchScope(self::integerList($extra['booking_ids'] ?? []), $routeNames)) {
            return true;
        }

        if (self::tripAssignmentIdsMatchScope(self::integerList($extra['trip_assignment_id'] ?? []), $routeNames)) {
            return true;
        }

        if (self::tripAssignmentIdsMatchScope(self::integerList($extra['trip_assignment_ids'] ?? []), $routeNames)) {
            return true;
        }

        if (self::charterIdsMatchScope(self::integerList($extra['charter_id'] ?? []), $poolIds, $labels)) {
            return true;
        }

        if (self::charterIdsMatchScope(self::integerList($extra['charter_ids'] ?? []), $poolIds, $labels)) {
            return true;
        }

        if (self::luggageIdsMatchScope(self::integerList($extra['luggage_id'] ?? []), $poolIds, $routeIds, $routeNames)) {
            return true;
        }

        if (self::luggageIdsMatchScope(self::integerList($extra['luggage_ids'] ?? []), $poolIds, $routeIds, $routeNames)) {
            return true;
        }

        if (self::luggageResiMatchScope(self::stringList($extra['kode_resi'] ?? []), $poolIds, $routeIds, $routeNames)) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $extra
     */
    private static function hasBusinessScopeHints(array $extra): bool
    {
        $keys = [
            'pool_id',
            'pool_ids',
            'route_id',
            'rute_id',
            'rute',
            'booking_id',
            'booking_ids',
            'trip_assignment_id',
            'trip_assignment_ids',
            'charter_id',
            'charter_ids',
            'luggage_id',
            'luggage_ids',
            'kode_resi',
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $extra) && self::valueIsFilled($extra[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $extra
     */
    private static function actorMatchesCurrentUser(string $actor, array $extra): bool
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            $userId = (int) ($user->id ?? 0);
            if ($userId > 0 && (int) ($extra['user_id'] ?? 0) === $userId) {
                return true;
            }

            $actor = mb_strtolower(trim($actor));
            $candidates = array_filter([
                mb_strtolower(trim((string) ($user->email ?? ''))),
                mb_strtolower(trim((string) ($user->name ?? ''))),
            ]);

            return $actor !== '' && in_array($actor, $candidates, true);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param array<int, int> $ids
     * @param array<int, string> $routeNames
     */
    private static function bookingIdsMatchScope(array $ids, array $routeNames): bool
    {
        if ($ids === [] || $routeNames === [] || ! Schema::hasTable('bookings')) {
            return false;
        }

        return DB::table('bookings')
            ->whereIn('id', $ids)
            ->whereIn('rute', $routeNames)
            ->exists();
    }

    /**
     * @param array<int, int> $ids
     * @param array<int, string> $routeNames
     */
    private static function tripAssignmentIdsMatchScope(array $ids, array $routeNames): bool
    {
        if ($ids === [] || $routeNames === [] || ! Schema::hasTable('trip_assignments')) {
            return false;
        }

        return DB::table('trip_assignments')
            ->whereIn('id', $ids)
            ->whereIn('rute', $routeNames)
            ->exists();
    }

    /**
     * @param array<int, int> $ids
     * @param array<int, int> $poolIds
     * @param array<int, string> $labels
     */
    private static function charterIdsMatchScope(array $ids, array $poolIds, array $labels): bool
    {
        if ($ids === [] || ! Schema::hasTable('charters')) {
            return false;
        }

        $canUsePool = $poolIds !== [] && Schema::hasColumn('charters', 'pool_id');
        if (! $canUsePool && $labels === []) {
            return false;
        }

        $query = DB::table('charters')->whereIn('id', $ids);

        $query->where(function ($builder) use ($canUsePool, $poolIds, $labels): void {
            $hasClause = false;
            if ($canUsePool) {
                $builder->whereIn('pool_id', $poolIds);
                $hasClause = true;
            }

            if ($labels !== []) {
                $method = $hasClause ? 'orWhere' : 'where';
                $builder->{$method}(function ($routeBuilder) use ($labels): void {
                    $routeBuilder
                        ->whereIn('pickup_point', $labels)
                        ->orWhereIn('drop_point', $labels);
                });
            }
        });

        return $query->exists();
    }

    /**
     * @param array<int, int> $ids
     * @param array<int, int> $poolIds
     * @param array<int, int> $routeIds
     * @param array<int, string> $routeNames
     */
    private static function luggageIdsMatchScope(array $ids, array $poolIds, array $routeIds, array $routeNames): bool
    {
        if ($ids === [] || ! Schema::hasTable('luggages')) {
            return false;
        }

        $query = DB::table('luggages')->whereIn('id', $ids);
        self::appendLuggageScope($query, $poolIds, $routeIds, $routeNames);

        return $query->exists();
    }

    /**
     * @param array<int, string> $codes
     * @param array<int, int> $poolIds
     * @param array<int, int> $routeIds
     * @param array<int, string> $routeNames
     */
    private static function luggageResiMatchScope(array $codes, array $poolIds, array $routeIds, array $routeNames): bool
    {
        if ($codes === [] || ! Schema::hasTable('luggages')) {
            return false;
        }

        $query = DB::table('luggages')->whereIn('kode_resi', $codes);
        self::appendLuggageScope($query, $poolIds, $routeIds, $routeNames);

        return $query->exists();
    }

    /**
     * @param array<int, int> $poolIds
     * @param array<int, int> $routeIds
     * @param array<int, string> $routeNames
     */
    private static function appendLuggageScope($query, array $poolIds, array $routeIds, array $routeNames): void
    {
        $canUsePool = $poolIds !== [] && Schema::hasColumn('luggages', 'pool_id');
        $canUseRouteId = $routeIds !== [] && Schema::hasColumn('luggages', 'rute_id');
        $canUseRouteName = $routeNames !== [];

        if (! $canUsePool && ! $canUseRouteId && ! $canUseRouteName) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($builder) use ($canUsePool, $canUseRouteId, $canUseRouteName, $poolIds, $routeIds, $routeNames): void {
            $hasClause = false;

            if ($canUsePool) {
                $builder->whereIn('pool_id', $poolIds);
                $hasClause = true;
            }

            if ($canUseRouteId) {
                $hasClause ? $builder->orWhereIn('rute_id', $routeIds) : $builder->whereIn('rute_id', $routeIds);
                $hasClause = true;
            }

            if ($canUseRouteName) {
                $hasClause ? $builder->orWhereIn('rute', $routeNames) : $builder->whereIn('rute', $routeNames);
            }
        });
    }

    /**
     * @return array<int, int>
     */
    private static function integerList(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_filter(array_map(
            static fn ($item): int => (int) $item,
            $values,
        ), static fn (int $item): bool => $item > 0)));
    }

    /**
     * @return array<int, string>
     */
    private static function stringList(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_filter(array_map(
            static fn ($item): string => trim((string) $item),
            $values,
        ), static fn (string $item): bool => $item !== '')));
    }

    /**
     * @param array<int, mixed> $left
     * @param array<int, mixed> $right
     */
    private static function intersects(array $left, array $right): bool
    {
        return $left !== [] && $right !== [] && array_intersect($left, $right) !== [];
    }

    private static function valueIsFilled(mixed $value): bool
    {
        if (is_array($value)) {
            return array_filter($value, static fn ($item): bool => trim((string) $item) !== '') !== [];
        }

        return trim((string) $value) !== '';
    }
}
