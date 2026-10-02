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
        'product' => \Reyhan\Core\Models\Product::class,
        'product_variant' => \Reyhan\Core\Models\ProductVariant::class,
        'category' => \Reyhan\Core\Models\Category::class,
        'brand' => \Reyhan\Core\Models\Brand::class,
        'order' => \Reyhan\Core\Models\Order::class,
        'order_item' => \Reyhan\Core\Models\OrderItem::class,
        'cart' => \Reyhan\Core\Models\Cart::class,
        'cart_item' => \Reyhan\Core\Models\CartItem::class,
        'user' => \Reyhan\Core\Models\User::class,
        'admin' => \Reyhan\Core\Models\Admin::class,
        'address' => \Reyhan\Core\Models\Address::class,
        'coupon' => \Reyhan\Core\Models\Coupon::class,
        'review' => \Reyhan\Core\Models\Review::class,
        'payment' => \Reyhan\Core\Models\Payment::class,
        'shipping_method' => \Reyhan\Core\Models\ShippingMethod::class,
        'wishlist' => \Reyhan\Core\Models\Wishlist::class,
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
        'tax_rate_percent' => (int) env('STORE_TAX_RATE_PERCENT', 10),
        'tax_mode' => env('STORE_TAX_MODE', 'exclusive'), // 'exclusive' | 'inclusive'
        'locale' => env('APP_LOCALE', 'fa'),
        'fallback_locale' => 'en',
    ],

];
