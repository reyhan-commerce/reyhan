<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.work_hours')) {
            $this->migrator->add('general.work_hours', 'شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴');
        }
        if (! $this->migrator->exists('general.whatsapp_url')) {
            $this->migrator->add('general.whatsapp_url', null);
        }
    }
};
