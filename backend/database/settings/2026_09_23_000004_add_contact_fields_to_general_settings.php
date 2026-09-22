<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.work_hours', 'شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴');
        $this->migrator->add('general.whatsapp_url', null);
    }
};
