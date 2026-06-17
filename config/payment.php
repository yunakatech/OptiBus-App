<?php

/**
 * Legacy manual payment configuration.
 *
 * SaaS subscription billing uses Mayar. These values are kept only for
 * backward compatibility or non-SaaS operational flows that still read them.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | QRIS Static
    |--------------------------------------------------------------------------
    |
    | Nama merchant/owner yang akan tampil di QRIS.
    | QRIS image disimpan di public/images/qris.png
    | Nominal QRIS statis sebenarnya â€” nominal dinamis ditampilkan
    | sebagai label teks di atas QR.
    |
    */
    'qris' => [
        'enabled' => env('PAYMENT_QRIS_ENABLED', false),
        'merchant_name' => env('PAYMENT_QRIS_MERCHANT_NAME', 'OptiBus Indonesia'),
        'image_path' => env('PAYMENT_QRIS_IMAGE', 'images/qris.png'),
        'note' => 'Legacy manual payment setting. SaaS billing uses Mayar.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bank Transfer
    |--------------------------------------------------------------------------
    */
    'bank_transfer' => [
        'enabled' => env('PAYMENT_TRANSFER_ENABLED', false),
        'accounts' => [
            [
                'bank_name' => env('PAYMENT_BANK_1_NAME', 'BCA'),
                'account_number' => env('PAYMENT_BANK_1_NUMBER', '1234567890'),
                'account_holder' => env('PAYMENT_BANK_1_HOLDER', 'PT OptiBus Indonesia'),
                'note' => 'Legacy manual payment setting. SaaS billing uses Mayar.',
            ],
            [
                'bank_name' => env('PAYMENT_BANK_2_NAME', 'BRI'),
                'account_number' => env('PAYMENT_BANK_2_NUMBER', '0987654321'),
                'account_holder' => env('PAYMENT_BANK_2_HOLDER', 'PT OptiBus Indonesia'),
                'note' => 'Legacy manual payment setting. SaaS billing uses Mayar.',
            ],
            [
                'bank_name' => env('PAYMENT_BANK_3_NAME', 'Mandiri'),
                'account_number' => env('PAYMENT_BANK_3_NUMBER', '1122334455'),
                'account_holder' => env('PAYMENT_BANK_3_HOLDER', 'PT OptiBus Indonesia'),
                'note' => 'Legacy manual payment setting. SaaS billing uses Mayar.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Bukti Transfer
    |--------------------------------------------------------------------------
    |
    | Maksimum file size dalam KB dan format yang diizinkan.
    | File disimpan di storage/app/public/payment-proofs/
    |
    */
    'upload' => [
        'max_size_kb' => (int) env('PAYMENT_UPLOAD_MAX_KB', 2048),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],
];
