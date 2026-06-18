<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Support\AccessControl;
use App\Support\TenantBillingAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegisterResponseContract::class, function () {
            return new class implements RegisterResponseContract
            {
                public function toResponse($request)
                {
                    if ($request->wantsJson()) {
                        return new JsonResponse('', 201);
                    }

                    $userId = (int) ($request->user()?->id ?? auth()->id() ?? 0);
                    if ($userId <= 0) {
                        return redirect()->route('subscription.index');
                    }

                    $billingAccess = TenantBillingAccess::forUser($userId);
                    if ($billingAccess['locked'] ?? false) {
                        return redirect()->route('subscription.index');
                    }

                    if (AccessControl::can($userId, 'dashboard.view')) {
                        return redirect()->route('dashboard');
                    }

                    return redirect()->route('subscription.index');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn () => redirect()->route('login'));

        Fortify::requestPasswordResetLinkView(fn () => redirect()->route('login'));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(function (Request $request) {
            // Store registration source for the provisioning listener.
            $plan = trim((string) $request->query('plan', ''));
            $intent = trim((string) $request->query('intent', ''));
            if ($intent === 'payment') {
                $intent = 'paid';
            }
            $intent = in_array($intent, ['trial', 'paid'], true)
                ? $intent
                : ($plan !== '' ? 'paid' : 'trial');

            if ($intent === 'trial') {
                $plan = 'starter';
            }

            if ($plan !== '') {
                session(['registration_plan' => $plan]);
            } else {
                session()->forget('registration_plan');
            }
            session(['registration_intent' => $intent]);

            // Load plans
            $plans = [];
            if (Schema::hasTable('plans')) {
                $plans = DB::table('plans')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'name', 'slug', 'description', 'price_monthly'])
                    ->map(fn ($p) => [
                        'id' => (int) $p->id,
                        'name' => (string) $p->name,
                        'slug' => (string) $p->slug,
                        'description' => (string) ($p->description ?? ''),
                        'price_monthly' => (float) $p->price_monthly,
                    ])
                    ->all();
            }

            return Inertia::render('auth/Register', [
                'plans' => $plans,
                'passwordRules' => '',
                'selectedPlan' => $plan !== '' ? $plan : config('saas.default_plan', 'starter'),
                'registrationIntent' => $intent,
            ]);
        });

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
