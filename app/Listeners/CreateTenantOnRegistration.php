<?php

namespace App\Listeners;

use App\Services\PaymentGateway;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateTenantOnRegistration
{
    /**
     * Handle the Registered event — auto-create tenant, subscription, and default pool.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $userId = (int) $user->id;
        $userName = trim((string) $user->name);
        if ($userId <= 0 || $userName === '') {
            return;
        }

        // Only provision if SaaS tables exist
        if (! $this->saasReady()) {
            return;
        }

        // Don't double-provision — check if user already has a tenant_id
        if (Schema::hasColumn('users', 'tenant_id')) {
            $existingTenantId = (int) DB::table('users')->where('id', $userId)->value('tenant_id');
            if ($existingTenantId > 0) {
                return; // Already provisioned
            }
        }

        // Get the selected plan and flow from session or request input.
        $requestedPlanSlug = trim((string) (request()->input('plan') ?: session('registration_plan') ?: config('saas.default_plan', 'starter')));
        $registrationIntent = trim((string) (request()->input('registration_intent') ?: request()->input('intent') ?: session('registration_intent') ?: ''));
        $registrationIntent = in_array($registrationIntent, ['trial', 'payment'], true)
            ? $registrationIntent
            : ($requestedPlanSlug !== '' && request()->has('plan') ? 'payment' : 'trial');

        $planSlug = $registrationIntent === 'trial'
            ? 'starter'
            : ($requestedPlanSlug !== '' ? $requestedPlanSlug : (string) config('saas.default_plan', 'starter'));

        $plan = DB::table('plans')->where('slug', $planSlug)->where('is_active', true)->first();
        if (! $plan) {
            $plan = DB::table('plans')->where('slug', 'starter')->first();
        }
        if (! $plan) {
            return;
        }

        $planId = (int) $plan->id;
        $planSlug = (string) $plan->slug;
        $planAmount = (float) $plan->price_monthly;

        // Use travel_name from form if provided, otherwise fallback to user's name
        $travelName = trim((string) request()->input('travel_name', ''));
        if ($travelName === '') {
            $travelName = $userName;
        }
        $phone = trim((string) request()->input('phone', ''));
        $origin = trim((string) request()->input('origin', ''));
        $destination = trim((string) request()->input('destination', ''));
        // Fallback: parse old 'route' field if origin/destination not provided
        if ($origin === '' && $destination === '') {
            $routeText = trim((string) request()->input('route', ''));
            if ($routeText !== '') {
                $parts = array_map('trim', explode('-', $routeText, 2));
                $origin = $parts[0] ?? '';
                $destination = $parts[1] ?? '';
            }
        }

        $tenantSlug = $this->generateTenantSlug($travelName);
        $userEmail = DB::table('users')->where('id', $userId)->value('email') ?? '';

        // Trial logic:
        // - Starter plan → 14 days trial (unless email already used trial)
        // - Pro/Fleet plan → no trial, immediate payment required
        // Only intent=trial can receive trial days; pricing plan selection is payment.
        $trialDays = 0;
        if ($registrationIntent === 'trial' && $planSlug === 'starter') {
            $trialDays = (int) config('saas.trial_days', 14);
            $alreadyHadTrial = $this->emailHasUsedTrial($userEmail);
            if ($alreadyHadTrial) {
                $trialDays = 0; // No trial — requires immediate subscription
            }
        }

        DB::transaction(function () use ($userId, $travelName, $phone, $origin, $destination, $tenantSlug, $planId, $trialDays, $planAmount): void {
            // 1. Create tenant
            $tenantId = (int) DB::table('tenants')->insertGetId([
                'name' => $travelName,
                'slug' => $tenantSlug,
                'email' => DB::table('users')->where('id', $userId)->value('email') ?? '',
                'phone' => $phone !== '' ? $phone : null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create subscription
            //   - Starter: 14 day trial
            //   - Pro/Fleet: no trial, 1 day billing cycle so payment is prompted ASAP
            $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;
            $endsAt = $trialDays > 0
                ? $trialEndsAt
                : now()->addDay()->toDateString(); // 1 day for paid plans to prompt payment
            $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'status' => $trialDays > 0 ? 'trial' : 'active',
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => now()->toDateString(),
                'ends_at' => $endsAt,
                'billing_interval' => 'monthly',
                'grace_period_days' => config('saas.grace_period_days', 7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($trialDays <= 0 && $planAmount > 0) {
                PaymentGateway::createInvoice($tenantId, $subscriptionId, $planAmount, $endsAt);
            }

            // 3. Create default pool
            if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
                $poolName = strtoupper($travelName);
                if (! str_contains($poolName, 'POOL')) {
                    $poolName .= ' POOL';
                }
                $poolCode = $tenantSlug.'-pool';
                $existingPool = DB::table('pools')->where('code', $poolCode)->exists();
                if ($existingPool) {
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

                // Auto-create route from origin & destination fields
                if ($origin !== '' && $destination !== '' && Schema::hasTable('routes') && Schema::hasTable('pool_route')) {
                    $routeName = strtoupper($origin.' -> '.$destination);

                    // Check if route already exists
                    $existingRouteQuery = DB::table('routes')->where('name', $routeName);
                    if (Schema::hasColumn('routes', 'tenant_id')) {
                        $existingRouteQuery->where('tenant_id', $tenantId);
                    }
                    $existingRouteId = $existingRouteQuery->value('id');

                    if ($existingRouteId) {
                        $routeId = (int) $existingRouteId;
                    } else {
                        $routeId = (int) DB::table('routes')->insertGetId($this->routePayload($routeName, $origin, $destination, $tenantId));
                    }

                    // Map route to pool if not already mapped
                    if (! DB::table('pool_route')->where('pool_id', $poolId)->where('route_id', $routeId)->exists()) {
                        DB::table('pool_route')->insert([
                            'pool_id' => $poolId,
                            'route_id' => $routeId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 4. Assign user to pool
                if (Schema::hasTable('pool_user')) {
                    DB::table('pool_user')->insert([
                        'pool_id' => $poolId,
                        'user_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Assign tenant_id to user
            if (Schema::hasColumn('users', 'tenant_id')) {
                DB::table('users')->where('id', $userId)->update(['tenant_id' => $tenantId]);
            }

            // 6. Assign default "Admin Pool" role so user can access the app
            $this->assignDefaultRole($userId);
        });

        session()->forget(['registration_plan', 'registration_intent']);

        $redirectRoute = $registrationIntent === 'trial' && $trialDays > 0
            ? route('dashboard')
            : route('subscription.index');
        session()->put('url.intended', $redirectRoute);

        Log::info("Tenant auto-provisioned for user #{$userId}", [
            'tenant_slug' => $tenantSlug,
            'plan' => $planSlug,
            'registration_intent' => $registrationIntent,
        ]);
    }

    private function saasReady(): bool
    {
        return Schema::hasTable('tenants')
            && Schema::hasTable('plans')
            && Schema::hasTable('subscriptions');
    }

    private function assignDefaultRole(int $userId): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role')) {
            return;
        }

        // Safety net: ensure roles and permissions are seeded
        if ((int) DB::table('roles')->count() === 0) {
            \App\Support\AccessControl::syncDefaults();
        }

        // Find "Admin Pool" role (most appropriate for tenant owner)
        $roleId = DB::table('roles')->where('slug', 'admin-pool')->value('id');

        // Fallback: use any available non-super-admin role
        if (! $roleId) {
            $roleId = DB::table('roles')
                ->where('slug', '!=', 'super-admin')
                ->orderBy('id')
                ->value('id');
        }

        if (! $roleId) {
            Log::error("Cannot assign role to user #{$userId}: no roles exist in database. Run AccessControl::syncDefaults().");

            return;
        }

        // Check if user already has a role
        $existingRole = DB::table('user_role')->where('user_id', $userId)->exists();
        if ($existingRole) {
            return;
        }

        DB::table('user_role')->insert([
            'user_id' => $userId,
            'role_id' => (int) $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Assigned role '{$roleId}' to user #{$userId}");
    }

    /**
     * Check if this email has ever had a trial subscription before.
     */
    /**
     * Build route payload with only the columns that exist.
     */
    private function routePayload(string $name, string $origin, string $destination, int $tenantId): array
    {
        $payload = [
            'name' => $name,
            'origin' => $origin !== '' ? $origin : null,
            'destination' => $destination !== '' ? $destination : null,
        ];

        if (Schema::hasColumn('routes', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('routes', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('routes', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        return $payload;
    }

    private function emailHasUsedTrial(string $email): bool
    {
        if ($email === '' || ! Schema::hasTable('subscriptions') || ! Schema::hasTable('tenants')) {
            return false;
        }

        return DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('tenants.email', $email)
            ->where(function ($q) {
                $q->where('subscriptions.status', 'trial')
                  ->orWhereNotNull('subscriptions.trial_ends_at');
            })
            ->exists();
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
