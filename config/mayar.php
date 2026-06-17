<?php

return [
    'enabled' => (bool) env('MAYAR_ENABLED', false),
    'api_key' => env('MAYAR_API_KEY', ''),
    'api_url' => rtrim((string) env('MAYAR_API_URL', 'https://api.mayar.id'), '/'),
    'payment_create_path' => env('MAYAR_PAYMENT_CREATE_PATH', '/hl/v1/payment/create'),
    'webhook_secret' => env('MAYAR_WEBHOOK_SECRET', ''),
    'timeout' => (int) env('MAYAR_TIMEOUT', 15),
];
