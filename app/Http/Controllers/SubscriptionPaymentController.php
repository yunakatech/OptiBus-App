<?php

namespace App\Http\Controllers;

use App\Services\MayarGateway;
use App\Services\PaymentGateway;
use App\Support\PoolScope;
use App\Support\TenantBillingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SubscriptionPaymentController extends Controller
{
    /**
     * Show the tenant's subscription + payment page.
     * GET /subscription
     */
    public function index(): Response
    {
        $tenantSub = PoolScope::tenantSubscription();
        $invoices = [];
        $currentPlan = null;

        $this->syncPendingPaymentInvoice($tenantSub);

        if ($tenantSub && Schema::hasTable('invoice_subscriptions')) {
            $hasDueDateColumn = Schema::hasColumn('invoice_subscriptions', 'due_date');
            $hasPaidAtColumn = Schema::hasColumn('invoice_subscriptions', 'paid_at');
            $hasGatewayColumns = Schema::hasColumn('invoice_subscriptions', 'gateway_checkout_url');
            $invoices = DB::table('invoice_subscriptions')
                ->where('tenant_id', $tenantSub['tenant_id'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($inv) use ($hasDueDateColumn, $hasPaidAtColumn, $hasGatewayColumns) {
                    return [
                        'id' => (int) $inv->id,
                        'invoice_number' => (string) $inv->invoice_number,
                        'amount' => (float) $inv->amount,
                        'status' => (string) $inv->status,
                        'due_date' => $hasDueDateColumn ? ($inv->due_date ?? null) : null,
                        'paid_at' => $hasPaidAtColumn ? ($inv->paid_at ?? null) : null,
                        'payment_method' => (string) ($inv->payment_method ?? ''),
                        'payment_gateway' => (string) ($inv->payment_gateway ?? 'Mayar'),
                        'gateway_reference' => $hasGatewayColumns ? (string) ($inv->gateway_reference ?? '') : '',
                        'gateway_checkout_url' => $hasGatewayColumns ? (string) ($inv->gateway_checkout_url ?? '') : '',
                        'gateway_status' => $hasGatewayColumns ? (string) ($inv->gateway_status ?? '') : '',
                        'gateway_paid_at' => $hasGatewayColumns ? ($inv->gateway_paid_at ?? null) : null,
                        'created_at' => $inv->created_at,
                    ];
                })
                ->all();

            if (Schema::hasTable('plans')) {
                $currentPlan = DB::table('plans')->where('id', $tenantSub['plan_id'])->first();
            }
        }

        $plans = Schema::hasTable('plans')
            ? DB::table('plans')->where('is_active', true)->orderBy('sort_order')->get()
            : collect();

        return Inertia::render('Subscription', [
            'tenant_subscription' => $tenantSub,
            'invoices' => $invoices,
            'current_plan' => $currentPlan ? [
                'id' => (int) $currentPlan->id,
                'name' => (string) $currentPlan->name,
                'slug' => (string) $currentPlan->slug,
                'price_monthly' => (float) $currentPlan->price_monthly,
                'price_yearly' => (float) $currentPlan->price_yearly,
                'description' => (string) ($currentPlan->description ?? ''),
            ] : null,
            'plans' => $plans->map(fn ($p) => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'slug' => (string) $p->slug,
                'price_monthly' => (float) $p->price_monthly,
                'price_yearly' => (float) $p->price_yearly,
                'description' => (string) ($p->description ?? ''),
            ])->all(),
            'account_access' => $this->accountAccess(),
            'billing_access' => TenantBillingAccess::forUser(),
        ]);
    }

    /**
     * Create a Mayar checkout invoice for the selected SaaS plan.
     * POST /subscription/checkout
     */
    public function checkout(Request $request): RedirectResponse|HttpResponse
    {
        $tenantId = PoolScope::tenantId();
        if ($tenantId <= 0 || ! Schema::hasTable('plans') || ! Schema::hasTable('subscriptions')) {
            return back()->with('status', 'billing_missing_tenant');
        }

        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'in:starter,pro,fleet'],
            'billing_interval' => ['nullable', 'string', 'in:monthly,yearly'],
        ]);

        $billingInterval = ($data['billing_interval'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly';
        $plan = DB::table('plans')
            ->where('slug', $data['plan_slug'])
            ->where('is_active', true)
            ->first();
        if (! $plan) {
            return back()->with('status', 'billing_plan_missing');
        }

        $amount = $billingInterval === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;
        if ($amount <= 0) {
            return back()->with('status', 'billing_plan_free');
        }

        $invoiceId = DB::transaction(function () use ($tenantId, $plan, $billingInterval, $amount): int {
            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'pending_payment',
                'updated_at' => now(),
            ]);

            DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['trial', 'active', 'past_due'])
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

            $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
                'tenant_id' => $tenantId,
                'plan_id' => (int) $plan->id,
                'status' => 'pending_payment',
                'trial_ends_at' => null,
                'starts_at' => null,
                'ends_at' => null,
                'billing_interval' => $billingInterval,
                'grace_period_days' => config('saas.grace_period_days', 7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return PaymentGateway::createInvoice(
                $tenantId,
                $subscriptionId,
                $amount,
                now()->addDay()->toDateString(),
            );
        });

        if ($invoiceId <= 0) {
            return back()->with('status', 'billing_invoice_failed');
        }

        $checkoutUrl = Schema::hasColumn('invoice_subscriptions', 'gateway_checkout_url')
            ? (string) (DB::table('invoice_subscriptions')->where('id', $invoiceId)->value('gateway_checkout_url') ?? '')
            : '';

        if ($checkoutUrl !== '' && $request->header('X-Inertia')) {
            return Inertia::location($checkoutUrl);
        }

        if ($checkoutUrl !== '') {
            return redirect()->away($checkoutUrl);
        }

        return redirect()
            ->route('subscription.index')
            ->with('status', 'payment_link_error');
    }

    /**
     * @param  array<string, mixed>|null  $tenantSub
     */
    private function syncPendingPaymentInvoice(?array $tenantSub): void
    {
        if (! $tenantSub || ($tenantSub['subscription_status'] ?? '') !== 'pending_payment') {
            return;
        }

        if (! Schema::hasTable('invoice_subscriptions') || ! Schema::hasTable('subscriptions') || ! Schema::hasTable('plans')) {
            return;
        }

        $subscriptionId = (int) ($tenantSub['subscription_id'] ?? 0);
        if ($subscriptionId <= 0) {
            return;
        }

        $activeInvoice = DB::table('invoice_subscriptions')
            ->where('subscription_id', $subscriptionId)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderByDesc('created_at')
            ->first();
        if ($activeInvoice) {
            if (
                Schema::hasColumn('invoice_subscriptions', 'gateway_checkout_url')
                && trim((string) ($activeInvoice->gateway_checkout_url ?? '')) === ''
            ) {
                app(MayarGateway::class)->createCheckoutForInvoice((int) $activeInvoice->id);
            }

            return;
        }

        $subscription = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.id', $subscriptionId)
            ->select('subscriptions.*', 'plans.price_monthly', 'plans.price_yearly')
            ->first();
        if (! $subscription) {
            return;
        }

        $amount = ($subscription->billing_interval ?? 'monthly') === 'yearly'
            ? (float) $subscription->price_yearly
            : (float) $subscription->price_monthly;
        if ($amount <= 0) {
            return;
        }

        PaymentGateway::createInvoice(
            (int) $subscription->tenant_id,
            (int) $subscription->id,
            $amount,
            now()->addDay()->toDateString(),
        );
    }

    /**
     * @return array{tenant_id: int, pool_count: int, role_names: array<int, string>}
     */
    private function accountAccess(): array
    {
        $userId = (int) (auth()->id() ?? 0);
        $roleNames = [];
        if ($userId > 0 && Schema::hasTable('user_role') && Schema::hasTable('roles')) {
            $roleNames = DB::table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('user_role.user_id', $userId)
                ->orderBy('roles.name')
                ->pluck('roles.name')
                ->map(static fn ($value) => (string) $value)
                ->values()
                ->all();
        }

        return [
            'tenant_id' => $userId > 0 ? PoolScope::tenantId($userId) : 0,
            'pool_count' => $userId > 0 ? count(PoolScope::userPoolIds($userId)) : 0,
            'role_names' => $roleNames,
        ];
    }
}
