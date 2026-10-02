<?php

declare(strict_types=1);

namespace Reyhan\Core\Support;

use Reyhan\Core\Contracts\Models\BrandContract;
use Reyhan\Core\Contracts\Models\CartContract;
use Reyhan\Core\Contracts\Models\CategoryContract;
use Reyhan\Core\Contracts\Models\OrderContract;
use Reyhan\Core\Contracts\Models\ProductContract;
use Reyhan\Core\Contracts\Models\ProductVariantContract;
use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Review;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class Reyhan
{
    /**
     * Runtime model registry bindings.
     *
     * @var array<string, class-string<Model>>
     */
    private static array $modelBindings = [];

    /**
     * Default core model mappings.
     *
     * @var array<string, class-string<Model>>
     */
    private static array $defaultModels = [
        'order' => Order::class,
        'order_item' => OrderItem::class,
        'product' => Product::class,
        'product_variant' => ProductVariant::class,
        'variant' => ProductVariant::class,
        'cart' => Cart::class,
        'cart_item' => CartItem::class,
        'user' => User::class,
        'admin' => Admin::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'address' => Address::class,
        'coupon' => Coupon::class,
        'review' => Review::class,
        'payment' => Payment::class,
        'shipping_method' => ShippingMethod::class,
        'wishlist' => Wishlist::class,
    ];

    /**
     * Get the current Reyhan engine version.
     */
    public static function version(): string
    {
        return (string) config('reyhan.version', '1.0.0');
    }

    /**
     * Bind a custom user model to replace a core model.
     *
     * @param  string  $alias  e.g. 'product', 'order', 'cart', 'user'
     * @param  class-string<Model>  $concrete
     */
    public static function useModel(string $alias, string $concrete): void
    {
        if (! is_subclass_of($concrete, Model::class)) {
            throw new InvalidArgumentException("Class [{$concrete}] must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        self::$modelBindings[$alias] = $concrete;
    }

    /**
     * Resolve the configured Eloquent model class for a given alias.
     *
     * @param  string  $alias  e.g. 'product', 'order', 'cart', 'user'
     * @return class-string<Model>
     */
    public static function model(string $alias): string
    {
        if (isset(self::$modelBindings[$alias])) {
            return self::$modelBindings[$alias];
        }

        $configured = config("reyhan.models.{$alias}");

        if (is_string($configured) && class_exists($configured)) {
            return $configured;
        }

        if (isset(self::$defaultModels[$alias])) {
            return self::$defaultModels[$alias];
        }

        throw new InvalidArgumentException("No model configured for Reyhan alias [{$alias}].");
    }

    /**
     * Instantiate a new model instance for the given alias.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function newModel(string $alias, array $attributes = []): Model
    {
        $class = self::model($alias);

        return new $class($attributes);
    }

    /**
     * Start a new query builder on the resolved model alias.
     */
    public static function query(string $alias): Builder
    {
        /** @var class-string<Model> $class */
        $class = self::model($alias);

        return $class::query();
    }

    /**
     * @return class-string<OrderContract&Model>
     */
    public static function orderModel(): string
    {
        /** @var class-string<OrderContract&Model> */
        return self::model('order');
    }

    /**
     * @return class-string<ProductContract&Model>
     */
    public static function productModel(): string
    {
        /** @var class-string<ProductContract&Model> */
        return self::model('product');
    }

    /**
     * @return class-string<ProductVariantContract&Model>
     */
    public static function variantModel(): string
    {
        /** @var class-string<ProductVariantContract&Model> */
        return self::model('variant');
    }

    /**
     * @return class-string<CartContract&Model>
     */
    public static function cartModel(): string
    {
        /** @var class-string<CartContract&Model> */
        return self::model('cart');
    }

    /**
     * @return class-string<UserContract&Model>
     */
    public static function userModel(): string
    {
        /** @var class-string<UserContract&Model> */
        return self::model('user');
    }

    /**
     * @return class-string<CategoryContract&Model>
     */
    public static function categoryModel(): string
    {
        /** @var class-string<CategoryContract&Model> */
        return self::model('category');
    }

    /**
     * @return class-string<BrandContract&Model>
     */
    public static function brandModel(): string
    {
        /** @var class-string<BrandContract&Model> */
        return self::model('brand');
    }

    /**
     * Get the core Cart service instance.
     */
    public static function cart(): \Reyhan\Core\Services\Cart\CartService
    {
        return app(\Reyhan\Core\Services\Cart\CartService::class);
    }

    /**
     * Get the core Stock/Inventory reservation service instance.
     */
    public static function inventory(): \Reyhan\Core\Services\Inventory\StockReservationService
    {
        return app(\Reyhan\Core\Services\Inventory\StockReservationService::class);
    }

    /**
     * Get the core Pricing service instance.
     */
    public static function pricing(): \Reyhan\Core\Services\Pricing\PricingService
    {
        return app(\Reyhan\Core\Services\Pricing\PricingService::class);
    }

    /**
     * Get the core Checkout service instance.
     */
    public static function checkout(): \Reyhan\Core\Services\Checkout\CheckoutService
    {
        return app(\Reyhan\Core\Services\Checkout\CheckoutService::class);
    }

    /**
     * Get the core double-entry accounting Ledger service instance.
     */
    public static function ledger(): \Reyhan\Core\Services\Accounting\LedgerService
    {
        return app(\Reyhan\Core\Services\Accounting\LedgerService::class);
    }
}
