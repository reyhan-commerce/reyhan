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
            self::REVIEWS => 'سیستم نظرات و امتیازات کاربران',
            self::COUPONS => 'سیستم کدهای تخفیف و پروموشن',
            self::WISHLIST => 'لیست علاقه‌مندی‌ها و بوک‌مارک کالا',
            self::BRANDS => 'ویترین و فیلتر برندها',
            self::STOCK_ALERTS => 'اطلاع‌رسانی پیامکی موجود شدن کالا',
            self::COMPARISON => 'مقایسه مشخصات و ویژگی‌های کالاها',
            self::BLOG => 'سیستم وبلاگ و مجله تخصصی',
            self::LOYALTY => 'باشگاه مشتریان و سیستم وفاداری (Loyalty Club)',
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
