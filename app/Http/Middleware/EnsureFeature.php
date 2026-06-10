<?php

namespace App\Http\Middleware;

use App\Support\FeatureGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    /**
     * Handle an incoming request — check if the tenant's plan includes the required feature.
     *
     * Usage in routes:
     *   ->middleware('feature:booking.seat_map')
     *   ->middleware('feature:report.export_csv,charter.full')
     *
     * Multiple features are OR'd (any one is sufficient).
     */
    public function handle(Request $request, Closure $next, string ...$featureKeys): Response
    {
        $userId = (int) ($request->user()?->id ?? 0);
        $featureKeys = array_values(array_filter($featureKeys, static fn (string $key): bool => $key !== ''));

        if ($featureKeys === []) {
            return $next($request);
        }

        // At least one feature must pass
        foreach ($featureKeys as $featureKey) {
            if (FeatureGate::can($featureKey, 0, $userId)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Fitur ini tidak tersedia pada paket langganan Anda. Silakan upgrade paket.',
            ], 403);
        }

        abort(403, 'Fitur ini tidak tersedia pada paket langganan Anda. Silakan upgrade paket.');
    }
}
