<?php

declare(strict_types=1);

namespace Reyhan\Core\Providers;

use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Services\Integrations\FarazSms\FarazSmsClient;
use Reyhan\Core\Services\Integrations\Ghasedak\GhasedakClient;
use Reyhan\Core\Services\Integrations\Kavenegar\KavenegarClient;
use Reyhan\Core\Services\Sms\SmsManager;
use Reyhan\Core\Settings\SmsSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register any SMS application services.
     */
    public function register(): void
    {
        // 1. Transient binds for integration clients (zero stale state)
        $this->app->bind(KavenegarClient::class);
        $this->app->bind(FarazSmsClient::class);
        $this->app->bind(GhasedakClient::class);

        // 2. Transient bind for SmsManager so each call/request resolves fresh state
        $this->app->bind('sms', function (Application $app): SmsManager {
            return new SmsManager($app);
        });

        $this->app->alias('sms', SmsManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 3. Register custom SMS channel for Laravel Notification system
        Notification::extend('sms', function (Application $app): SmsChannel {
            return $app->make(SmsChannel::class);
        });

        // 4. Invalidate cached settings instance in container when admin updates settings
        Event::listen(SettingsSaved::class, function (SettingsSaved $event): void {
            if ($event->settings instanceof SmsSettings) {
                $this->app->forgetInstance(SmsSettings::class);
            }
        });
    }
}
