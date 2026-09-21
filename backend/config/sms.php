<?php

declare(strict_types=1);

return [
    'default' => env('SMS_DEFAULT_DRIVER', 'log'),

    'drivers' => [
        'log' => [],

        'kavenegar' => [
            'api_key' => env('KAVENEGAR_API_KEY', ''),
            'sender' => env('KAVENEGAR_SENDER', ''),
            'otp_pattern' => env('KAVENEGAR_OTP_PATTERN', ''),
        ],

        'farazsms' => [
            'api_key' => env('FARAZSMS_API_KEY', ''),
            'sender' => env('FARAZSMS_SENDER', ''),
            'otp_pattern' => env('FARAZSMS_OTP_PATTERN', ''),
        ],

        'ghasedak' => [
            'api_key' => env('GHASEDAK_API_KEY', ''),
            'sender' => env('GHASEDAK_SENDER', ''),
            'otp_template' => env('GHASEDAK_OTP_TEMPLATE', ''),
        ],
    ],
];
