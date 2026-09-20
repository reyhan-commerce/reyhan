<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Integrations\FarazSms\FarazSmsClient;
use App\Services\Integrations\Ghasedak\GhasedakClient;
use App\Services\Integrations\Kavenegar\KavenegarClient;
use App\Services\Sms\SmsManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register any SMS application services.
     */
    public function register(): void
    {
        // Bind clients as transient (bind) so container resolves fresh dependencies when needed
        $this->app->bind(KavenegarClient::class);
        $this->app->bind(FarazSmsClient::class);
        $this->app->bind(GhasedakClient::class);

        $this->app->singleton('sms', function (Application $app): SmsManager {
            return new SmsManager($app);
        });

        $this->app->alias('sms', SmsManager::class);
    }
}
