<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Top Announcement Bar
        $this->migrator->add('general.announcement_enabled', true);
        $this->migrator->add('general.announcement_text', 'ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان • تضمین ۱۰۰٪ اصالت کالا');
        $this->migrator->add('general.announcement_link', null);

        // Home Hero Banner
        $this->migrator->add('general.hero_badge_text', 'تخفیف‌های ویژه و محصولات برگزیده');
        $this->migrator->add('general.hero_primary_button_text', 'مشاهده کل کاتالوگ');
        $this->migrator->add('general.hero_secondary_button_text', 'دسته‌بندی‌های کالا');

        // Home Trust Badges (Structured array)
        $this->migrator->add('general.trust_badges', [
            [
                'icon' => 'i-lucide-shield-check',
                'title' => 'ضمانت ۱۰۰٪ اصالت کالا',
                'desc' => 'تمامی کالاها با برچسب اصالت و ضمانت سلامت',
            ],
            [
                'icon' => 'i-lucide-truck',
                'title' => 'ارسال سریع به سراسر ایران',
                'desc' => 'پست پیشتاز و تیپاکس اکسپرس',
            ],
            [
                'icon' => 'i-lucide-rotate-ccw',
                'title' => '۷ روز ضمانت بازگشت',
                'desc' => 'امکان عودت کالا در صورت نارضایتی',
            ],
            [
                'icon' => 'i-lucide-headphones',
                'title' => 'مشاوره و پشتیبانی خرید',
                'desc' => 'پاسخگویی سریع و راهنمایی تخصصی انتخاب محصول',
            ],
        ]);

        // Home Sections Titles & Action Buttons
        $this->migrator->add('general.categories_title', 'دسته‌بندی‌های تخصصی');
        $this->migrator->add('general.categories_button_text', 'مشاهده نقشه کامل');
        $this->migrator->add('general.flash_deals_title', 'پیشنهادات شگفت‌انگیز روز');
        $this->migrator->add('general.flash_deals_subtitle', 'فرصت محدود با تخفیف‌های ویژه تا پایان امروز');
        $this->migrator->add('general.featured_products_title', 'محصولات برگزیده فروشگاه');
        $this->migrator->add('general.featured_products_button_text', 'مشاهده همه کاتالوگ');
        $this->migrator->add('general.blog_title', 'مجله تخصصی و تازه‌ترین مقالات');
        $this->migrator->add('general.blog_button_text', 'ورود به وبلاگ');
        $this->migrator->add('general.brands_title', 'اصیل‌ترین برندهای معتبر جهانی و ایرانی');

        // Footer & Copyright
        $this->migrator->add('general.footer_about_text', null);
        $this->migrator->add('general.footer_copyright_text', 'تمامی حقوق مادی و معنوی متعلق به این فروشگاه می‌باشد.');
        $this->migrator->add('general.footer_designer_credit', 'طراحی شده با رعایت استانداردهای تجربه کاربری و تجارت الکترونیک');
    }
};
