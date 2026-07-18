<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Per-request static cache for Schema::hasTable and Schema::hasColumn.
 *
 * On Vercel (Supabase PostgreSQL), each Schema::hasTable / Schema::hasColumn
 * call queries information_schema over the network and can take 20–100 ms.
 * With dozens of such calls per request in AdminOpsApiController, the total
 * easily exceeds the Vercel function timeout. This class caches the results
 * for the lifetime of the PHP process (one request on Vercel serverless).
 */
final class SchemaCache
{
    /** @var array<string, bool> */
    private static array $tables = [];

    /** @var array<string, bool> */
    private static array $columns = [];

    public static function hasTable(string $table): bool
    {
        if (! array_key_exists($table, self::$tables)) {
            self::$tables[$table] = Schema::hasTable($table);
        }

        return self::$tables[$table];
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $key = $table.'.'.$column;
        if (! array_key_exists($key, self::$columns)) {
            self::$columns[$key] = Schema::hasColumn($table, $column);
        }

        return self::$columns[$key];
    }

    /**
     * Pre-warm the cache for a set of tables and their columns in a single
     * information_schema query instead of N individual lookups.
     *
     * @param  array<string, list<string>>  $tableColumns  e.g. ['trips' => ['driver_id', 'pool_id']]
     */
    public static function warm(array $tableColumns): void
    {
        // Mark uncached tables as pending
        $uncachedTables = array_filter(
            array_keys($tableColumns),
            static fn (string $t): bool => ! array_key_exists($t, self::$tables),
        );

        if ($uncachedTables === []) {
            return;
        }

        // Bulk-check table existence
        $existingTables = \Illuminate\Support\Facades\DB::table('information_schema.tables')
            ->whereIn('table_name', $uncachedTables)
            ->where('table_schema', config('database.connections.pgsql.search_path', 'public'))
            ->pluck('table_name')
            ->map(static fn ($t): string => (string) $t)
            ->all();

        foreach ($uncachedTables as $table) {
            self::$tables[$table] = in_array($table, $existingTables, true);
        }

        // Bulk-check columns for tables that exist
        $columnsToCheck = [];
        foreach ($tableColumns as $table => $columns) {
            if (! self::$tables[$table]) {
                // Mark all columns false for non-existent tables
                foreach ($columns as $column) {
                    self::$columns[$table.'.'.$column] = false;
                }
                continue;
            }
            foreach ($columns as $column) {
                $key = $table.'.'.$column;
                if (! array_key_exists($key, self::$columns)) {
                    $columnsToCheck[$table][] = $column;
                }
            }
        }

        if ($columnsToCheck === []) {
            return;
        }

        // Flatten for a single IN query
        $pairs = [];
        foreach ($columnsToCheck as $table => $cols) {
            foreach ($cols as $col) {
                $pairs[] = ['table' => $table, 'column' => $col];
            }
        }

        $tableNames = array_unique(array_column($pairs, 'table'));
        $columnNames = array_unique(array_column($pairs, 'column'));

        $found = \Illuminate\Support\Facades\DB::table('information_schema.columns')
            ->whereIn('table_name', $tableNames)
            ->whereIn('column_name', $columnNames)
            ->where('table_schema', config('database.connections.pgsql.search_path', 'public'))
            ->get(['table_name', 'column_name'])
            ->map(static fn ($row): string => $row->table_name.'.'.$row->column_name)
            ->all();

        foreach ($pairs as ['table' => $table, 'column' => $col]) {
            self::$columns[$table.'.'.$col] = in_array($table.'.'.$col, $found, true);
        }
    }

    /** Reset all caches (useful in tests). */
    public static function flush(): void
    {
        self::$tables = [];
        self::$columns = [];
    }
}
