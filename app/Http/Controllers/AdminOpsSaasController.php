<?php

namespace App\Http\Controllers;

use App\Support\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsSaasController extends Controller
{
    /**
     * Render the SaaS management page with optional tab routing.
     *
     * Tabs: tenants, subscriptions, plans
     */
    public function __invoke(Request $request): Response
    {
        $tab = trim((string) $request->route('tab', 'tenants'));
        if (! in_array($tab, ['tenants', 'subscriptions', 'plans', 'payment'], true)) {
            $tab = 'tenants';
        }

        $props = [
            'tab' => $tab,
            'saasTablesReady' => FeatureGate::ready(),
        ];

        // Pre-load summary counts for tab badges
        if (FeatureGate::ready()) {
            $props['summary'] = [
                'tenant_count' => (int) DB::table('tenants')->count(),
                'active_subscription_count' => (int) DB::table('subscriptions')
                    ->whereIn('status', ['trial', 'active'])
                    ->count(),
                'plan_count' => (int) DB::table('plans')->where('is_active', true)->count(),
            ];
        }

        return Inertia::render('AdminOpsSaas', $props);
    }
}
