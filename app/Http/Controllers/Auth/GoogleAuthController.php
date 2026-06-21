<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentGateway;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     * GET /auth/google/redirect
     */
    public function redirect(Request $request)
    {
        if ($request->has('intent')) {
            session(['registration_intent' => $request->intent]);
            session(['registration_plan' => $request->plan]);
        }
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     * GET /auth/google/callback
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth error: '.$e->getMessage());

            return redirect()->route('login')->with('status', 'Gagal login dengan Google. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User';
        $avatar = $googleUser->getAvatar();

        // Find or create user
        $user = User::where('email', $email)->first();
        $isNewUser = ! $user;

        if ($isNewUser) {
            // New user — create account
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(bin2hex(random_bytes(16))), // random password
                'avatar' => $avatar,
                'email_verified_at' => now(), // Google already verified
            ]);

            // Auto-assign default role
            $this->assignDefaultRole((int) $user->id);

            Log::info("New user created via Google OAuth: #{$user->id} {$email}");
        } else {
            // Update avatar if null
            if (! $user->avatar && $avatar) {
                $user->update(['avatar' => $avatar]);
            }
        }

        Auth::login($user, true);

        // New users must complete onboarding before accessing dashboard
        if ($isNewUser) {
            return redirect()->route('onboarding');
        }

        // Returning users go directly to dashboard
        if (AccessControl::userIsSuperAdmin((int) $user->id)) {
            return redirect()->intended(route('platform.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    private function assignDefaultRole(int $userId): void
    {
        try {
            if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role')) {
                return;
            }

            if ((int) DB::table('roles')->count() === 0) {
                AccessControl::syncDefaults();
            }
            AccessControl::ensureDefaultRoleReady('tenant-owner');

            if (DB::table('user_role')->where('user_id', $userId)->exists()) {
                return;
            }

            $roleId = DB::table('roles')->where('slug', 'tenant-owner')->value('id')
                ?? DB::table('roles')->where('slug', 'admin-pool')->value('id')
                ?? DB::table('roles')->where('slug', '!=', 'super-admin')->orderBy('id')->value('id');

            if ($roleId) {
                DB::table('user_role')->insert([
                    'user_id' => $userId,
                    'role_id' => (int) $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to assign role in Google OAuth: {$e->getMessage()}");
        }
    }

    private function autoProvisionTenant(int $userId, string $name, string $email): void
    {
        try {
            if (! Schema::hasTable('tenants') || ! Schema::hasTable('plans')) {
                return;
            }

            // Skip if user already has tenant
            if (Schema::hasColumn('users', 'tenant_id')) {
                $existing = DB::table('users')->where('id', $userId)->value('tenant_id');
                if ($existing) {
                    return;
                }
            }

            $tenantSlug = $this->generateTenantSlug($name);
            $plan = DB::table('plans')->where('slug', 'starter')->where('is_active', true)->first();
            if (! $plan) {
                return;
            }

            // One trial per email
            $trialDays = (int) config('saas.trial_days', 14);
            $alreadyHadTrial = DB::table('subscriptions')
                ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
                ->where('tenants.email', $email)
                ->where(function ($q) {
                    $q->where('subscriptions.status', 'trial')
                      ->orWhereNotNull('subscriptions.trial_ends_at');
                })
                ->exists();
            if ($alreadyHadTrial) {
                $trialDays = 0;
            }

            DB::transaction(function () use ($userId, $name, $email, $tenantSlug, $plan, $trialDays): void {
                $requiresPayment = $trialDays <= 0 && (float) ($plan->price_monthly ?? 0) > 0;
                $tenantId = DB::table('tenants')->insertGetId([
                    'name' => $name,
                    'slug' => $tenantSlug,
                    'email' => $email,
                    'status' => $requiresPayment ? 'pending_payment' : 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;
                $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
                    'tenant_id' => $tenantId,
                    'plan_id' => (int) $plan->id,
                    'status' => $trialDays > 0 ? 'trial' : ($requiresPayment ? 'pending_payment' : 'active'),
                    'trial_ends_at' => $trialEndsAt,
                    'starts_at' => $requiresPayment ? null : now()->toDateString(),
                    'ends_at' => $trialDays > 0 ? $trialEndsAt : ($requiresPayment ? null : now()->addMonth()->toDateString()),
                    'billing_interval' => 'monthly',
                    'grace_period_days' => config('saas.grace_period_days', 7),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($requiresPayment) {
                    PaymentGateway::createInvoice(
                        (int) $tenantId,
                        $subscriptionId,
                        (float) $plan->price_monthly,
                        now()->addDay()->toDateString(),
                    );
                }

                if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
                    $poolId = DB::table('pools')->insertGetId([
                        'name' => strtoupper($name).' POOL',
                        'code' => $tenantSlug.'-pool',
                        'tenant_id' => $tenantId,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (Schema::hasTable('pool_user')) {
                        DB::table('pool_user')->insert([
                            'pool_id' => $poolId,
                            'user_id' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (Schema::hasColumn('users', 'tenant_id')) {
                    DB::table('users')->where('id', $userId)->update(['tenant_id' => $tenantId]);
                }
            });
        } catch (\Throwable $e) {
            Log::error("Failed to provision tenant in Google OAuth: {$e->getMessage()}");
        }
    }

    private function generateTenantSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'travel-'.now()->format('His');
        }
        $baseSlug = $slug;
        $counter = 1;
        while (DB::table('tenants')->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
