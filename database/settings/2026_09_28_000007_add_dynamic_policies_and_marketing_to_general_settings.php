<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Referral program dynamic settings
        $this->migrator->add('general.referral_reward_toman', 50000);
        $this->migrator->add('general.referral_banner_title', 'دوستانت را دعوت کن، هدیه نقدی بگیر!');
        $this->migrator->add('general.referral_banner_desc', 'با دعوت دوستان، آن‌ها با تخفیف ویژه خرید می‌کنند و با اولین خرید موفق هر دوست، هدیه نقدی مستقیماً به کیف پول شما واریز می‌شود.');

        // Returns (RMA) & Guarantee policy settings
        $this->migrator->add('general.return_guarantee_days', 7);
        $this->migrator->add('general.return_policy_notice', 'تا ۷ روز پس از دریافت سفارش، در صورت باز نشدن پلمپ و حفظ سلامت فیزیکی بسته، می‌توانید درخواست مرجوعی ثبت نمایید.');

        // Official Tax Invoice (ماده ۱۹) notice
        $this->migrator->add('general.tax_invoice_notice', 'صورتحساب رسمی مطابق ماده ۱۹ قانون مالیات بر ارزش افزوده با درج کد اقتصادی و شناسه ملی فروشنده و خریدار صادر گردیده است.');

        // Support work hours notice
        $this->migrator->add('general.support_work_hours_notice', 'شنبه تا چهارشنبه ۹ الی ۱۸ | پنج‌شنبه‌ها ۹ الی ۱۴ (پاسخگویی آنلاین و تلفنی)');
    }
};
