<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported: "sandbox", "zarinpal", "saman", "mellat"
    |
    */
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'sandbox'),

    'gateways' => [
        'sandbox' => [
            'name' => 'درگاه آزمایشی سندباکس',
            'active' => true,
        ],

        'zarinpal' => [
            'name' => 'زرین‌پال',
            'merchant_id' => env('ZARINPAL_MERCHANT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'),
            'sandbox' => env('ZARINPAL_SANDBOX', false),
            'mode' => env('ZARINPAL_MODE', 'normal'),
            'active' => env('ZARINPAL_ACTIVE', true),
        ],

        'saman' => [
            'name' => 'بانک سامان (سپ)',
            'terminal_id' => env('SEP_TERMINAL_ID', ''),
            'active' => env('SEP_ACTIVE', false),
        ],

        'mellat' => [
            'name' => 'به پرداخت ملت',
            'terminal_id' => env('MELLAT_TERMINAL_ID', ''),
            'username' => env('MELLAT_USERNAME', ''),
            'password' => env('MELLAT_PASSWORD', ''),
            'active' => env('MELLAT_ACTIVE', false),
        ],
    ],
];
