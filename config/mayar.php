<?php

$apiUrl = rtrim((string) env('MAYAR_API_URL', ''), '/');
if (in_array($apiUrl, ['https://api.mayar.id', 'https://api.mayar.io'], true)) {
    $apiUrl .= '/hl/v2';
}
if (str_ends_with($apiUrl, '/hl/v1')) {
    $apiUrl = substr($apiUrl, 0, -strlen('/hl/v1')).'/hl/v2';
}

$paymentCreatePath = (string) env('MAYAR_PAYMENT_CREATE_PATH', '/invoices/create');
if ($paymentCreatePath === '/hl/v1/invoice/create' || $paymentCreatePath === '/invoice/create') {
    $paymentCreatePath = '/invoices/create';
}

return [
    'enabled' => (bool) env('MAYAR_ENABLED', false),
    'api_key' => env('MAYAR_API_KEY', ''),
    'environment' => in_array(env('MAYAR_ENV', 'sandbox'), ['sandbox', 'production'], true)
        ? env('MAYAR_ENV', 'sandbox')
        : 'sandbox',
    'api_url' => $apiUrl,
    'payment_create_path' => $paymentCreatePath ?: '/invoices/create',
    'webhook_secret' => env('MAYAR_WEBHOOK_SECRET', ''),
    'timeout' => (int) env('MAYAR_TIMEOUT', 15),
];
