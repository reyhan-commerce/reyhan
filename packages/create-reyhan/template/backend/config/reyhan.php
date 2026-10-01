<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Userland Model Overrides
    |--------------------------------------------------------------------------
    | Map core models to your custom extended models.
    | Example: 'product' => App\Models\CustomProduct::class
    */
    'models' => [
        // 'product' => \App\Models\Product::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Userland Request Validations & Resources
    |--------------------------------------------------------------------------
    */
    'requests' => [
        // 'checkout' => \App\Http\Requests\CustomCheckoutRequest::class,
    ],

    'resources' => [
        // 'product' => \App\Http\Resources\CustomProductResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Routes Auto-Loading
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'api_enabled' => true,
    ],
];
