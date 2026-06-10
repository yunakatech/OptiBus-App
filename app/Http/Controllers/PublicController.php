<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    /**
     * Welcome / Landing page — full width, no sidebar layout.
     * GET /
     */
    public function welcome(): Response
    {
        return Inertia::render('Welcome', [
            'plans' => $this->publicPlans(),
        ]);
    }

    /**
     * Pricing page — guest layout.
     * GET /pricing
     */
    public function pricing(): Response
    {
        return Inertia::render('Pricing', [
            'plans' => $this->publicPlans(),
        ]);
    }

    /**
     * Fetch public plan data (no auth required).
     */
    private function publicPlans(): array
    {
        if (! Schema::hasTable('plans')) {
            return [];
        }

        return DB::table('plans')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($p) {
                // Get feature list for this plan
                $features = [];
                if (Schema::hasTable('plan_feature') && Schema::hasTable('feature_gates')) {
                    $features = DB::table('plan_feature')
                        ->join('feature_gates', 'plan_feature.feature_gate_id', '=', 'feature_gates.id')
                        ->where('plan_feature.plan_id', $p->id)
                        ->orderBy('feature_gates.feature_group')
                        ->orderBy('feature_gates.feature_name')
                        ->select('feature_gates.feature_name', 'feature_gates.feature_group', 'plan_feature.max_value')
                        ->get()
                        ->map(fn ($f) => [
                            'name' => (string) $f->feature_name,
                            'group' => (string) $f->feature_group,
                            'included' => $f->max_value === null || (int) $f->max_value > 0,
                            'limit' => $f->max_value,
                        ])
                        ->all();
                }

                return [
                    'id' => (int) $p->id,
                    'name' => (string) $p->name,
                    'slug' => (string) $p->slug,
                    'description' => (string) ($p->description ?? ''),
                    'price_monthly' => (float) $p->price_monthly,
                    'price_yearly' => (float) $p->price_yearly,
                    'max_pools' => (int) $p->max_pools,
                    'max_users' => (int) $p->max_users,
                    'max_armadas' => (int) $p->max_armadas,
                    'max_routes' => (int) $p->max_routes,
                    'max_drivers' => (int) $p->max_drivers,
                    'features' => $features,
                ];
            })
            ->all();
    }
}
