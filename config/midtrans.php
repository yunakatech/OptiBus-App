<?php

/**
 * Midtrans Payment Gateway Configuration
 *
 * @see https://docs.midtrans.com/
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Server Key
    |--------------------------------------------------------------------------
    |
    | Midtrans server key for API authentication.
    | Use SB-Mid-server-* for Sandbox, Mid-server-* for Production.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Client Key
    |--------------------------------------------------------------------------
    |
    | Midtrans client key for Snap.js frontend integration.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | 'sandbox' or 'production'
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | 3DS / Security
    |--------------------------------------------------------------------------
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Snap.js URL
    |--------------------------------------------------------------------------
    */
    'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),

    /*
    |--------------------------------------------------------------------------
    | Merchant ID (optional, for certain features)
    |--------------------------------------------------------------------------
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
];
