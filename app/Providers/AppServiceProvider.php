<?php

namespace App\Providers;

use App\Listeners\CreateTenantOnRegistration;
use App\Support\AccessControl;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->registerEventListeners();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Auto-provision tenant + subscription on user registration
        Event::listen(Registered::class, CreateTenantOnRegistration::class);

        // Redirect super admins to platform dashboard after login
        Event::listen(Login::class, function (Login $event): void {
            try {
                $userId = (int) ($event->user?->id ?? 0);
                if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
                    $request = request();
                    if ($request && $request->hasSession()) {
                        $request->session()->put('url.intended', route('platform.dashboard'));
                    }
                }
            } catch (\Throwable) {
                // Silently fail — don't break login for non-super-admin users
            }
        });
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
}
