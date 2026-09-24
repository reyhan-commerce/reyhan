<?php

declare(strict_types=1);

namespace App\Features;

final class ShopFeature
{
    public const REVIEWS = 'reviews';

    public const COUPONS = 'coupons';

    public const WISHLIST = 'wishlist';

    public const BRANDS = 'brands';

    public const STOCK_ALERTS = 'stock_alerts';

    public const COMPARISON = 'comparison';

    public const BLOG = 'blog';

    public const LOYALTY = 'loyalty';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::REVIEWS => __('User reviews and ratings system'),
            self::COUPONS => __('Discount coupons and promotions system'),
            self::WISHLIST => __('Wishlist and product bookmarks'),
            self::BRANDS => __('Brands showcase and filtering'),
            self::STOCK_ALERTS => __('SMS alerts for restocked items'),
            self::COMPARISON => __('Product specification comparison'),
            self::BLOG => __('Blog and editorial magazine'),
            self::LOYALTY => __('Customer loyalty club'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::all());
    }
}
