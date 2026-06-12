<?php

namespace App\Providers;

use App\Listeners\CreateTenantOnRegistration;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePerformanceTelemetry();
        $this->flushRequestScopedCachesOnTerminate();
        $this->registerEventListeners();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Auto-provision tenant + subscription on user registration
        Event::listen(Registered::class, CreateTenantOnRegistration::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        if ((bool) config('app.force_https')) {
            URL::forceScheme('https');
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configurePerformanceTelemetry(): void
    {
        $slowQueryMs = (int) env('PERF_SLOW_QUERY_MS', 0);
        $queryCountEnabled = (bool) env('PERF_QUERY_COUNT_ENABLED', false);

        if ($slowQueryMs <= 0 && ! $queryCountEnabled) {
            return;
        }

        $queryCount = 0;
        DB::listen(function (QueryExecuted $query) use ($slowQueryMs, $queryCountEnabled, &$queryCount): void {
            if ($queryCountEnabled) {
                $queryCount += 1;
            }

            if ($slowQueryMs > 0 && $query->time >= $slowQueryMs) {
                Log::warning('performance.slow_query', [
                    'time_ms' => $query->time,
                    'connection' => $query->connectionName,
                    'route' => $this->currentRequestPath(),
                    'sql' => $query->sql,
                ]);
            }
        });

        if ($queryCountEnabled) {
            app()->terminating(function () use (&$queryCount): void {
                Log::info('performance.query_count', [
                    'count' => $queryCount,
                    'route' => $this->currentRequestPath(),
                ]);
            });
        }
    }

    protected function flushRequestScopedCachesOnTerminate(): void
    {
        app()->terminating(static function (): void {
            PoolScope::flushRequestCache();
            FeatureGate::flushRequestCache();
        });
    }

    protected function currentRequestPath(): string
    {
        try {
            return app()->bound('request') ? request()->path() : 'cli';
        } catch (\Throwable) {
            return 'unknown';
        }
    }
}
