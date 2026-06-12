<?php

namespace App\Services;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantProvisioningService
{
    /**
     * Provision a tenant from the platform SaaS admin, without an owner user.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function provisionStandaloneTenant(array $input): array
    {
        if (! $this->saasReady()) {
            throw new \RuntimeException('SaaS tables not ready.');
        }

        $name = trim((string) ($input['name'] ?? $input['company_name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Nama tenant wajib diisi.');
        }

        $slug = trim((string) ($input['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->generateTenantSlug($name);
        }

        $plan = isset($input['plan_id'])
            ? DB::table('plans')->where('id', (int) $input['plan_id'])->where('is_active', true)->first()
            : null;
        $plan ??= $this->activePlan((string) config('saas.default_plan', 'starter')) ?? $this->activePlan('starter');
        if (! $plan) {
            throw new \RuntimeException('Paket aktif tidak ditemukan.');
        }

        $trialDays = max(0, (int) ($input['trial_days'] ?? config('saas.trial_days', 14)));
        $billingInterval = $this->billingInterval($input);
        $amount = $billingInterval === 'yearly'
            ? (float) ($plan->price_yearly ?? 0)
            : (float) ($plan->price_monthly ?? 0);
        $requiresPayment = $trialDays <= 0 && $amount > 0;
        $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;

        return DB::transaction(function () use ($input, $name, $slug, $plan, $trialDays, $billingInterval, $amount, $requiresPayment, $trialEndsAt): array {
            $tenantId = (int) DB::table('tenants')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'email' => $this->nullableString($input['email'] ?? null),
                'phone' => $this->nullableString($input['phone'] ?? null),
                'address' => $this->nullableString($input['address'] ?? null),
                'domain' => $this->nullableString($input['domain'] ?? null),
                'status' => $requiresPayment ? 'pending_payment' : 'active',
                'target_revenue' => (float) ($input['target_revenue'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $endsAt = $trialDays > 0
                ? $trialEndsAt
                : ($requiresPayment ? null : ($billingInterval === 'yearly' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString()));
            $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
                'tenant_id' => $tenantId,
                'plan_id' => (int) $plan->id,
                'status' => $trialDays > 0 ? 'trial' : ($requiresPayment ? 'pending_payment' : 'active'),
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => $requiresPayment ? null : now()->toDateString(),
                'ends_at' => $endsAt,
                'billing_interval' => $billingInterval,
                'grace_period_days' => config('saas.grace_period_days', 7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $invoiceId = 0;
            if ($requiresPayment) {
                $invoiceId = PaymentGateway::createInvoice(
                    $tenantId,
                    $subscriptionId,
                    $amount,
                    now()->addDay()->toDateString(),
                );
            }

            $poolId = $this->createDefaultPool($tenantId, $slug, $name);

            return [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'invoice_id' => $invoiceId,
                'pool_id' => $poolId,
                'tenant_status' => $requiresPayment ? 'pending_payment' : 'active',
                'subscription_status' => $trialDays > 0 ? 'trial' : ($requiresPayment ? 'pending_payment' : 'active'),
            ];
        });
    }

    /**
     * Provision a SaaS tenant, subscription, default pool, optional route, and owner role.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function provisionForUser(User $user, array $input = []): array
    {
        $userId = (int) $user->id;
        $userName = trim((string) $user->name);
        if ($userId <= 0 || $userName === '' || ! $this->saasReady()) {
            return ['provisioned' => false, 'reason' => 'not_ready'];
        }

        if (Schema::hasColumn('users', 'tenant_id')) {
            $existingTenantId = (int) DB::table('users')->where('id', $userId)->value('tenant_id');
            if ($existingTenantId > 0) {
                return [
                    'provisioned' => false,
                    'reason' => 'already_has_tenant',
                    'tenant_id' => $existingTenantId,
                    'redirect_route' => route('subscription.index'),
                ];
            }
        }

        $intent = $this->registrationIntent($input);
        $billingInterval = $this->billingInterval($input);
        $requestedPlanSlug = trim((string) ($input['plan_slug'] ?? $input['plan'] ?? session('registration_plan') ?? config('saas.default_plan', 'starter')));
        $planSlug = $intent === 'trial' ? 'starter' : ($requestedPlanSlug !== '' ? $requestedPlanSlug : (string) config('saas.default_plan', 'starter'));
        $plan = $this->activePlan($planSlug) ?? $this->activePlan('starter');

        if (! $plan) {
            return ['provisioned' => false, 'reason' => 'plan_missing'];
        }

        $travelName = trim((string) ($input['travel_name'] ?? $input['company_name'] ?? ''));
        if ($travelName === '') {
            $travelName = $userName;
        }

        $phone = trim((string) ($input['phone'] ?? ''));
        $origin = trim((string) ($input['origin'] ?? ''));
        $destination = trim((string) ($input['destination'] ?? ''));
        if ($origin === '' && $destination === '') {
            [$origin, $destination] = $this->routeParts((string) ($input['route'] ?? ''));
        }

        $email = (string) (DB::table('users')->where('id', $userId)->value('email') ?? $user->email ?? '');
        $trialDays = 0;
        if ($intent === 'trial' && (string) $plan->slug === 'starter') {
            $trialDays = (int) config('saas.trial_days', 14);
            if ($this->emailHasUsedTrial($email)) {
                $trialDays = 0;
            }
        }

        $amount = $billingInterval === 'yearly'
            ? (float) ($plan->price_yearly ?? 0)
            : (float) ($plan->price_monthly ?? 0);
        $isTrial = $trialDays > 0;
        $requiresPayment = ! $isTrial && $amount > 0;
        $tenantSlug = $this->generateTenantSlug($travelName);

        return DB::transaction(function () use (
            $userId,
            $email,
            $travelName,
            $phone,
            $origin,
            $destination,
            $tenantSlug,
            $plan,
            $trialDays,
            $billingInterval,
            $amount,
            $isTrial,
            $requiresPayment,
        ): array {
            $tenantId = (int) DB::table('tenants')->insertGetId([
                'name' => $travelName,
                'slug' => $tenantSlug,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
                'status' => $requiresPayment ? 'pending_payment' : 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays)->toDateString() : null;
            $activeEndsAt = $isTrial
                ? $trialEndsAt
                : ($requiresPayment ? null : now()->addMonth()->toDateString());

            if (! $requiresPayment && $billingInterval === 'yearly') {
                $activeEndsAt = now()->addYear()->toDateString();
            }

            $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
                'tenant_id' => $tenantId,
                'plan_id' => (int) $plan->id,
                'status' => $isTrial ? 'trial' : ($requiresPayment ? 'pending_payment' : 'active'),
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => $requiresPayment ? null : now()->toDateString(),
                'ends_at' => $activeEndsAt,
                'billing_interval' => $billingInterval,
                'grace_period_days' => config('saas.grace_period_days', 7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $invoiceId = 0;
            if ($requiresPayment) {
                $invoiceId = PaymentGateway::createInvoice(
                    $tenantId,
                    $subscriptionId,
                    $amount,
                    now()->addDay()->toDateString(),
                );
            }

            $poolId = $this->createDefaultPool($tenantId, $tenantSlug, $travelName);
            if ($poolId > 0) {
                $this->createDefaultRoute($tenantId, $poolId, $origin, $destination);
                $this->assignUserToPool($userId, $poolId);
            }

            if (Schema::hasColumn('users', 'tenant_id')) {
                DB::table('users')->where('id', $userId)->update(['tenant_id' => $tenantId]);
            }

            $this->assignDefaultRole($userId);

            Log::info("Tenant provisioned for user #{$userId}", [
                'tenant_id' => $tenantId,
                'tenant_slug' => $tenantSlug,
                'plan' => (string) $plan->slug,
                'status' => $requiresPayment ? 'pending_payment' : ($isTrial ? 'trial' : 'active'),
            ]);

            return [
                'provisioned' => true,
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'invoice_id' => $invoiceId,
                'subscription_status' => $isTrial ? 'trial' : ($requiresPayment ? 'pending_payment' : 'active'),
                'tenant_status' => $requiresPayment ? 'pending_payment' : 'active',
                'redirect_route' => $requiresPayment ? route('subscription.index') : route('dashboard'),
            ];
        });
    }

    private function saasReady(): bool
    {
        return Schema::hasTable('tenants')
            && Schema::hasTable('plans')
            && Schema::hasTable('subscriptions');
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function registrationIntent(array $input): string
    {
        $intent = trim((string) ($input['registration_intent'] ?? $input['intent'] ?? session('registration_intent') ?? ''));
        if ($intent === 'payment') {
            $intent = 'paid';
        }

        if (in_array($intent, ['trial', 'paid'], true)) {
            return $intent;
        }

        $hasPlan = trim((string) ($input['plan_slug'] ?? $input['plan'] ?? session('registration_plan') ?? '')) !== '';

        return $hasPlan ? 'paid' : 'trial';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function billingInterval(array $input): string
    {
        $interval = trim((string) ($input['billing_interval'] ?? 'monthly'));

        return $interval === 'yearly' ? 'yearly' : 'monthly';
    }

    private function activePlan(string $slug): ?object
    {
        $slug = trim($slug);
        if ($slug === '' || ! Schema::hasTable('plans')) {
            return null;
        }

        return DB::table('plans')->where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function routeParts(string $routeText): array
    {
        $routeText = trim($routeText);
        if ($routeText === '') {
            return ['', ''];
        }

        $parts = array_map('trim', explode('-', $routeText, 2));

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function createDefaultPool(int $tenantId, string $tenantSlug, string $travelName): int
    {
        if (! Schema::hasTable('pools') || ! Schema::hasColumn('pools', 'tenant_id')) {
            return 0;
        }

        $poolName = strtoupper($travelName);
        if (! str_contains($poolName, 'POOL')) {
            $poolName .= ' POOL';
        }

        $poolCode = $tenantSlug.'-pool';
        if (DB::table('pools')->where('code', $poolCode)->exists()) {
            $poolCode = $tenantSlug.'-pool-'.now()->format('His');
        }

        return (int) DB::table('pools')->insertGetId([
            'name' => $poolName,
            'code' => $poolCode,
            'tenant_id' => $tenantId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDefaultRoute(int $tenantId, int $poolId, string $origin, string $destination): void
    {
        if ($origin === '' || $destination === '' || ! Schema::hasTable('routes') || ! Schema::hasTable('pool_route')) {
            return;
        }

        $routeName = strtoupper($origin.' -> '.$destination);
        $existingRouteQuery = DB::table('routes')->where('name', $routeName);
        if (Schema::hasColumn('routes', 'tenant_id')) {
            $existingRouteQuery->where('tenant_id', $tenantId);
        }

        $routeId = (int) ($existingRouteQuery->value('id') ?? 0);
        if ($routeId <= 0) {
            $routeId = (int) DB::table('routes')->insertGetId($this->routePayload($routeName, $origin, $destination, $tenantId));
        }

        if (! DB::table('pool_route')->where('pool_id', $poolId)->where('route_id', $routeId)->exists()) {
            DB::table('pool_route')->insert([
                'pool_id' => $poolId,
                'route_id' => $routeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
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

    private function assignUserToPool(int $userId, int $poolId): void
    {
        if (! Schema::hasTable('pool_user')) {
            return;
        }

        if (DB::table('pool_user')->where('pool_id', $poolId)->where('user_id', $userId)->exists()) {
            return;
        }

        DB::table('pool_user')->insert([
            'pool_id' => $poolId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assignDefaultRole(int $userId): void
    {
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
    }

    private function emailHasUsedTrial(string $email): bool
    {
        if ($email === '' || ! Schema::hasTable('subscriptions') || ! Schema::hasTable('tenants')) {
            return false;
        }

        return DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('tenants.email', $email)
            ->where(function ($q): void {
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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
