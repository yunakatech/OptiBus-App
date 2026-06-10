<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Show onboarding form for Google OAuth users.
     * GET /onboarding
     */
    public function show(): Response
    {
        return Inertia::render('Onboarding', [
            'user_name' => Auth::user()?->name ?? '',
            'user_email' => Auth::user()?->email ?? '',
        ]);
    }

    /**
     * Process onboarding — provision tenant with user's travel data.
     * POST /onboarding
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'travel_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'route' => ['required', 'string', 'max:120'],
        ]);

        $travelName = trim($data['travel_name']);
        $phone = trim($data['phone']);
        $routeText = trim($data['route']);
        $userId = (int) $user->id;
        $email = $user->email;
        $tenantSlug = $this->generateSlug($travelName);

        try {
            DB::transaction(function () use ($userId, $travelName, $phone, $routeText, $email, $tenantSlug): void {
                // 1. Create tenant
                $tenantId = (int) DB::table('tenants')->insertGetId([
                    'name' => $travelName,
                    'slug' => $tenantSlug,
                    'email' => $email,
                    'phone' => $phone,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Create subscription (check for trial eligibility)
                $plan = DB::table('plans')->where('slug', 'starter')->where('is_active', true)->first();
                if (! $plan) {
                    return;
                }

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

                // 3. Create pool + route
                if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
                    $poolName = strtoupper($travelName).' POOL';
                    $poolCode = $tenantSlug.'-pool';
                    if (DB::table('pools')->where('code', $poolCode)->exists()) {
                        $poolCode = $tenantSlug.'-pool-'.now()->format('His');
                    }

                    $poolId = (int) DB::table('pools')->insertGetId([
                        'name' => $poolName,
                        'code' => $poolCode,
                        'tenant_id' => $tenantId,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Auto-create route from text
                    if ($routeText !== '' && Schema::hasTable('routes') && Schema::hasTable('pool_route')) {
                        $parts = array_map('trim', explode('-', $routeText, 2));
                        $origin = $parts[0] ?? '';
                        $destination = $parts[1] ?? '';
                        $routeName = $origin && $destination ? strtoupper($origin.' -> '.$destination) : strtoupper($routeText);

                        $existingId = DB::table('routes')->where('name', $routeName)->value('id');
                        $routeId = $existingId
                            ? (int) $existingId
                            : (int) DB::table('routes')->insertGetId([
                                'name' => $routeName,
                                'origin' => $origin !== '' ? $origin : null,
                                'destination' => $destination !== '' ? $destination : null,
                                'tenant_id' => $tenantId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                        DB::table('pool_route')->insert([
                            'pool_id' => $poolId,
                            'route_id' => $routeId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Assign user to pool
                    if (Schema::hasTable('pool_user')) {
                        DB::table('pool_user')->insert([
                            'pool_id' => $poolId,
                            'user_id' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 4. Assign tenant_id to user
                if (Schema::hasColumn('users', 'tenant_id')) {
                    DB::table('users')->where('id', $userId)->update(['tenant_id' => $tenantId]);
                }
            });
        } catch (\Throwable $e) {
            Log::error("Onboarding failed for user #{$userId}: {$e->getMessage()}");

            return back()->withErrors(['travel_name' => 'Gagal menyimpan data. Silakan coba lagi.']);
        }

        Log::info("Onboarding complete for user #{$userId}: {$travelName}");

        return redirect()->route('dashboard');
    }

    private function generateSlug(string $name): string
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
