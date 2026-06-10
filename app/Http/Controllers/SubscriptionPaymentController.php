<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateway;
use App\Support\PoolScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPaymentController extends Controller
{
    /**
     * Show the tenant's subscription + invoice/payment page.
     * GET /subscription
     */
    public function index(): Response
    {
        $tenantSub = PoolScope::tenantSubscription();
        $invoices = [];
        $currentPlan = null;

        if ($tenantSub && Schema::hasTable('invoice_subscriptions')) {
            $invoices = DB::table('invoice_subscriptions')
                ->where('tenant_id', $tenantSub['tenant_id'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($inv) {
                    return [
                        'id' => (int) $inv->id,
                        'invoice_number' => (string) $inv->invoice_number,
                        'amount' => (float) $inv->amount,
                        'status' => (string) $inv->status,
                        'due_date' => $inv->due_date,
                        'paid_at' => $inv->paid_at,
                        'payment_method' => (string) ($inv->payment_method ?? ''),
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
        ]);
    }

    /**
     * Create a Snap token for Midtrans payment.
     * POST /api/subscription/pay/{invoiceId}
     */
    public function pay(int $invoiceId): \Illuminate\Http\JsonResponse
    {
        $result = PaymentGateway::createSnapToken($invoiceId);

        if (! $result) {
            return response()->json(['success' => false, 'error' => 'Gagal membuat token pembayaran.'], 500);
        }

        return response()->json(['success' => true, ...$result]);
    }

    /**
     * Payment finish/return page.
     * GET /subscription/payment/finish
     */
    public function finish(): Response
    {
        return Inertia::render('Subscription', [
            'payment_result' => 'success',
        ]);
    }

    /**
     * Payment error page.
     * GET /subscription/payment/error
     */
    public function error(): Response
    {
        return Inertia::render('Subscription', [
            'payment_result' => 'error',
        ]);
    }
}
