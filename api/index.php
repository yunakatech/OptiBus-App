<?php

if (! function_exists('optibus_vercel_request_expects_api')) {
    function optibus_vercel_request_expects_api(): bool
    {
        if (isset($_SERVER['HTTP_X_INERTIA'])) {
            return false;
        }

        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? ''));

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || $requestedWith === 'xmlhttprequest';
    }
}

if (! function_exists('optibus_vercel_set_request_path')) {
    function optibus_vercel_set_request_path(string $path, string $query): void
    {
        $_SERVER['REQUEST_URI'] = $path.($query !== '' ? '?'.$query : '');
        $_SERVER['PATH_INFO'] = $path;
        $_SERVER['ORIG_PATH_INFO'] = $path;
    }
}

if (! function_exists('optibus_vercel_set_public_front_controller')) {
    function optibus_vercel_set_public_front_controller(): void
    {
        $publicIndex = __DIR__.'/../public/index.php';
        $_SERVER['SCRIPT_FILENAME'] = $publicIndex;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['ORIG_SCRIPT_NAME'] = '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public';
    }
}

if (! function_exists('optibus_vercel_normalize_request_path')) {
    function optibus_vercel_normalize_request_path(): void
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '');
        $query = (string) (parse_url($uri, PHP_URL_QUERY) ?: '');

        foreach (['/api/index.php', '/index.php'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $path = substr($path, strlen($prefix)) ?: '/';
                optibus_vercel_set_request_path($path, $query);

                break;
            }
        }
    }
}

if (! function_exists('optibus_vercel_restore_api_prefix')) {
    function optibus_vercel_restore_api_prefix(): void
    {
        optibus_vercel_normalize_request_path();

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '');
        $query = (string) (parse_url($uri, PHP_URL_QUERY) ?: '');

        if ($path === '' || str_starts_with($path, '/api/')) {
            return;
        }

        $trimmed = ltrim($path, '/');
        $segment = explode('/', $trimmed, 2)[0] ?? '';
        $expectsApi = optibus_vercel_request_expects_api();
        $restore = match ($segment) {
            'plans', 'webhooks', 'build' => true,
            'bookings', 'master', 'ops', 'user' => $expectsApi,
            'admin' => $expectsApi,
            default => false,
        };

        if (! $restore) {
            return;
        }

        $restoredPath = '/api/'.$trimmed;
        optibus_vercel_set_request_path($restoredPath, $query);
    }
}

optibus_vercel_set_public_front_controller();
optibus_vercel_restore_api_prefix();

// Forward Vercel requests to the normal Laravel entry point
require __DIR__.'/../public/index.php';
