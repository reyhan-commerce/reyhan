<?php

declare(strict_types=1);

namespace Reyhan\Core\Features;

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

    public const WALLET = 'wallet';

    public const REFERRAL = 'referral';

    public const RETURNS = 'returns';

    public const FAQ = 'faq';

    public const TICKETS = 'tickets';

    public const QUESTIONS = 'questions';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::REVIEWS => 'سیستم نقد و بررسی و امتیازدهی کاربران',
            self::COUPONS => 'سیستم کدهای تخفیف و پروموشن‌ها',
            self::WISHLIST => 'لیست علاقه‌مندی‌ها و بوک‌مارک کالاها',
            self::BRANDS => 'نمایش و فیلتر بر اساس برندها',
            self::STOCK_ALERTS => 'اطلاع‌رسانی پیامکی موجود شدن کالا',
            self::COMPARISON => 'مقایسه فنی مشخصات کالاها',
            self::BLOG => 'مجله اینترنتی و وبلاگ آموزشی',
            self::LOYALTY => 'باشگاه مشتریان و امتیاز وفاداری',
            self::WALLET => 'کیف پول و اعتبار آنلاین',
            self::REFERRAL => 'سیستم معرفی دوستان (کد معرف و پاداش)',
            self::RETURNS => 'درخواست آنلاین مرجوعی کالا (RMA)',
            self::FAQ => 'بخش سوالات متداول (FAQ)',
            self::TICKETS => 'سیستم تیکت و پشتیبانی مشتریان',
            self::QUESTIONS => 'پرسش و پاسخ تعاملی روی محصولات',
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
