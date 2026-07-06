<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeatureGate
{
    /** @var array<string, object|null> */
    private static array $currentPlanCache = [];

    /** @var array<string, int> */
    private static array $featureGateIdCache = [];

    /** @var array<string, object|null> */
    private static array $planFeatureCache = [];

    /** @var array<string, mixed> */
    private static array $featureMaxValueCache = [];

    /** @var array<string, string> */
    private static array $featureNameCache = [];

    /** @var array<string, int> */
    private static array $resourceCountCache = [];

    /** @var array<string, string> */
    private static array $planNameCache = [];

    /** @var array<string, array<string, array{max: int|null, current: int, feature_name: string}>> */
    private static array $usageLimitsCache = [];

    /**
     * Check if the feature gating system is ready.
     */
    public static function ready(): bool
    {
        return Schema::hasTable('feature_gates')
            && Schema::hasTable('plan_feature')
            && Schema::hasTable('subscriptions')
            && Schema::hasTable('plans');
    }

    public static function flushRequestCache(): void
    {
        self::$currentPlanCache = [];
        self::$featureGateIdCache = [];
        self::$planFeatureCache = [];
        self::$featureMaxValueCache = [];
        self::$featureNameCache = [];
        self::$resourceCountCache = [];
        self::$planNameCache = [];
        self::$usageLimitsCache = [];
    }

    /**
     * Check if SaaS feature gating is enabled.
     * Controlled via config('saas.feature_gating_enabled') — defaults to false.
     */
    public static function enabled(): bool
    {
        return (bool) (config('saas.feature_gating_enabled', false));
    }

    /**
     * Get the current user's tenant_id (delegates to PoolScope).
     */
    public static function currentTenantId(?int $userId = null): int
    {
        return PoolScope::tenantId($userId);
    }

    /**
     * Get the current tenant's active plan.
     * Returns null if no active subscription exists.
     *
     * @return object{plan_id: int, plan_slug: string, status: string}|null
     */
    public static function currentPlan(?int $userId = null): ?object
    {
        $userId ??= (int) (auth()->id() ?? 0);

        // Super admins bypass feature gates
        if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
            return null; // null = bypass (unlimited everything)
        }

        $tenantId = self::currentTenantId($userId);
        if ($tenantId <= 0 || ! self::ready()) {
            return null;
        }

        $cacheKey = self::requestCacheKey("plan:t{$tenantId}");
        if (array_key_exists($cacheKey, self::$currentPlanCache)) {
            return self::$currentPlanCache[$cacheKey];
        }

        $sub = DB::table('subscriptions')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.tenant_id', $tenantId)
            ->whereIn('subscriptions.status', ['trial', 'active', 'past_due'])
            ->orderByRaw("CASE subscriptions.status WHEN 'active' THEN 0 WHEN 'trial' THEN 1 WHEN 'past_due' THEN 2 ELSE 3 END")
            ->select(
                'subscriptions.id as subscription_id',
                'subscriptions.plan_id',
                'subscriptions.status',
                'subscriptions.custom_price_monthly',
                'subscriptions.custom_price_yearly',
                'subscriptions.custom_max_pools',
                'subscriptions.custom_max_users',
                'subscriptions.custom_max_armadas',
                'subscriptions.custom_max_routes',
                'plans.slug as plan_slug',
                'plans.price_monthly as base_price_monthly',
                'plans.price_yearly as base_price_yearly',
                'plans.max_pools as base_max_pools',
                'plans.max_users as base_max_users',
                'plans.max_armadas as base_max_armadas',
                'plans.max_routes as base_max_routes',
            )
            ->first();

        if (! $sub) {
            self::$currentPlanCache[$cacheKey] = (object) [
                'subscription_id' => 0,
                'plan_id' => 0,
                'plan_slug' => '',
                'status' => 'inactive',
                'custom_price_monthly' => null,
                'custom_price_yearly' => null,
                'custom_max_pools' => null,
                'custom_max_users' => null,
                'custom_max_armadas' => null,
                'custom_max_routes' => null,
                'base_price_monthly' => null,
                'base_price_yearly' => null,
                'base_max_pools' => null,
                'base_max_users' => null,
                'base_max_armadas' => null,
                'base_max_routes' => null,
                'price_monthly' => 0.0,
                'price_yearly' => 0.0,
                'max_pools' => null,
                'max_users' => null,
                'max_armadas' => null,
                'max_routes' => null,
            ];

            return self::$currentPlanCache[$cacheKey];
        }

        self::$currentPlanCache[$cacheKey] = (object) [
            'subscription_id' => (int) ($sub->subscription_id ?? 0),
            'plan_id' => (int) $sub->plan_id,
            'plan_slug' => (string) ($sub->plan_slug ?? ''),
            'status' => (string) $sub->status,
            'custom_price_monthly' => self::nullableFloat($sub->custom_price_monthly ?? null),
            'custom_price_yearly' => self::nullableFloat($sub->custom_price_yearly ?? null),
            'custom_max_pools' => self::nullableInt($sub->custom_max_pools ?? null),
            'custom_max_users' => self::nullableInt($sub->custom_max_users ?? null),
            'custom_max_armadas' => self::nullableInt($sub->custom_max_armadas ?? null),
            'custom_max_routes' => self::nullableInt($sub->custom_max_routes ?? null),
            'base_price_monthly' => self::nullableFloat($sub->base_price_monthly ?? null),
            'base_price_yearly' => self::nullableFloat($sub->base_price_yearly ?? null),
            'base_max_pools' => self::nullableInt($sub->base_max_pools ?? null),
            'base_max_users' => self::nullableInt($sub->base_max_users ?? null),
            'base_max_armadas' => self::nullableInt($sub->base_max_armadas ?? null),
            'base_max_routes' => self::nullableInt($sub->base_max_routes ?? null),
            'price_monthly' => self::effectivePrice($sub->custom_price_monthly ?? null, $sub->base_price_monthly ?? null),
            'price_yearly' => self::effectivePrice($sub->custom_price_yearly ?? null, $sub->base_price_yearly ?? null),
            'max_pools' => self::effectiveLimit($sub->custom_max_pools ?? null, $sub->base_max_pools ?? null),
            'max_users' => self::effectiveLimit($sub->custom_max_users ?? null, $sub->base_max_users ?? null),
            'max_armadas' => self::effectiveLimit($sub->custom_max_armadas ?? null, $sub->base_max_armadas ?? null),
            'max_routes' => self::effectiveLimit($sub->custom_max_routes ?? null, $sub->base_max_routes ?? null),
        ];

        return self::$currentPlanCache[$cacheKey];
    }

    /**
     * Check if the current tenant's plan includes a feature.
     *
     * @param string $featureKey  e.g. 'booking.seat_map', 'report.export_csv'
     * @param int    $increment   When checking before creating, pass 1 to account for the new item
     */
    public static function can(string $featureKey, int $increment = 0, ?int $userId = null): bool
    {
        // Feature gating not enabled → everything allowed
        if (! self::enabled()) {
            return true;
        }

        $userId ??= (int) (auth()->id() ?? 0);

        // Super admins always bypass
        if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
            return true;
        }

        if (! self::ready()) {
            return true; // Tables not ready yet → allow (graceful fallback)
        }

        $plan = self::currentPlan($userId);

        // No plan = no subscription = blocked (when gating is enabled)
        if (! $plan || $plan->plan_id <= 0) {
            return false;
        }

        // Suspended tenants: denied for all features
        if ($plan->status === 'suspended') {
            return false;
        }

        $featureGateId = self::featureGateId($featureKey);

        if (! $featureGateId) {
            // Feature gate not registered → assume core feature (allowed)
            return true;
        }

        $mapping = self::planFeatureMapping((int) $plan->plan_id, $featureGateId);

        if (! $mapping) {
            return false;
        }

        // max_value = NULL → unlimited
        // max_value = 0    → disabled
        // max_value = N    → limited to N
        $maxValue = $mapping->max_value;
        if ($maxValue === null) {
            return true; // unlimited
        }
        if ((int) $maxValue <= 0) {
            return false; // explicitly disabled
        }

        // Numeric limit — must check with canCreate() instead
        // For simple boolean checks, just being > 0 is enough
        return true;
    }

    /**
     * Check if the current tenant can create one more of a resource.
     *
     * @param string $resourceKey  Feature key that maps to a numeric limit (e.g. 'master.routes')
     * @param string $tableName    Table to count from
     * @param string $tenantColumn Column on the table that holds tenant_id
     * @param int    $increment    How many more to check for (usually 1)
     */
    public static function canCreate(
        string $resourceKey,
        string $tableName,
        string $tenantColumn = 'tenant_id',
        int $increment = 1,
        ?int $userId = null,
    ): bool {
        // Feature gating not enabled → everything allowed
        if (! self::enabled()) {
            return true;
        }

        $userId ??= (int) (auth()->id() ?? 0);

        // Super admins always bypass
        if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
            return true;
        }

        if (! self::ready() || ! Schema::hasTable($tableName)) {
            return true;
        }

        $plan = self::currentPlan($userId);
        if (! $plan || $plan->plan_id <= 0) {
            return false;
        }

        if ($plan->status === 'suspended') {
            return false;
        }

        if (self::subscriptionOverrideUnlimited($plan, $resourceKey)) {
            return true;
        }

        $maxValue = self::maxValueForFeature((int) $plan->plan_id, $resourceKey, $plan);

        // null = unlimited
        if ($maxValue === null) {
            return true;
        }

        if ((int) $maxValue <= 0) {
            return false; // explicitly disabled
        }

        // Count existing resources for this tenant
        $tenantId = self::currentTenantId($userId);
        if ($tenantId <= 0) {
            return true;
        }

        if (Schema::hasColumn($tableName, $tenantColumn)) {
            $currentCount = self::resourceCount($tableName, $tenantColumn, $tenantId);

            return ($currentCount + $increment) <= (int) $maxValue;
        }

        // Fallback: count all (no tenant column)
        $currentCount = self::resourceCount($tableName, '', 0);

        return ($currentCount + $increment) <= (int) $maxValue;
    }

    /**
     * Check and throw 403 if feature is not available.
     */
    public static function require(string $featureKey, ?int $userId = null): void
    {
        if (! self::can($featureKey, 0, $userId)) {
            abort(403, 'Fitur ini tidak tersedia pada paket langganan Anda. Silakan upgrade paket.');
        }
    }

    /**
     * Get all numeric limit info for the current plan.
     * Useful for the frontend to show usage progress.
     *
     * @return array<string, array{max: int|null, current: int, feature_name: string}>
     */
    public static function usageLimits(?int $userId = null): array
    {
        if (! self::ready()) {
            return [];
        }

        $plan = self::currentPlan($userId);
        if (! $plan || $plan->plan_id <= 0) {
            return [];
        }

        $tenantId = self::currentTenantId($userId);
        if ($tenantId <= 0) {
            return [];
        }

        $cacheKey = self::requestCacheKey(
            'usage:'.$tenantId.':'.$plan->plan_id.':'.(int) ($plan->subscription_id ?? 0).':'.md5(json_encode([
                'custom_price_monthly' => $plan->custom_price_monthly ?? null,
                'custom_price_yearly' => $plan->custom_price_yearly ?? null,
                'custom_max_pools' => $plan->custom_max_pools ?? null,
                'custom_max_users' => $plan->custom_max_users ?? null,
                'custom_max_armadas' => $plan->custom_max_armadas ?? null,
                'custom_max_routes' => $plan->custom_max_routes ?? null,
            ]) ?: ''),
        );
        if (array_key_exists($cacheKey, self::$usageLimitsCache)) {
            return self::$usageLimitsCache[$cacheKey];
        }

        $limits = [];
        $countMap = [
            'master.routes' => ['table' => 'routes', 'column' => 'tenant_id'],
            'master.armadas' => ['table' => 'armadas', 'column' => 'tenant_id'],
            'master.drivers' => ['table' => 'drivers', 'column' => 'tenant_id'],
            'tenant.multiple_pools' => ['table' => 'pools', 'column' => 'tenant_id'],
            'user.management' => ['table' => 'users', 'column' => 'tenant_id'],
        ];

        foreach ($countMap as $featureKey => $info) {
            $current = 0;

            if (Schema::hasTable($info['table']) && Schema::hasColumn($info['table'], $info['column'])) {
                $current = self::resourceCount($info['table'], $info['column'], $tenantId);
            }

            $limits[$featureKey] = [
                'max' => self::subscriptionOverrideUnlimited($plan, $featureKey)
                    ? null
                    : self::maxValueForFeature((int) $plan->plan_id, $featureKey, $plan),
                'current' => $current,
                'feature_name' => self::featureNameForKey($featureKey),
            ];
        }

        return self::$usageLimitsCache[$cacheKey] = $limits;
    }

    /**
     * Get a human-readable message when a resource limit is reached.
     */
    public static function limitMessage(string $resourceKey, ?int $userId = null): ?string
    {
        if (! self::ready()) {
            return null;
        }

        $plan = self::currentPlan($userId);
        if (! $plan || $plan->plan_id <= 0) {
            return 'Tidak ada langganan aktif.';
        }

        if (self::subscriptionOverrideUnlimited($plan, $resourceKey)) {
            return null;
        }

        $maxValue = self::maxValueForFeature((int) $plan->plan_id, $resourceKey, $plan);

        if ($maxValue === null) {
            return null; // unlimited — no message needed
        }

        $planName = self::planName((int) $plan->plan_id);

        return "Batas maksimal tercapai untuk paket {$planName}. Silakan upgrade paket untuk menambah kapasitas.";
    }

    private static function featureGateId(string $featureKey): int
    {
        $featureKey = trim($featureKey);
        $cacheKey = self::requestCacheKey('feature-gate:'.$featureKey);
        if (array_key_exists($cacheKey, self::$featureGateIdCache)) {
            return self::$featureGateIdCache[$cacheKey];
        }

        return self::$featureGateIdCache[$cacheKey] = (int) (DB::table('feature_gates')
            ->where('feature_key', $featureKey)
            ->value('id') ?? 0);
    }

    private static function planFeatureMapping(int $planId, int $featureGateId): ?object
    {
        $cacheKey = self::requestCacheKey("plan-feature:{$planId}:{$featureGateId}");
        if (array_key_exists($cacheKey, self::$planFeatureCache)) {
            return self::$planFeatureCache[$cacheKey];
        }

        return self::$planFeatureCache[$cacheKey] = DB::table('plan_feature')
            ->where('plan_id', $planId)
            ->where('feature_gate_id', $featureGateId)
            ->first();
    }

    private static function maxValueForFeature(int $planId, string $featureKey, ?object $planContext = null): mixed
    {
        $featureKey = trim($featureKey);
        $overrideSignature = $planContext ? md5(json_encode([
            'subscription_id' => (int) ($planContext->subscription_id ?? 0),
            'custom_max_pools' => $planContext->custom_max_pools ?? null,
            'custom_max_users' => $planContext->custom_max_users ?? null,
            'custom_max_armadas' => $planContext->custom_max_armadas ?? null,
            'custom_max_routes' => $planContext->custom_max_routes ?? null,
        ]) ?: '') : '0';
        $cacheKey = self::requestCacheKey("feature-max:{$planId}:{$featureKey}:{$overrideSignature}");
        if (array_key_exists($cacheKey, self::$featureMaxValueCache)) {
            return self::$featureMaxValueCache[$cacheKey];
        }

        $overrideValue = self::subscriptionOverrideLimit($planContext, $featureKey);
        if ($overrideValue !== null) {
            return self::$featureMaxValueCache[$cacheKey] = $overrideValue;
        }

        $mappedValue = DB::table('plan_feature')
            ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
            ->where('plan_feature.plan_id', $planId)
            ->where('feature_gates.feature_key', $featureKey)
            ->value('plan_feature.max_value');

        if ($mappedValue !== null) {
            return self::$featureMaxValueCache[$cacheKey] = $mappedValue;
        }

        return self::$featureMaxValueCache[$cacheKey] = self::planColumnLimit($planId, $featureKey);
    }

    private static function subscriptionOverrideLimit(?object $planContext, string $featureKey): ?int
    {
        if (! $planContext) {
            return null;
        }

        $column = [
            'master.routes' => 'custom_max_routes',
            'master.armadas' => 'custom_max_armadas',
            'tenant.multiple_pools' => 'custom_max_pools',
            'user.management' => 'custom_max_users',
        ][$featureKey] ?? null;

        if ($column === null || ! property_exists($planContext, $column)) {
            return null;
        }

        return self::nullableInt($planContext->{$column});
    }

    private static function subscriptionOverrideUnlimited(?object $planContext, string $featureKey): bool
    {
        return self::subscriptionOverrideLimit($planContext, $featureKey) === 0;
    }

    private static function featureNameForKey(string $featureKey): string
    {
        $featureKey = trim($featureKey);
        $cacheKey = self::requestCacheKey('feature-name:'.$featureKey);
        if (array_key_exists($cacheKey, self::$featureNameCache)) {
            return self::$featureNameCache[$cacheKey];
        }

        $name = DB::table('feature_gates')->where('feature_key', $featureKey)->value('feature_name');
        if (is_string($name) && trim($name) !== '') {
            return self::$featureNameCache[$cacheKey] = $name;
        }

        return self::$featureNameCache[$cacheKey] = match ($featureKey) {
            'master.routes' => 'Rute',
            'master.armadas' => 'Armada',
            'master.drivers' => 'Driver',
            'tenant.multiple_pools' => 'Pool',
            'user.management' => 'User',
            default => $featureKey,
        };
    }

    private static function effectivePrice(mixed $overrideValue, mixed $baseValue): float
    {
        if ($overrideValue !== null && $overrideValue !== '') {
            return (float) $overrideValue;
        }

        return (float) ($baseValue ?? 0);
    }

    private static function effectiveLimit(mixed $overrideValue, mixed $baseValue): ?int
    {
        $override = self::nullableInt($overrideValue);
        if ($override !== null) {
            return $override > 0 ? $override : null;
        }

        return self::limitFromNullableValue($baseValue);
    }

    private static function limitFromNullableValue(mixed $value): ?int
    {
        $intValue = self::nullableInt($value);
        if ($intValue === null) {
            return null;
        }

        return $intValue > 0 ? $intValue : null;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private static function planColumnLimit(int $planId, string $featureKey): mixed
    {
        $column = [
            'master.routes' => 'max_routes',
            'master.armadas' => 'max_armadas',
            'master.drivers' => 'max_drivers',
            'tenant.multiple_pools' => 'max_pools',
            'user.management' => 'max_users',
        ][$featureKey] ?? null;

        if ($column === null || ! Schema::hasColumn('plans', $column)) {
            return null;
        }

        $value = DB::table('plans')->where('id', $planId)->value($column);
        if ($value === null || (int) $value <= 0) {
            return null;
        }

        return (int) $value;
    }

    private static function resourceCount(string $tableName, string $tenantColumn, int $tenantId): int
    {
        $cacheKey = self::requestCacheKey("resource-count:{$tableName}:{$tenantColumn}:{$tenantId}");
        if (array_key_exists($cacheKey, self::$resourceCountCache)) {
            return self::$resourceCountCache[$cacheKey];
        }

        $query = DB::table($tableName);
        if ($tenantColumn !== '' && $tenantId > 0) {
            $query->where($tenantColumn, $tenantId);
        }

        return self::$resourceCountCache[$cacheKey] = (int) $query->count();
    }

    private static function planName(int $planId): string
    {
        $cacheKey = self::requestCacheKey('plan-name:'.$planId);
        if (array_key_exists($cacheKey, self::$planNameCache)) {
            return self::$planNameCache[$cacheKey];
        }

        return self::$planNameCache[$cacheKey] = (string) (DB::table('plans')->where('id', $planId)->value('name') ?? 'saat ini');
    }

    private static function requestCacheKey(string $key): string
    {
        $requestId = 'cli';

        try {
            if (app()->bound('request')) {
                $requestId = (string) spl_object_id(app('request'));
            }
        } catch (\Throwable) {
            $requestId = 'cli';
        }

        return $requestId.':'.$key;
    }
}
