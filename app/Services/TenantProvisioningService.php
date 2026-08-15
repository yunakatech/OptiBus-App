<?php

namespace App\Services;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantProvisioningService
{
    public function __construct(private readonly LuggagePricingService $luggagePricing) {}

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
                    now()->addDays((int) config('saas.invoice_payment_days', 1))->toDateString(),
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
            $input,
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
                    now()->addDays((int) config('saas.invoice_payment_days', 1))->toDateString(),
                );
            }

            $poolId = $this->createDefaultPool($tenantId, $tenantSlug, $travelName);
            $routeId = 0;
            if ($poolId > 0) {
                $routeId = $this->createDefaultRoute($tenantId, $poolId, $origin, $destination);
                $routeName = $this->routeNameForId($routeId) ?: strtoupper($origin.' -> '.$destination);
                $segmentId = $this->createOnboardingSegment($tenantId, $routeId, $routeName, [
                    ...$input,
                    'origin' => $origin,
                    'destination' => $destination,
                ]);
                $unitId = $this->createOnboardingUnit($tenantId, $poolId, $input);
                $armadaId = $this->createOnboardingArmada($tenantId, $poolId, $input);
                $this->createOnboardingDriver($tenantId, $poolId, $armadaId, $input);
                $this->createOnboardingSchedules($tenantId, $routeId, $routeName, $unitId, $segmentId, $input);
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
                'pool_id' => $poolId,
                'route_id' => $routeId,
                'setup_progress' => $this->setupProgressForTenant($tenantId),
                'redirect_route' => $requiresPayment ? route('subscription.index') : route('dashboard'),
            ];
        });
    }

    /**
     * Add or repair first-run operational data for a user that already owns a tenant.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function completeSetupForUser(User $user, array $input = []): array
    {
        $userId = (int) $user->id;
        $tenantId = $this->tenantIdForUser($userId);
        if ($userId <= 0 || $tenantId <= 0) {
            return ['provisioned' => false, 'reason' => 'tenant_missing'];
        }

        return DB::transaction(function () use ($user, $input, $tenantId): array {
            $travelName = trim((string) ($input['travel_name'] ?? ''));
            $phone = trim((string) ($input['phone'] ?? ''));
            if ($travelName !== '' && Schema::hasTable('tenants')) {
                $tenantPayload = ['name' => $travelName, 'updated_at' => now()];
                if ($phone !== '' && Schema::hasColumn('tenants', 'phone')) {
                    $tenantPayload['phone'] = $phone;
                }
                DB::table('tenants')->where('id', $tenantId)->update($tenantPayload);
            }

            $poolId = $this->defaultPoolForTenant($tenantId);
            if ($poolId <= 0) {
                $tenantSlug = (string) (DB::table('tenants')->where('id', $tenantId)->value('slug') ?? 'travel-'.$tenantId);
                $tenantName = trim((string) (DB::table('tenants')->where('id', $tenantId)->value('name') ?? ''));
                if ($tenantName === '') {
                    $tenantName = $travelName !== '' ? $travelName : (string) $user->name;
                }
                $poolId = $this->createDefaultPool($tenantId, $tenantSlug, $tenantName);
            }

            if ($poolId > 0) {
                $this->assignUserToPool((int) $user->id, $poolId);
            }

            $origin = trim((string) ($input['origin'] ?? ''));
            $destination = trim((string) ($input['destination'] ?? ''));
            $routeId = $this->createDefaultRoute($tenantId, $poolId, $origin, $destination);
            $routeName = $this->routeNameForId($routeId) ?: strtoupper($origin.' -> '.$destination);

            $segmentId = $this->createOnboardingSegment($tenantId, $routeId, $routeName, $input);
            $unitId = $this->createOnboardingUnit($tenantId, $poolId, $input);
            $armadaId = $this->createOnboardingArmada($tenantId, $poolId, $input);
            $this->createOnboardingDriver($tenantId, $poolId, $armadaId, $input);
            $this->createOnboardingSchedules($tenantId, $routeId, $routeName, $unitId, $segmentId, $input);

            return [
                'provisioned' => true,
                'tenant_id' => $tenantId,
                'pool_id' => $poolId,
                'route_id' => $routeId,
                'redirect_route' => route('dashboard'),
                'setup_progress' => $this->setupProgressForTenant($tenantId),
            ];
        });
    }

    /**
     * @return array{route: bool, segment: bool, schedule: bool, unit: bool, armada: bool, driver: bool, completed: bool, completed_count: int, total_count: int, percent: int, items: array<int, array{key: string, label: string, done: bool}>}
     */
    public function setupProgressForTenant(int $tenantId): array
    {
        $items = [
            ['key' => 'route', 'label' => 'Rute sudah dibuat', 'done' => $this->tenantRowExists('routes', $tenantId)],
            ['key' => 'schedule', 'label' => 'Jadwal sudah dibuat', 'done' => $this->tenantRowExists('schedules', $tenantId)],
            ['key' => 'segment', 'label' => 'Harga sudah dibuat', 'done' => $this->tenantRowExists('segments', $tenantId)],
            ['key' => 'unit', 'label' => 'Kategori armada sudah dibuat', 'done' => $this->tenantRowExists('category_armada', $tenantId)],
            ['key' => 'armada', 'label' => 'Armada sudah dibuat', 'done' => $this->tenantRowExists('armadas', $tenantId)],
            ['key' => 'driver', 'label' => 'Driver sudah dibuat', 'done' => $this->tenantRowExists('drivers', $tenantId)],
        ];

        $completedCount = collect($items)->filter(fn (array $item): bool => (bool) $item['done'])->count();
        $totalCount = count($items);

        return [
            'route' => (bool) $items[0]['done'],
            'schedule' => (bool) $items[1]['done'],
            'segment' => (bool) $items[2]['done'],
            'unit' => (bool) $items[3]['done'],
            'armada' => (bool) $items[4]['done'],
            'driver' => (bool) $items[5]['done'],
            'completed' => $completedCount === $totalCount,
            'completed_count' => $completedCount,
            'total_count' => $totalCount,
            'percent' => $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function onboardingDefaultsForUser(User $user): array
    {
        $tenantId = $this->tenantIdForUser((int) $user->id);
        $tenant = $tenantId > 0 && Schema::hasTable('tenants')
            ? DB::table('tenants')->where('id', $tenantId)->first(['name', 'phone'])
            : null;
        $route = $tenantId > 0 && Schema::hasTable('routes') && Schema::hasColumn('routes', 'tenant_id')
            ? DB::table('routes')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->first(['origin', 'destination'])
            : null;

        return [
            'travel_name' => (string) ($tenant->name ?? ''),
            'phone' => (string) ($tenant->phone ?? ''),
            'origin' => (string) ($route->origin ?? ''),
            'destination' => (string) ($route->destination ?? ''),
        ];
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

    private function createDefaultRoute(int $tenantId, int $poolId, string $origin, string $destination): int
    {
        if ($origin === '' || $destination === '' || ! Schema::hasTable('routes') || ! Schema::hasTable('pool_route')) {
            return 0;
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

        return $routeId;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createOnboardingSegment(int $tenantId, int $routeId, string $routeName, array $input): int
    {
        if ($tenantId <= 0 || $routeId <= 0 || ! Schema::hasTable('segments')) {
            return 0;
        }

        $pickupTimes = $this->timeList($input['pickup_times'] ?? []);
        $price = (float) ($input['ticket_price'] ?? 0);
        if ($pickupTimes === [] && $price <= 0) {
            return 0;
        }

        $origin = trim((string) ($input['segment_origin'] ?? ''));
        $destination = trim((string) ($input['segment_destination'] ?? ''));
        if ($origin === '') {
            $origin = trim((string) ($input['origin'] ?? ''));
        }
        if ($destination === '') {
            $destination = trim((string) ($input['destination'] ?? ''));
        }

        $segmentName = strtoupper(trim($origin.' -> '.$destination));
        if ($segmentName === '->') {
            $segmentName = $routeName;
        }

        $existingQuery = DB::table('segments')
            ->where('route_id', $routeId)
            ->where('rute', $segmentName);
        if (Schema::hasColumn('segments', 'tenant_id')) {
            $existingQuery->where('tenant_id', $tenantId);
        }
        $existing = $existingQuery->value('id');
        if ($existing) {
            if ($this->luggagePricing->ready()) {
                $this->luggagePricing->syncSegmentRates($tenantId, (int) $existing);
            }

            return (int) $existing;
        }

        $payload = [
            'route_id' => $routeId,
            'rute' => $segmentName,
            'origin' => $origin !== '' ? $origin : null,
            'destination' => $destination !== '' ? $destination : null,
            'jam' => ($pickupTimes[0] ?? '08:00').':00',
            'harga' => $price,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('segments', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('segments', 'jam_pickups')) {
            $payload['jam_pickups'] = json_encode($pickupTimes !== [] ? $pickupTimes : ['08:00']);
        }

        $segmentId = (int) DB::table('segments')->insertGetId($this->filterPayloadForTable('segments', $payload));
        if ($this->luggagePricing->ready()) {
            $this->luggagePricing->syncSegmentRates($tenantId, $segmentId);
        }

        return $segmentId;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createOnboardingUnit(int $tenantId, int $poolId, array $input): int
    {
        if ($tenantId <= 0 || ! Schema::hasTable('category_armada')) {
            return 0;
        }

        $category = $this->normalizeUnitCategory($input['unit_category'] ?? 'Minibus');
        $capacity = max(0, (int) ($input['seat_capacity'] ?? 0));
        $namaKategori = trim((string) ($input['unit_template_name'] ?? ''));
        if ($namaKategori === '' && (trim((string) ($input['unit_category'] ?? '')) !== '' || $capacity > 0)) {
            $namaKategori = trim($category.' '.($capacity > 0 ? $capacity.' Seat' : ''));
        }
        if ($namaKategori === '') {
            $namaKategori = trim((string) ($input['unit_nopol'] ?? ''));
        }

        $namaKategori = strtoupper($namaKategori);
        if ($namaKategori === '' && trim((string) ($input['unit_category'] ?? '')) === '' && $capacity <= 0) {
            return 0;
        }
        if ($namaKategori === '') {
            $namaKategori = 'SETUP-'.$tenantId;
        }

        $existing = DB::table('category_armada')
            ->when(Schema::hasColumn('category_armada', 'tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereRaw('UPPER(nama_kategori) = ?', [$namaKategori])
            ->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $namaKategori = $this->uniqueOnboardingUnitTemplateName($tenantId, $namaKategori);

        $payload = [
            'nama_kategori' => $namaKategori,
            'category' => $category,
            'kapasitas' => $capacity,
            'status' => 'Aktif',
            'created_at' => now(),
        ];
        if (Schema::hasColumn('category_armada', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('category_armada', 'pool_id')) {
            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        try {
            return (int) DB::table('category_armada')->insertGetId($this->filterPayloadForTable('category_armada', $payload));
        } catch (QueryException $e) {
            Log::warning('onboarding.unit_skipped', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createOnboardingArmada(int $tenantId, int $poolId, array $input): int
    {
        if ($tenantId <= 0 || ! Schema::hasTable('armadas')) {
            return 0;
        }

        $armadaNopol = strtoupper(trim((string) ($input['unit_nopol'] ?? '')));
        if ($armadaNopol === '') {
            return 0;
        }

        $existing = DB::table('armadas')
            ->when(Schema::hasColumn('armadas', 'tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereRaw('UPPER(nopol) = ?', [$armadaNopol])
            ->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $payload = [
            'nopol' => $armadaNopol,
            'merk' => $this->nullableString($input['armada_merk'] ?? null),
            'tahun' => 0,
            'warna' => null,
            'kategori' => $this->normalizeUnitCategory($input['unit_category'] ?? 'Minibus'),
            'ac_type' => 'AC',
            'target_bulanan' => 0,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('armadas', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('armadas', 'pool_id')) {
            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        try {
            return (int) DB::table('armadas')->insertGetId($this->filterPayloadForTable('armadas', $payload));
        } catch (QueryException $e) {
            Log::warning('onboarding.armada_skipped', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createOnboardingDriver(int $tenantId, int $poolId, int $armadaId, array $input): int
    {
        if ($tenantId <= 0 || ! Schema::hasTable('drivers')) {
            return 0;
        }

        $name = strtoupper(trim((string) ($input['driver_name'] ?? '')));
        if ($name === '') {
            return 0;
        }

        $existing = DB::table('drivers')
            ->when(Schema::hasColumn('drivers', 'tenant_id'), fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('nama', $name)
            ->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $payload = [
            'nama' => $name,
            'phone' => $this->nullableString($input['driver_phone'] ?? null),
            'created_at' => now(),
        ];
        if (Schema::hasColumn('drivers', 'kategori')) {
            $payload['kategori'] = $this->normalizeUnitCategory($input['unit_category'] ?? 'Minibus');
        }
        if (Schema::hasColumn('drivers', 'armada_id') && $armadaId > 0) {
            $payload['armada_id'] = $armadaId;
        }
        if (Schema::hasColumn('drivers', 'armada_nopol')) {
            $payload['armada_nopol'] = strtoupper(trim((string) ($input['unit_nopol'] ?? ''))) ?: null;
        }
        if (Schema::hasColumn('drivers', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('drivers', 'pool_id')) {
            $payload['pool_id'] = $poolId > 0 ? $poolId : null;
        }

        return (int) DB::table('drivers')->insertGetId($this->filterPayloadForTable('drivers', $payload));
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createOnboardingSchedules(int $tenantId, int $routeId, string $routeName, int $unitId, int $segmentId, array $input): void
    {
        if ($tenantId <= 0 || $routeId <= 0 || $routeName === '' || ! Schema::hasTable('schedules')) {
            return;
        }

        $days = array_values(array_unique(array_filter(array_map('intval', (array) ($input['schedule_days'] ?? [])), static fn (int $day): bool => $day >= 0 && $day <= 6)));
        $departureTime = $this->normalizeTime($input['departure_time'] ?? null);
        if ($days === [] || $departureTime === '') {
            return;
        }

        foreach ($days as $day) {
            try {
                DB::transaction(function () use (
                    $tenantId,
                    $routeId,
                    $routeName,
                    $unitId,
                    $segmentId,
                    $input,
                    $day,
                    $departureTime,
                ): void {
                    $scheduleId = $this->findOrCreateOnboardingSchedule(
                        $tenantId,
                        $routeId,
                        $routeName,
                        $unitId,
                        $day,
                        $departureTime,
                    );

                    if ($scheduleId <= 0) {
                        return;
                    }

                    $this->upsertScheduleUnitRow($tenantId, $scheduleId, $unitId);
                    $this->upsertScheduleSegmentRow($tenantId, $scheduleId, $segmentId, $input);
                });
            } catch (QueryException $e) {
                Log::error('onboarding.schedule_failed', [
                    'tenant_id' => $tenantId,
                    'route' => $routeName,
                    'day' => $day,
                    'jam' => $departureTime,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }
    }

    private function findOrCreateOnboardingSchedule(
        int $tenantId,
        int $routeId,
        string $routeName,
        int $unitId,
        int $day,
        string $departureTime,
    ): int {
        $query = DB::table('schedules')
            ->where('rute', $routeName)
            ->where('dow', $day);

        if (Schema::hasColumn('schedules', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $existing = $query->get(['id', 'jam'])->first(
            fn (object $row): bool => $this->normalizeTime($row->jam ?? null) === $departureTime,
        );
        $existingId = (int) ($existing->id ?? 0);
        if ($existingId > 0) {
            return $existingId;
        }

        $payload = [
            'rute' => $routeName,
            'dow' => $day,
            'jam' => $departureTime,
            'units' => 1,
            'unit_label' => 'Unit 1',
            'unit_id' => $unitId > 0 ? $unitId : null,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('schedules', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('schedules', 'route_id')) {
            $payload['route_id'] = $routeId;
        }
        if (Schema::hasColumn('schedules', 'seats')) {
            $payload['seats'] = $unitId > 0 ? $this->unitSeatCount($unitId) : 0;
        }

        return (int) DB::table('schedules')->insertGetId($this->filterPayloadForTable('schedules', $payload));
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

    private function tenantIdForUser(int $userId): int
    {
        if ($userId <= 0 || ! Schema::hasTable('users') || ! Schema::hasColumn('users', 'tenant_id')) {
            return 0;
        }

        return (int) (DB::table('users')->where('id', $userId)->value('tenant_id') ?? 0);
    }

    private function defaultPoolForTenant(int $tenantId): int
    {
        if ($tenantId <= 0 || ! Schema::hasTable('pools') || ! Schema::hasColumn('pools', 'tenant_id')) {
            return 0;
        }

        return (int) (DB::table('pools')
            ->where('tenant_id', $tenantId)
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    private function routeNameForId(int $routeId): string
    {
        if ($routeId <= 0 || ! Schema::hasTable('routes')) {
            return '';
        }

        return trim((string) (DB::table('routes')->where('id', $routeId)->value('name') ?? ''));
    }

    private function tenantRowExists(string $table, int $tenantId): bool
    {
        if ($tenantId <= 0 || ! Schema::hasTable($table)) {
            return false;
        }

        $hasTenantColumn = Schema::hasColumn($table, 'tenant_id');
        $hasPoolColumn = Schema::hasColumn($table, 'pool_id');
        if (! $hasTenantColumn && ! $hasPoolColumn) {
            return false;
        }

        $poolIds = $hasPoolColumn ? $this->tenantPoolIds($tenantId) : [];
        if ($hasPoolColumn && $poolIds === [] && ! $hasTenantColumn) {
            return false;
        }

        $query = DB::table($table)->where(function ($builder) use ($hasTenantColumn, $tenantId, $hasPoolColumn, $poolIds): void {
            if ($hasTenantColumn) {
                $builder->where('tenant_id', $tenantId);
            }

            if ($hasPoolColumn && $poolIds !== []) {
                if ($hasTenantColumn) {
                    $builder->orWhereIn('pool_id', $poolIds);
                } else {
                    $builder->whereIn('pool_id', $poolIds);
                }
            }
        });

        return $query->exists();
    }

    /**
     * @return array<int, int>
     */
    private function tenantPoolIds(int $tenantId): array
    {
        if ($tenantId <= 0 || ! Schema::hasTable('pools') || ! Schema::hasColumn('pools', 'tenant_id')) {
            return [];
        }

        return DB::table('pools')
            ->where('tenant_id', $tenantId)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterPayloadForTable(string $table, array $payload): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return collect($payload)
            ->filter(static fn (mixed $value, string $column): bool => Schema::hasColumn($table, $column))
            ->all();
    }

    private function normalizeUnitCategory(mixed $value): string
    {
        $normalized = strtolower(preg_replace('/\s+/', '', trim((string) $value)) ?? '');

        return match ($normalized) {
            'mediumbus' => 'Mediumbus',
            'bigbus', 'bigbun' => 'Bigbus',
            'microbus' => 'Microbus',
            default => 'Minibus',
        };
    }

    private function uniqueOnboardingUnitTemplateName(int $tenantId, string $name): string
    {
        $base = trim($name);
        if ($base === '') {
            $base = 'SETUP-'.$tenantId;
        }

        $base = strtoupper(substr($base, 0, 50));
        if (! Schema::hasTable('category_armada') || ! Schema::hasColumn('category_armada', 'nama_kategori')) {
            return $base;
        }

        if (! DB::table('category_armada')->whereRaw('UPPER(nama_kategori) = ?', [$base])->exists()) {
            return $base;
        }

        $suffix = '-T'.$tenantId;
        $candidateBase = substr($base, 0, max(1, 50 - strlen($suffix)));
        $candidate = $candidateBase.$suffix;
        $counter = 2;

        while (DB::table('category_armada')->whereRaw('UPPER(nama_kategori) = ?', [$candidate])->exists()) {
            $suffix = '-T'.$tenantId.'-'.$counter;
            $candidate = substr($base, 0, max(1, 50 - strlen($suffix))).$suffix;
            $counter++;
        }

        return $candidate;
    }

    private function normalizeTime(mixed $value): string
    {
        $time = trim((string) $value);
        if (preg_match('/^\d{2}:\d{2}/', $time) !== 1) {
            return '';
        }

        return substr($time, 0, 5);
    }

    /**
     * @return array<int, string>
     */
    private function timeList(mixed $value): array
    {
        $items = is_array($value) ? $value : [$value];

        return collect($items)
            ->map(fn (mixed $item): string => $this->normalizeTime($item))
            ->filter(static fn (string $item): bool => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function unitSeatCount(int $unitId): int
    {
        if ($unitId <= 0 || ! Schema::hasTable('category_armada')) {
            return 0;
        }

        return max(0, (int) (DB::table('category_armada')->where('id', $unitId)->value('kapasitas') ?? 0));
    }

    private function upsertScheduleUnitRow(int $tenantId, int $scheduleId, int $unitId): void
    {
        if ($scheduleId <= 0 || ! Schema::hasTable('schedule_units')) {
            return;
        }

        $payload = [
            'schedule_id' => $scheduleId,
            'unit_no' => 1,
            'label' => 'Unit 1',
            'unit_id' => $unitId > 0 ? $unitId : null,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('schedule_units', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }

        $keys = ['schedule_id' => $scheduleId, 'unit_no' => 1];
        if (Schema::hasColumn('schedule_units', 'tenant_id')) {
            $keys['tenant_id'] = $tenantId;
        }

        DB::table('schedule_units')->updateOrInsert(
            $keys,
            $this->filterPayloadForTable('schedule_units', $payload),
        );
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function upsertScheduleSegmentRow(int $tenantId, int $scheduleId, int $segmentId, array $input): void
    {
        if ($scheduleId <= 0 || $segmentId <= 0 || ! Schema::hasTable('schedule_segment')) {
            return;
        }

        $pickupTimes = $this->timeList($input['pickup_times'] ?? []);
        $payload = $this->filterPayloadForTable('schedule_segment', [
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
            'jam_pickup' => $pickupTimes[0] ?? ($this->normalizeTime($input['departure_time'] ?? null) ?: '08:00'),
            'created_at' => now(),
        ]);

        $keys = [
            'schedule_id' => $scheduleId,
            'segment_id' => $segmentId,
        ];
        if (Schema::hasColumn('schedule_segment', 'tenant_id')) {
            $keys['tenant_id'] = $tenantId;
            $payload['tenant_id'] = $tenantId;
        }

        DB::table('schedule_segment')->updateOrInsert($keys, $payload);
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
