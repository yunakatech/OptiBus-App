<?php

return [
    // Empty means use the hosting default: enabled locally, disabled on Vercel.
    'ops_deferred' => env('OPS_INERTIA_DEFERRED'),
    'navigation_metrics' => env('NAVIGATION_METRICS', false),
];
