<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Reyhan Engine Core Version
    |--------------------------------------------------------------------------
    |
    | The current version of the Reyhan Commerce Core framework.
    | This value is verified during zero-downtime updates and health checks.
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Dynamic Model Registry (Extensibility & Model Swapping)
    |--------------------------------------------------------------------------
    |
    | To keep end-users completely away from modifying core files, all domain
    | actions, services, and queries resolve Eloquent models through this
    | registry. Users can extend the base model and swap the class here.
    |
    */

    'models' => [
        'product' => \App\Models\Product::class,
        'product_variant' => \App\Models\ProductVariant::class,
        'category' => \App\Models\Category::class,
        'brand' => \App\Models\Brand::class,
        'order' => \App\Models\Order::class,
        'order_item' => \App\Models\OrderItem::class,
        'cart' => \App\Models\Cart::class,
        'cart_item' => \App\Models\CartItem::class,
        'user' => \App\Models\User::class,
        'admin' => \App\Models\Admin::class,
        'address' => \App\Models\Address::class,
        'coupon' => \App\Models\Coupon::class,
        'review' => \App\Models\Review::class,
        'payment' => \App\Models\Payment::class,
        'shipping_method' => \App\Models\ShippingMethod::class,
        'wishlist' => \App\Models\Wishlist::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Modules & Extensions Directory
    |--------------------------------------------------------------------------
    |
    | Reyhan provides a drop-in extension architecture. Custom user modules
    | placed inside this directory are auto-discovered without touching core
    | application directories.
    |
    */

    'extensions' => [
        'enabled' => env('REYHAN_EXTENSIONS_ENABLED', true),
        'directory' => base_path('extensions'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Pipeline Extensibility
    |--------------------------------------------------------------------------
    |
    | Pipelines enable users to inject custom stages into critical workflows
    | (like checkout, pricing, or fraud detection) without modifying core code.
    |
    */

    'pipelines' => [
        'checkout' => [
            // Custom pipeline steps registered by extensions or AppServiceProvider
        ],
        'pricing' => [
            // Custom discount / tax / promotion pipeline steps
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Automated Update Pipeline Safeguards
    |--------------------------------------------------------------------------
    |
    | Configuration governing the behaviour of `php artisan reyhan:update`
    | and root zero-downtime updates.
    |
    */

    'updates' => [
        'backup_database_before_update' => env('REYHAN_BACKUP_ON_UPDATE', true),
        'run_migrations' => true,
        'upgrade_filament_assets' => true,
        'optimize_caches' => true,
        'reload_octane' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storefront Identity Defaults
    |--------------------------------------------------------------------------
    */

    'store' => [
        'name' => env('APP_NAME', 'فروشگاه ریحان'),
        'currency' => env('STORE_CURRENCY', 'IRR'),
        'currency_symbol' => env('STORE_CURRENCY_SYMBOL', 'تومان'),
        'locale' => env('APP_LOCALE', 'fa'),
        'fallback_locale' => 'en',
    ],

];
