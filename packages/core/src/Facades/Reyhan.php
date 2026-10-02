<?php

declare(strict_types=1);

namespace Reyhan\Core\Facades;

use Reyhan\Core\Support\Reyhan as ReyhanSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string version()
 * @method static void useModel(string $alias, string $concrete)
 * @method static class-string<\Illuminate\Database\Eloquent\Model> model(string $alias)
 * @method static \Illuminate\Database\Eloquent\Model newModel(string $alias, array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Builder query(string $alias)
 * @method static class-string<\Reyhan\Core\Contracts\Models\OrderContract&\Illuminate\Database\Eloquent\Model> orderModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\ProductContract&\Illuminate\Database\Eloquent\Model> productModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\ProductVariantContract&\Illuminate\Database\Eloquent\Model> variantModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\CartContract&\Illuminate\Database\Eloquent\Model> cartModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\UserContract&\Illuminate\Database\Eloquent\Model> userModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\CategoryContract&\Illuminate\Database\Eloquent\Model> categoryModel()
 * @method static class-string<\Reyhan\Core\Contracts\Models\BrandContract&\Illuminate\Database\Eloquent\Model> brandModel()
 * @method static \Reyhan\Core\Services\Cart\CartService cart()
 * @method static \Reyhan\Core\Services\Inventory\StockReservationService inventory()
 * @method static \Reyhan\Core\Services\Pricing\PricingService pricing()
 * @method static \Reyhan\Core\Services\Checkout\CheckoutService checkout()
 *
 * @see \Reyhan\Core\Support\Reyhan
 */
final class Reyhan extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReyhanSupport::class;
    }
}
