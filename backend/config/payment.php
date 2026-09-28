<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported: "sandbox", "zarinpal", "snapp_pay", "card_to_card", "wallet", "saman", "mellat"
    |
    */
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'sandbox'),

    'gateways' => [
        'sandbox' => [
            'name' => 'درگاه آزمایشی سندباکس',
            'active' => true,
        ],

        'snapp_pay' => [
            'name' => 'خرید اقساطی اسنپ‌پی',
            'client_id' => env('SNAPP_PAY_CLIENT_ID', 'sandbox_client'),
            'client_secret' => env('SNAPP_PAY_CLIENT_SECRET', 'sandbox_secret'),
            'active' => true,
        ],

        'card_to_card' => [
            'name' => 'پرداخت کارت‌به‌کارت',
            'card_number' => env('CARD_TO_CARD_NUMBER', '6037-9975-1234-5678'),
            'card_holder' => env('CARD_TO_CARD_HOLDER', 'فروشگاه اینترنتی ایزی‌شاپ'),
            'bank_name' => env('CARD_TO_CARD_BANK', 'بانک ملی ایران'),
            'active' => true,
        ],

        'wallet' => [
            'name' => 'کیف پول کاربری',
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
