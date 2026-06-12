<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscription:check-expired';
    protected $description = 'Check and update subscription and tenant billing statuses';

    public function handle(): int
    {
        if (! $this->saasReady()) {
            $this->warn('SaaS tables not ready. Run migrations first.');

            return 1;
        }

        $today = now()->toDateString();
        $todayDate = CarbonImmutable::parse($today);
        $updated = 0;

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

        $suspended = 0;
        $pastDueRows = DB::table('subscriptions')
            ->where('status', 'past_due')
            ->whereNotNull('ends_at')
            ->get(['id', 'ends_at', 'grace_period_days']);

        foreach ($pastDueRows as $sub) {
            $graceDays = (int) ($sub->grace_period_days ?? 7);
            if (CarbonImmutable::parse((string) $sub->ends_at)->addDays($graceDays)->lt($todayDate)) {
                $suspended += DB::table('subscriptions')->where('id', $sub->id)->update([
                    'status' => 'suspended',
                    'updated_at' => now(),
                ]);
            }
        }
        $updated += $suspended;
        if ($suspended > 0) {
            $this->info("{$suspended} subscriptions suspended.");
        }

        $canceled = 0;
        $suspendedRows = DB::table('subscriptions')
            ->where('status', 'suspended')
            ->get(['id', 'updated_at']);

        foreach ($suspendedRows as $sub) {
            if (CarbonImmutable::parse((string) $sub->updated_at)->addDays(30)->lt($todayDate)) {
                $canceled += DB::table('subscriptions')->where('id', $sub->id)->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $updated += $canceled;
        if ($canceled > 0) {
            $this->info("{$canceled} subscriptions canceled.");
        }

        if (Schema::hasTable('invoice_subscriptions') && Schema::hasColumn('invoice_subscriptions', 'due_date')) {
            $overdueInvoices = DB::table('invoice_subscriptions')
                ->where('status', 'pending')
                ->whereDate('due_date', '<', $today)
                ->update([
                    'status' => 'overdue',
                    'updated_at' => now(),
                ]);
            $updated += $overdueInvoices;
            if ($overdueInvoices > 0) {
                $this->info("{$overdueInvoices} invoices marked overdue.");
            }
        }

        $this->syncTenantStatuses();

        if ($updated === 0) {
            $this->info('No subscription status changes needed.');
        } else {
            $this->info("Total: {$updated} billing records updated.");
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

        $pendingTenants = DB::table('subscriptions')
            ->join('tenants', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->where('subscriptions.status', 'pending_payment')
            ->where('tenants.status', '!=', 'pending_payment')
            ->pluck('tenants.id');

        foreach ($pendingTenants as $tenantId) {
            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'pending_payment',
                'updated_at' => now(),
            ]);
        }

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
