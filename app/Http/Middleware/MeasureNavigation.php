<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MeasureNavigation
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);
        $response = $next($request);

        if (config('ui.navigation_metrics')) {
            // Measures Laravel processing only; TTFB also includes network and boot.
            $response->headers->set('Server-Timing', sprintf(
                'app;dur=%.2f', (hrtime(true) - $start) / 1_000_000,
            ), false);
        }

        return $response;
    }
}
