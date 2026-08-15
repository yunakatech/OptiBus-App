<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateway;
use App\Support\PoolScope;
use App\Support\TenantBillingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
        // Keep the payment state fresh even when the scheduler has not run yet.
        PaymentGateway::cancelExpiredInvoices();
        $tenantSub = PoolScope::tenantSubscription();

        return Inertia::render('Subscription', [
            'tenant_subscription' => $tenantSub,
            'invoices' => $this->loadInvoices($tenantSub),
            'current_plan' => Inertia::defer(
                fn () => $this->loadCurrentPlan($tenantSub),
                'billing',
            ),
            'plans' => Inertia::defer(
                fn () => $this->loadPlans(),
                'saas',
            ),
            'account_access' => fn () => Cache::remember(
                'inertia:subscription:account-access:user:'.(int) (auth()->id() ?? 0).':v1',
                now()->addMinutes(2),
                fn () => $this->accountAccess(),
            ),
            'billing_access' => fn () => TenantBillingAccess::forUser(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $tenantSub
     * @return array<int, array<string, mixed>>
     */
    private function loadInvoices(?array $tenantSub): array
    {
        if (! $tenantSub || ! Schema::hasTable('invoice_subscriptions')) {
            return [];
        }

        $hasDueDateColumn = Schema::hasColumn('invoice_subscriptions', 'due_date');
        $hasPaidAtColumn = Schema::hasColumn('invoice_subscriptions', 'paid_at');
        $hasGatewayColumns = Schema::hasColumn('invoice_subscriptions', 'gateway_checkout_url');
        $hasGatewayPayloadColumn = Schema::hasColumn('invoice_subscriptions', 'gateway_payload');

        $columns = [
            'id',
            'invoice_number',
            'amount',
            'status',
            'payment_method',
            'payment_gateway',
            'created_at',
        ];

        if ($hasDueDateColumn) {
            $columns[] = 'due_date';
        }
        if ($hasPaidAtColumn) {
            $columns[] = 'paid_at';
        }
        if ($hasGatewayColumns) {
            $columns = array_merge($columns, [
                'gateway_reference',
                'gateway_checkout_url',
                'gateway_status',
                'gateway_paid_at',
            ]);
        }
        if ($hasGatewayPayloadColumn) {
            $columns[] = 'gateway_payload';
        }

        return DB::table('invoice_subscriptions')
            ->where('tenant_id', (int) $tenantSub['tenant_id'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get($columns)
            ->map(function ($invoice) use ($hasDueDateColumn, $hasPaidAtColumn, $hasGatewayColumns, $hasGatewayPayloadColumn): array {
                $gatewayPayload = $hasGatewayPayloadColumn
                    ? json_decode((string) ($invoice->gateway_payload ?? ''), true)
                    : [];
                $gatewayPayload = is_array($gatewayPayload) ? $gatewayPayload : [];
                $gatewayErrorMessage = $gatewayPayload['message']
                    ?? ($gatewayPayload['response']['messages'] ?? '');
                if (is_array($gatewayErrorMessage)) {
                    $gatewayErrorMessage = implode(' ', array_map('strval', $gatewayErrorMessage));
                }

                return [
                    'id' => (int) $invoice->id,
                    'invoice_number' => (string) $invoice->invoice_number,
                    'amount' => (float) $invoice->amount,
                    'status' => (string) $invoice->status,
                    'due_date' => $hasDueDateColumn ? ($invoice->due_date ?? null) : null,
                    'paid_at' => $hasPaidAtColumn ? ($invoice->paid_at ?? null) : null,
                    'payment_method' => (string) ($invoice->payment_method ?? ''),
                    'payment_gateway' => (string) ($invoice->payment_gateway ?? 'Mayar'),
                    'gateway_reference' => $hasGatewayColumns ? (string) ($invoice->gateway_reference ?? '') : '',
                    'gateway_checkout_url' => $hasGatewayColumns ? (string) ($invoice->gateway_checkout_url ?? '') : '',
                    'gateway_status' => $hasGatewayColumns ? (string) ($invoice->gateway_status ?? '') : '',
                    'gateway_paid_at' => $hasGatewayColumns ? ($invoice->gateway_paid_at ?? null) : null,
                    'gateway_error_message' => Str::limit(trim((string) $gatewayErrorMessage), 240, '...'),
                    'created_at' => $invoice->created_at,
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $tenantSub
     * @return array<string, mixed>|null
     */
    private function loadCurrentPlan(?array $tenantSub): ?array
    {
        if (! $tenantSub || ! Schema::hasTable('plans')) {
            return null;
        }

        $currentPlan = null;
        if (Schema::hasTable('subscriptions') && ($tenantSub['subscription_id'] ?? 0) > 0) {
            $currentPlan = DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.id', (int) $tenantSub['subscription_id'])
                ->select(
                    'plans.id',
                    'plans.name',
                    'plans.slug',
                    'plans.description',
                    'plans.price_monthly as base_price_monthly',
                    'plans.price_yearly as base_price_yearly',
                    'subscriptions.custom_price_monthly',
                    'subscriptions.custom_price_yearly',
                    'subscriptions.custom_max_pools',
                    'subscriptions.custom_max_users',
                    'subscriptions.custom_max_armadas',
                    'subscriptions.custom_max_routes',
                    DB::raw('COALESCE(subscriptions.custom_price_monthly, plans.price_monthly) as price_monthly'),
                    DB::raw('COALESCE(subscriptions.custom_price_yearly, plans.price_yearly) as price_yearly'),
                )
                ->first();
        } elseif (isset($tenantSub['plan_id'])) {
            $currentPlan = DB::table('plans')
                ->where('id', (int) $tenantSub['plan_id'])
                ->select([
                    'id',
                    'name',
                    'slug',
                    'description',
                    'price_monthly as base_price_monthly',
                    'price_yearly as base_price_yearly',
                ])
                ->first();
        }

        if (! $currentPlan) {
            return null;
        }

        return [
            'id' => (int) $currentPlan->id,
            'name' => (string) $currentPlan->name,
            'slug' => (string) $currentPlan->slug,
            'price_monthly' => (float) ($currentPlan->price_monthly ?? $currentPlan->base_price_monthly ?? 0),
            'price_yearly' => (float) ($currentPlan->price_yearly ?? $currentPlan->base_price_yearly ?? 0),
            'base_price_monthly' => (float) ($currentPlan->base_price_monthly ?? $currentPlan->price_monthly ?? 0),
            'base_price_yearly' => (float) ($currentPlan->base_price_yearly ?? $currentPlan->price_yearly ?? 0),
            'custom_price_monthly' => $currentPlan->custom_price_monthly ?? null,
            'custom_price_yearly' => $currentPlan->custom_price_yearly ?? null,
            'custom_max_pools' => $currentPlan->custom_max_pools ?? null,
            'custom_max_users' => $currentPlan->custom_max_users ?? null,
            'custom_max_armadas' => $currentPlan->custom_max_armadas ?? null,
            'custom_max_routes' => $currentPlan->custom_max_routes ?? null,
            'description' => (string) ($currentPlan->description ?? ''),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadPlans(): array
    {
        if (! Schema::hasTable('plans')) {
            return [];
        }

        return Cache::remember('inertia:saas:active-plans:v1', now()->addMinutes(10), fn () => DB::table('plans')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'price_monthly', 'price_yearly', 'description'])
            ->map(fn ($plan): array => [
                'id' => (int) $plan->id,
                'name' => (string) $plan->name,
                'slug' => (string) $plan->slug,
                'price_monthly' => (float) $plan->price_monthly,
                'price_yearly' => (float) $plan->price_yearly,
                'description' => (string) ($plan->description ?? ''),
            ])
            ->values()
            ->all());
    }

    /**
     * Create a Mayar checkout invoice for the selected SaaS plan.
     * POST /subscription/checkout
     */
    public function checkout(Request $request): RedirectResponse|HttpResponse
    {
        PaymentGateway::cancelExpiredInvoices();
        $tenantId = PoolScope::tenantId();
        if ($tenantId <= 0 || ! Schema::hasTable('plans') || ! Schema::hasTable('subscriptions')) {
            return back()->with('status', 'billing_missing_tenant');
        }

        $tenantSub = PoolScope::tenantSubscription();
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

        $preserveCurrentAccess = in_array(
            (string) ($tenantSub['subscription_status'] ?? ''),
            ['active', 'trial'],
            true,
        );

        $invoiceId = DB::transaction(function () use (
            $tenantId,
            $plan,
            $billingInterval,
            $amount,
            $preserveCurrentAccess,
        ): int {
            if (! $preserveCurrentAccess) {
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
            }

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
                now()->addDays((int) config('saas.invoice_payment_days', 1))->toDateString(),
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
