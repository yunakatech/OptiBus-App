<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function redirect()
    {
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

        if (! $user) {
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

            // Auto-provision tenant if SaaS tables exist
            $this->autoProvisionTenant((int) $user->id, $name, $email);

            Log::info("New user created via Google OAuth: #{$user->id} {$email}");
        } else {
            // Update avatar if null
            if (! $user->avatar && $avatar) {
                $user->update(['avatar' => $avatar]);
            }
        }

        Auth::login($user, true);

        // Redirect super admins to platform dashboard
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

            if (DB::table('user_role')->where('user_id', $userId)->exists()) {
                return;
            }

            $roleId = DB::table('roles')->where('slug', 'admin-pool')->value('id')
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

            $trialDays = (int) config('saas.trial_days', 14);

            DB::transaction(function () use ($userId, $name, $email, $tenantSlug, $plan, $trialDays): void {
                $tenantId = DB::table('tenants')->insertGetId([
                    'name' => $name,
                    'slug' => $tenantSlug,
                    'email' => $email,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;
                DB::table('subscriptions')->insert([
                    'tenant_id' => $tenantId,
                    'plan_id' => (int) $plan->id,
                    'status' => $trialDays > 0 ? 'trial' : 'active',
                    'trial_ends_at' => $trialEndsAt,
                    'starts_at' => now()->toDateString(),
                    'ends_at' => $trialDays > 0 ? $trialEndsAt : now()->addMonth()->toDateString(),
                    'billing_interval' => 'monthly',
                    'grace_period_days' => config('saas.grace_period_days', 7),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

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
