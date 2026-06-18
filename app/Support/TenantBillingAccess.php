<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantBillingAccess
{
    /**
     * Resolve billing access for a user.
     *
     * @return array<string, mixed>
     */
    public static function forUser(?int $userId = null): array
    {
        $userId ??= (int) (auth()->id() ?? 0);
        $redirectUrl = route('subscription.index', absolute: false);
        $base = [
            'allowed' => true,
            'locked' => false,
            'reason' => 'allowed',
            'tenant_id' => 0,
            'tenant_status' => '',
            'subscription_id' => null,
            'subscription_status' => '',
            'plan_id' => null,
            'plan_slug' => '',
            'plan_name' => '',
            'is_trial' => false,
            'trial_ends_at' => null,
            'ends_at' => null,
            'redirect_url' => $redirectUrl,
        ];

        if ($userId <= 0 || AccessControl::userIsSuperAdmin($userId)) {
            return $base;
        }

        if (! self::tablesReady()) {
            return $base;
        }

        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId <= 0) {
            return self::locked($base, 'no_tenant');
        }

        $tenant = DB::table('tenants')->where('id', $tenantId)->first(['id', 'status']);
        $tenantStatus = (string) ($tenant->status ?? '');

        $subscription = DB::table('subscriptions')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.tenant_id', $tenantId)
            ->orderByRaw("CASE subscriptions.status WHEN 'active' THEN 0 WHEN 'trial' THEN 1 WHEN 'pending_payment' THEN 2 WHEN 'past_due' THEN 3 WHEN 'suspended' THEN 4 WHEN 'expired' THEN 5 WHEN 'canceled' THEN 6 ELSE 7 END")
            ->orderByDesc('subscriptions.created_at')
            ->select(
                'subscriptions.id as subscription_id',
                'subscriptions.status as subscription_status',
                'subscriptions.plan_id',
                'subscriptions.trial_ends_at',
                'subscriptions.ends_at',
                'plans.slug as plan_slug',
                'plans.name as plan_name',
            )
            ->first();

        $access = [
            ...$base,
            'tenant_id' => $tenantId,
            'tenant_status' => $tenantStatus,
        ];

        if (! $subscription) {
            return self::locked($access, 'no_subscription');
        }

        $status = (string) ($subscription->subscription_status ?? '');
        $trialEndsAt = $subscription->trial_ends_at ?? null;
        $endsAt = $subscription->ends_at ?? null;

        $access = [
            ...$access,
            'subscription_id' => (int) $subscription->subscription_id,
            'subscription_status' => $status,
            'plan_id' => (int) ($subscription->plan_id ?? 0),
            'plan_slug' => (string) ($subscription->plan_slug ?? ''),
            'plan_name' => (string) ($subscription->plan_name ?? ''),
            'is_trial' => $status === 'trial',
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => $endsAt,
        ];

        if (! in_array($tenantStatus, ['', 'active'], true)) {
            return self::locked($access, 'tenant_'.$tenantStatus);
        }

        if ($status === 'trial') {
            $expiry = $trialEndsAt ?: $endsAt;

            return self::dateHasPassed($expiry)
                ? self::locked($access, 'trial_expired')
                : self::allowed($access);
        }

        if ($status === 'active') {
            return self::dateHasPassed($endsAt)
                ? self::locked($access, 'subscription_expired')
                : self::allowed($access);
        }

        return self::locked($access, $status !== '' ? $status : 'subscription_inactive');
    }

    private static function tablesReady(): bool
    {
        return Schema::hasTable('tenants')
            && Schema::hasTable('subscriptions')
            && Schema::hasTable('plans');
    }

    /**
     * @param  array<string, mixed>  $access
     * @return array<string, mixed>
     */
    private static function allowed(array $access): array
    {
        return [
            ...$access,
            'allowed' => true,
            'locked' => false,
            'reason' => 'allowed',
        ];
    }

    /**
     * @param  array<string, mixed>  $access
     * @return array<string, mixed>
     */
    private static function locked(array $access, string $reason): array
    {
        return [
            ...$access,
            'allowed' => false,
            'locked' => true,
            'reason' => $reason,
        ];
    }

    private static function dateHasPassed(mixed $value): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return false;
        }

        return CarbonImmutable::parse((string) $value)->endOfDay()->isPast();
    }
}
