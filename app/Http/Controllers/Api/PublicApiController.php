<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicApiController extends Controller
{
    /**
     * Public plans endpoint — no authentication required.
     * GET /api/plans
     */
    public function plans(): JsonResponse
    {
        if (! Schema::hasTable('plans')) {
            return response()->json(['plans' => []]);
        }

        $plans = DB::table('plans')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => (int) $p->id,
                    'name' => (string) $p->name,
                    'slug' => (string) $p->slug,
                    'description' => (string) ($p->description ?? ''),
                    'price_monthly' => (float) $p->price_monthly,
                    'price_yearly' => (float) $p->price_yearly,
                    'max_armadas' => (int) $p->max_armadas,
                    'max_routes' => (int) $p->max_routes,
                    'max_users' => (int) $p->max_users,
                    'max_pools' => (int) $p->max_pools,
                    'max_drivers' => (int) $p->max_drivers,
                ];
            })
            ->all();

        return response()->json(['plans' => $plans]);
    }
}
