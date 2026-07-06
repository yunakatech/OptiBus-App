<?php

namespace App\Console\Commands;

use App\Services\PaymentGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateSubscriptionInvoices extends Command
{
    protected $signature = 'subscription:generate-invoices';
    protected $description = 'Generate monthly invoices for active subscriptions that are due for renewal';

    public function handle(): int
    {
        if (! Schema::hasTable('subscriptions') || ! Schema::hasTable('plans')) {
            $this->warn('SaaS tables not ready.');

            return 1;
        }

        $dueThreshold = now()->addDays(7)->toDateString();
        $generated = 0;

        // Find active subscriptions ending within 7 days that don't have a pending invoice
        $subs = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->whereIn('subscriptions.status', ['trial', 'active'])
            ->whereNotNull('subscriptions.ends_at')
            ->where('subscriptions.ends_at', '<=', $dueThreshold)
            ->select(
                'subscriptions.*',
                'plans.price_monthly as base_price_monthly',
                'plans.price_yearly as base_price_yearly',
                DB::raw('COALESCE(subscriptions.custom_price_monthly, plans.price_monthly) as price_monthly'),
                DB::raw('COALESCE(subscriptions.custom_price_yearly, plans.price_yearly) as price_yearly'),
            )
            ->get();

        foreach ($subs as $sub) {
            // Check if a pending invoice already exists for this period
            $exists = DB::table('invoice_subscriptions')
                ->where('subscription_id', $sub->id)
                ->whereIn('status', ['pending', 'verification', 'overdue'])
                ->exists();

            if ($exists) {
                continue;
            }

            $amount = $sub->billing_interval === 'yearly'
                ? (float) ($sub->price_yearly ?? $sub->base_price_yearly ?? 0)
                : (float) ($sub->price_monthly ?? $sub->base_price_monthly ?? 0);

            if ($amount <= 0) {
                continue;
            }

            $dueDate = $sub->ends_at ?? now()->addDays(7)->toDateString();

            $invoiceId = PaymentGateway::createInvoice(
                (int) $sub->tenant_id,
                (int) $sub->id,
                $amount,
                $dueDate,
            );

            if ($invoiceId > 0) {
                $generated++;
            }
        }

        if ($generated > 0) {
            $this->info("{$generated} invoices generated.");
        } else {
            $this->info('No invoices needed.');
        }

        return 0;
    }
}
