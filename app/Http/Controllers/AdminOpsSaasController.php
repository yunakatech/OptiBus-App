<?php

namespace App\Http\Controllers;

use App\Support\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsSaasController extends Controller
{
    /**
     * Render the SaaS management page with optional tab routing.
     *
     * Tabs: tenants, subscriptions, plans, billing, payment
     */
    public function __invoke(Request $request): Response
    {
        $tab = trim((string) $request->route('tab', 'tenants'));
        if (! in_array($tab, ['tenants', 'subscriptions', 'plans', 'billing', 'payment'], true)) {
            $tab = 'tenants';
        }

        $saasTablesReady = FeatureGate::ready();

        $props = [
            'tab' => $tab,
            'saasTablesReady' => $saasTablesReady,
            'summary' => Inertia::defer(
                fn () => $saasTablesReady ? $this->loadSummary() : null,
                'saas-summary',
            ),
        ];

        return Inertia::render('AdminOpsSaas', $props);
    }

    /**
     * @return array<string, int>
     */
    private function loadSummary(): array
    {
        return Cache::remember('inertia:saas:summary:v1', now()->addSeconds(30), function (): array {
            $hasInvoices = Schema::hasTable('invoice_subscriptions');
            $invoiceSummary = [
                'invoice_pending_count' => 0,
                'invoice_verification_count' => 0,
                'invoice_overdue_count' => 0,
                'invoice_paid_month_count' => 0,
            ];

            if ($hasInvoices) {
                $hasPaymentProof = Schema::hasColumn('invoice_subscriptions', 'payment_proof');
                $hasDueDate = Schema::hasColumn('invoice_subscriptions', 'due_date');
                $invoiceSummary['invoice_pending_count'] = (int) DB::table('invoice_subscriptions')
                    ->where('status', 'pending')
                    ->when($hasPaymentProof, function ($query): void {
                        $query->where(function ($pending): void {
                            $pending->whereNull('payment_proof')->orWhere('payment_proof', '');
                        });
                    })
                    ->count();
                $invoiceSummary['invoice_verification_count'] = $hasPaymentProof
                    ? (int) DB::table('invoice_subscriptions')
                        ->where(function ($verification): void {
                            $verification
                                ->where('status', 'verification')
                                ->orWhere(function ($legacy): void {
                                    $legacy
                                        ->where('status', 'pending')
                                        ->whereNotNull('payment_proof')
                                        ->where('payment_proof', '!=', '');
                                });
                        })
                        ->count()
                    : 0;
                $overdueQuery = DB::table('invoice_subscriptions')->where('status', 'overdue');
                if ($hasDueDate) {
                    $overdueQuery->orWhere(function ($overdue): void {
                        $overdue
                            ->whereIn('status', ['pending', 'verification'])
                            ->whereDate('due_date', '<', now()->toDateString());
                    });
                }
                $invoiceSummary['invoice_overdue_count'] = (int) $overdueQuery->count();
                $invoiceSummary['invoice_paid_month_count'] = Schema::hasColumn('invoice_subscriptions', 'paid_at')
                    ? (int) DB::table('invoice_subscriptions')
                        ->where('status', 'paid')
                        ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count()
                    : 0;
            }

            return [
                'tenant_count' => (int) DB::table('tenants')->count(),
                'active_subscription_count' => (int) DB::table('subscriptions')
                    ->whereIn('status', ['trial', 'active'])
                    ->count(),
                'plan_count' => (int) DB::table('plans')->where('is_active', true)->count(),
                ...$invoiceSummary,
            ];
        });
    }
}
