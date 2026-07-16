<?php

namespace App\Support;

final class DeferredInertia
{
    public static function opsEnabled(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $override = filter_var(
            env('OPS_INERTIA_DEFERRED'),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE,
        );

        if ($override !== null) {
            return $override;
        }

        return ! self::runningOnVercel();
    }

    public static function runningOnVercel(): bool
    {
        return isset($_SERVER['VERCEL_URL'])
            || getenv('VERCEL')
            || env('VERCEL')
            || isset($_ENV['VERCEL']);
    }
}
