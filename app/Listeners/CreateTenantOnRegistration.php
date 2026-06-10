<?php

namespace App\Listeners;

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

        // Get the selected plan from session or request input
        $planSlug = (string) (session('registration_plan') ?: request()->input('plan', config('saas.default_plan', 'starter')));
        $plan = DB::table('plans')->where('slug', $planSlug)->where('is_active', true)->first();
        if (! $plan) {
            $plan = DB::table('plans')->where('slug', 'starter')->first();
        }
        if (! $plan) {
            return;
        }

        $planId = (int) $plan->id;
        $tenantSlug = $this->generateTenantSlug($userName);
        $trialDays = (int) config('saas.trial_days', 14);

        DB::transaction(function () use ($userId, $userName, $tenantSlug, $planId, $trialDays): void {
            // 1. Create tenant
            $tenantId = (int) DB::table('tenants')->insertGetId([
                'name' => $userName,
                'slug' => $tenantSlug,
                'email' => DB::table('users')->where('id', $userId)->value('email') ?? '',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create subscription with trial
            $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;
            DB::table('subscriptions')->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'status' => $trialDays > 0 ? 'trial' : 'active',
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => now()->toDateString(),
                'ends_at' => $trialDays > 0 ? $trialEndsAt : now()->addMonth()->toDateString(),
                'billing_interval' => 'monthly',
                'grace_period_days' => config('saas.grace_period_days', 7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Create default pool
            if (Schema::hasTable('pools') && Schema::hasColumn('pools', 'tenant_id')) {
                $poolName = strtoupper($userName).' POOL';
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

        session()->forget('registration_plan');

        Log::info("Tenant auto-provisioned for user #{$userId}", [
            'tenant_slug' => $tenantSlug,
            'plan' => $planSlug,
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
