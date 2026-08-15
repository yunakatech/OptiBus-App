<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentGateway;
use App\Services\TenantInvitationService;
use App\Support\AccessControl;
use App\Support\TenantBillingAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly TenantInvitationService $tenantInvitationService,
    ) {}

    /**
     * Redirect to Google OAuth.
     * GET /auth/google/redirect
     */
    public function redirect(Request $request)
    {
        if ($request->filled('invite')) {
            session(['google_invitation_token' => trim((string) $request->query('invite'))]);
        }

        $intent = trim((string) $request->query('intent', ''));
        $plan = trim((string) $request->query('plan', ''));
        if ($intent !== '' || $plan !== '') {
            if ($intent === 'payment') {
                $intent = 'paid';
            }
            $intent = in_array($intent, ['trial', 'paid'], true)
                ? $intent
                : ($plan !== '' ? 'paid' : 'trial');

            session([
                'registration_intent' => $intent,
                'registration_plan' => $intent === 'trial' ? 'starter' : ($plan !== '' ? $plan : 'starter'),
            ]);
        } elseif (! $request->filled('invite')) {
            // Ordinary Google sign-in must not inherit a previous paid checkout flow.
            session([
                'registration_intent' => 'trial',
                'registration_plan' => 'starter',
            ]);
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
        $inviteToken = (string) $request->session()->pull('google_invitation_token', '');

        $invitationResult = $this->tenantInvitationService->consumeForGoogle($email, $name, $avatar, $inviteToken);
        if (($invitationResult['status'] ?? '') === 'consumed' && $invitationResult['user'] instanceof User) {
            $user = $invitationResult['user'];
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        }

        if (in_array(($invitationResult['status'] ?? ''), ['email_mismatch', 'tenant_conflict', 'limit_reached', 'tenant_unavailable'], true)) {
            $message = match ($invitationResult['status']) {
                'email_mismatch' => 'Email Google tidak sesuai dengan undangan tenant.',
                'tenant_conflict' => 'Akun Google ini sudah terhubung ke tenant lain.',
                'limit_reached' => 'Batas user paket tenant sudah tercapai.',
                'tenant_unavailable' => 'Tenant sudah diarsipkan atau sedang dihapus.',
                default => 'Undangan tenant tidak dapat digunakan.',
            };

            return redirect()->route('login')->with('status', $message);
        }

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
            $updates = [];
            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }
            if (! $user->avatar && $avatar) {
                $updates['avatar'] = $avatar;
            }
            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }
        }

        $billingAccess = TenantBillingAccess::forUser((int) $user->id);
        if (TenantBillingAccess::tenantUnavailable($billingAccess)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'tenant_unavailable');
        }

        Auth::login($user, true);

        if (AccessControl::userIsSuperAdmin((int) $user->id)) {
            $request->session()->forget('active_tenant_id');
            $request->session()->forget('active_pool_id');
            $request->session()->forget([
                'registration_plan',
                'registration_intent',
                'registration_onboarding_defaults',
                'registration_onboarding_pending',
            ]);

            return redirect()->intended(route('platform.dashboard'));
        }

        // Any Google account without a tenant must complete onboarding first.
        if ((int) ($user->tenant_id ?? 0) <= 0) {
            $request->session()->put('registration_onboarding_pending', true);

            return redirect()->route('onboarding');
        }

        $request->session()->forget('registration_onboarding_pending');

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
                        now()->addDays((int) config('saas.invoice_payment_days', 1))->toDateString(),
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
