<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeatureGate
{
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
        static $cache = [];
        $userId ??= (int) (auth()->id() ?? 0);

        // Super admins bypass feature gates
        if ($userId > 0 && AccessControl::userIsSuperAdmin($userId)) {
            return null; // null = bypass (unlimited everything)
        }

        $tenantId = self::currentTenantId($userId);
        if ($tenantId <= 0 || ! self::ready()) {
            return null;
        }

        $cacheKey = "t{$tenantId}";
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $sub = DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->orderByRaw("FIELD(status, 'active', 'trial', 'past_due')")
            ->select('plan_id', 'status')
            ->first();

        if (! $sub) {
            $cache[$cacheKey] = (object) ['plan_id' => 0, 'plan_slug' => '', 'status' => 'inactive'];

            return $cache[$cacheKey];
        }

        $planSlug = (string) (DB::table('plans')->where('id', (int) $sub->plan_id)->value('slug') ?? '');

        $cache[$cacheKey] = (object) [
            'plan_id' => (int) $sub->plan_id,
            'plan_slug' => $planSlug,
            'status' => (string) $sub->status,
        ];

        return $cache[$cacheKey];
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

        // Get the feature gate ID
        $featureGateId = DB::table('feature_gates')
            ->where('feature_key', $featureKey)
            ->value('id');

        if (! $featureGateId) {
            // Feature gate not registered → assume core feature (allowed)
            return true;
        }

        // Check if the plan includes this feature
        $mapping = DB::table('plan_feature')
            ->where('plan_id', $plan->plan_id)
            ->where('feature_gate_id', $featureGateId)
            ->first();

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

        // Get max_value from plan_feature
        $maxValue = DB::table('plan_feature')
            ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
            ->where('plan_feature.plan_id', $plan->plan_id)
            ->where('feature_gates.feature_key', $resourceKey)
            ->value('plan_feature.max_value');

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
            $currentCount = (int) DB::table($tableName)
                ->where($tenantColumn, $tenantId)
                ->count();

            return ($currentCount + $increment) <= (int) $maxValue;
        }

        // Fallback: count all (no tenant column)
        $currentCount = (int) DB::table($tableName)->count();

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

        $mappings = DB::table('plan_feature')
            ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
            ->where('plan_feature.plan_id', $plan->plan_id)
            ->whereNotNull('plan_feature.max_value')
            ->where('plan_feature.max_value', '>', 0)
            ->select('feature_gates.feature_key', 'feature_gates.feature_name', 'plan_feature.max_value')
            ->get();

        $limits = [];
        $countMap = [
            'master.routes' => ['table' => 'routes', 'column' => 'tenant_id'],
            'master.armadas' => ['table' => 'armadas', 'column' => 'tenant_id'],
            'master.drivers' => ['table' => 'drivers', 'column' => 'tenant_id'],
            'tenant.multiple_pools' => ['table' => 'pools', 'column' => 'tenant_id'],
            'user.management' => ['table' => 'users', 'column' => 'tenant_id'],
        ];

        foreach ($mappings as $mapping) {
            $info = $countMap[$mapping->feature_key] ?? null;
            $current = 0;

            if ($info && Schema::hasTable($info['table']) && Schema::hasColumn($info['table'], $info['column'])) {
                $current = (int) DB::table($info['table'])
                    ->where($info['column'], $tenantId)
                    ->count();
            }

            $limits[$mapping->feature_key] = [
                'max' => (int) $mapping->max_value,
                'current' => $current,
                'feature_name' => (string) $mapping->feature_name,
            ];
        }

        return $limits;
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

        $maxValue = DB::table('plan_feature')
            ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
            ->where('plan_feature.plan_id', $plan->plan_id)
            ->where('feature_gates.feature_key', $resourceKey)
            ->value('plan_feature.max_value');

        if ($maxValue === null) {
            return null; // unlimited — no message needed
        }

        $planName = DB::table('plans')->where('id', $plan->plan_id)->value('name') ?? 'saat ini';

        return "Batas maksimal tercapai untuk paket {$planName}. Silakan upgrade paket untuk menambah kapasitas.";
    }
}
