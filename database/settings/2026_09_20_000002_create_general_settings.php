<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.store_name', 'فروشگاه اینترنتی ایزیشاپ');
        $this->migrator->add('general.store_slogan', 'تخصصی‌ترین مرجع لوازم آرایشی و بهداشتی اصل');
        $this->migrator->add('general.store_logo', null);
        $this->migrator->add('general.store_favicon', null);
        $this->migrator->add('general.support_phone', '۰۲۱-۸۸۸۸۸۸۸۸');
        $this->migrator->add('general.support_email', 'support@easyshop.local');
        $this->migrator->add('general.address', 'تهران، خیابان ولیعصر، نرسیده به میدان ونک');
        $this->migrator->add('general.postal_code', '1999999999');
        $this->migrator->add('general.free_shipping_threshold', 500000);
        $this->migrator->add('general.is_store_open', true);
        $this->migrator->add('general.maintenance_message', null);
        $this->migrator->add('general.instagram_url', 'https://instagram.com/easyshop');
        $this->migrator->add('general.telegram_url', 'https://t.me/easyshop');
        $this->migrator->add('general.enamad_code', null);
    }
};
