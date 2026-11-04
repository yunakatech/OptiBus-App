<?php

namespace App\Support;

final class DeferredInertia
{
    public static function opsEnabled(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        return self::resolve(config('ui.ops_deferred'), self::runningOnVercel());
    }

    public static function resolve(mixed $value, bool $onVercel): bool
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return ! $onVercel;
        }

        return filter_var(
            is_string($value) ? trim($value) : $value,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE,
        ) ?? ! $onVercel;
    }

    public static function runningOnVercel(): bool
    {
        return isset($_SERVER['VERCEL_URL'])
            || getenv('VERCEL')
            || env('VERCEL')
            || isset($_ENV['VERCEL']);
    }
}
