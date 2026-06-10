<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscription:check-expired';
    protected $description = 'Check and update subscription statuses: trial→expired, active→past_due, past_due→suspended, suspended→canceled';

    public function handle(): int
    {
        if (! $this->saasReady()) {
            $this->warn('SaaS tables not ready. Run migrations first.');

            return 1;
        }

        $today = now()->toDateString();
        $updated = 0;

        // 1. Trial → Expired (trial ended)
        $trialExpired = DB::table('subscriptions')
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $today)
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);
        $updated += $trialExpired;
        if ($trialExpired > 0) {
            $this->info("{$trialExpired} trial subscriptions expired.");
        }

        // 2. Active → Past Due (billing period ended)
        $pastDue = DB::table('subscriptions')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $today)
            ->update([
                'status' => 'past_due',
                'updated_at' => now(),
            ]);
        $updated += $pastDue;
        if ($pastDue > 0) {
            $this->info("{$pastDue} subscriptions moved to past_due.");
        }

        // 3. Past Due → Suspended (grace period expired)
        $suspended = DB::table('subscriptions')
            ->where('status', 'past_due')
            ->whereNotNull('ends_at')
            ->whereRaw("DATE(ends_at, '+' || COALESCE(grace_period_days, 7) || ' days') < ?", [$today])
            ->update([
                'status' => 'suspended',
                'updated_at' => now(),
            ]);
        $updated += $suspended;
        if ($suspended > 0) {
            $this->info("{$suspended} subscriptions suspended (grace period expired).");
        }

        // 4. Suspended → Canceled (30 days after suspension)
        $canceled = DB::table('subscriptions')
            ->where('status', 'suspended')
            ->whereRaw("DATE(updated_at, '+30 days') < ?", [$today])
            ->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'updated_at' => now(),
            ]);
        $updated += $canceled;
        if ($canceled > 0) {
            $this->info("{$canceled} subscriptions canceled (30 days elapsed).");
        }

        // 5. Sync tenant status
        $this->syncTenantStatuses();

        if ($updated === 0) {
            $this->info('No subscription status changes needed.');
        } else {
            $this->info("Total: {$updated} subscriptions updated.");
        }

        return 0;
    }

    private function saasReady(): bool
    {
        return Schema::hasTable('subscriptions')
            && Schema::hasTable('tenants')
            && Schema::hasTable('plans');
    }

    private function syncTenantStatuses(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        // Suspended/canceled subscriptions → sync tenant status
        $tenantsToSuspend = DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('subscriptions.status', 'suspended')
            ->where('tenants.status', '!=', 'suspended')
            ->pluck('tenants.id');

        foreach ($tenantsToSuspend as $tenantId) {
            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'suspended',
                'updated_at' => now(),
            ]);
        }

        $tenantsToCancel = DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('subscriptions.status', 'canceled')
            ->where('tenants.status', '!=', 'canceled')
            ->pluck('tenants.id');

        foreach ($tenantsToCancel as $tenantId) {
            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'canceled',
                'updated_at' => now(),
            ]);
        }
    }
}
