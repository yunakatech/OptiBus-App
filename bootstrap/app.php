<?php

use App\Http\Middleware\EnsureFeature;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\EnsureTenantSubscriptionActive;
use App\Http\Middleware\EnsureSuperAdminTenantContext;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request as HttpRequest;

$runningOnVercel = isset($_SERVER['VERCEL_URL'])
    || isset($_SERVER['VERCEL'])
    || getenv('VERCEL')
    || isset($_ENV['VERCEL']);
$vercelStoragePath = '/tmp/storage';

if ($runningOnVercel) {
    $deploymentKey = preg_replace(
        '/[^A-Za-z0-9_.-]/',
        '_',
        (string) (getenv('VERCEL_GIT_COMMIT_SHA')
            ?: getenv('VERCEL_URL')
            ?: ($_SERVER['VERCEL_URL'] ?? 'vercel')),
    );

    if (! is_string($deploymentKey) || $deploymentKey === '') {
        $deploymentKey = 'vercel';
    }

    $cacheDirectory = $vercelStoragePath.'/bootstrap/cache';
    $cacheFiles = [
        'APP_SERVICES_CACHE' => $cacheDirectory."/services-{$deploymentKey}.php",
        'APP_PACKAGES_CACHE' => $cacheDirectory."/packages-{$deploymentKey}.php",
        'APP_CONFIG_CACHE' => $cacheDirectory."/config-{$deploymentKey}.php",
        'APP_ROUTES_CACHE' => $cacheDirectory."/routes-{$deploymentKey}.php",
        'APP_EVENTS_CACHE' => $cacheDirectory."/events-{$deploymentKey}.php",
    ];

    foreach ($cacheFiles as $envKey => $envValue) {
        putenv("{$envKey}={$envValue}");
        $_ENV[$envKey] = $envValue;
        $_SERVER[$envKey] = $envValue;
    }
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = (string) env('TRUSTED_PROXIES', '*');

        $middleware->trustProxies(
            at: $trustedProxies !== '' ? $trustedProxies : '*',
            headers: HttpRequest::HEADER_X_FORWARDED_FOR
                | HttpRequest::HEADER_X_FORWARDED_HOST
                | HttpRequest::HEADER_X_FORWARDED_PORT
                | HttpRequest::HEADER_X_FORWARDED_PROTO
                | HttpRequest::HEADER_X_FORWARDED_PREFIX
                | HttpRequest::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            EnsureSuperAdminTenantContext::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/webhooks/mayar',
        ]);

        $middleware->redirectUsersTo(function (HttpRequest $request): string {
            $userId = (int) ($request->user()?->id ?? 0);

            if ($userId > 0 && \App\Support\AccessControl::userIsSuperAdmin($userId)) {
                return route('platform.dashboard');
            }

            if ($userId > 0 && \App\Support\AccessControl::can($userId, 'dashboard.view')) {
                return route('dashboard');
            }

            return route('subscription.index');
        });

        $middleware->alias([
            'permission' => EnsurePermission::class,
            'feature' => EnsureFeature::class,
            'subscription.active' => EnsureTenantSubscriptionActive::class,
            'superadmin.tenant-context' => EnsureSuperAdminTenantContext::class,
            'superadmin.redirect' => RedirectSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if ($runningOnVercel) {
    $app->useStoragePath($vercelStoragePath);

    // Isolate runtime caches per deployment so new routes are visible immediately.
    foreach (['app/public', 'framework/cache/data', 'framework/views', 'framework/sessions', 'logs', 'bootstrap/cache'] as $folder) {
        $path = $vercelStoragePath.'/'.$folder;
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
    }
}

return $app;
