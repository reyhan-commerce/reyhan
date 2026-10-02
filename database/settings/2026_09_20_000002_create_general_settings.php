<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.store_name', 'فروشگاه اینترنتی ریحان');
        $this->migrator->add('general.store_slogan', 'خرید آنلاین هوشمند و سریع با تضمین اصالت و بهترین قیمت');
        $this->migrator->add('general.store_logo', null);
        $this->migrator->add('general.store_favicon', null);
        $this->migrator->add('general.support_phone', '۰۲۱-۸۸۸۸۸۸۸۸');
        $this->migrator->add('general.support_email', 'support@reyhan.local');
        $this->migrator->add('general.address', 'تهران، خیابان ولیعصر، نرسیده به میدان ونک');
        $this->migrator->add('general.postal_code', '1999999999');
        $this->migrator->add('general.free_shipping_threshold', 500000);
        $this->migrator->add('general.is_store_open', true);
        $this->migrator->add('general.maintenance_message', null);
        $this->migrator->add('general.instagram_url', 'https://instagram.com/reyhan');
        $this->migrator->add('general.telegram_url', 'https://t.me/reyhan');
        $this->migrator->add('general.work_hours', 'شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴');
        $this->migrator->add('general.whatsapp_url', null);
        $this->migrator->add('general.loyalty_rate_amount_per_point', 10000);
        $this->migrator->add('general.loyalty_point_redemption_value', 500);
        $this->migrator->add('general.loyalty_signup_bonus', 50);
        $this->migrator->add('general.enamad_code', null);
    }
};
