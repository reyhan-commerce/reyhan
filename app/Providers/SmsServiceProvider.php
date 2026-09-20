<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Integrations\FarazSms\FarazSmsClient;
use App\Services\Integrations\Ghasedak\GhasedakClient;
use App\Services\Integrations\Kavenegar\KavenegarClient;
use App\Services\Sms\SmsManager;
use App\Settings\SmsSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register any SMS application services.
     */
    public function register(): void
    {
        $this->app->singleton(KavenegarClient::class, function (Application $app): KavenegarClient {
            $settings = $app->make(SmsSettings::class);

            return new KavenegarClient(
                apiKey: (string) ($settings->kavenegar_api_key ?? ''),
                sender: (string) ($settings->kavenegar_sender ?? ''),
                otpPattern: (string) ($settings->kavenegar_otp_pattern ?? '')
            );
        });

        $this->app->singleton(FarazSmsClient::class, function (Application $app): FarazSmsClient {
            $settings = $app->make(SmsSettings::class);

            return new FarazSmsClient(
                apiKey: (string) ($settings->farazsms_api_key ?? ''),
                sender: (string) ($settings->farazsms_sender ?? ''),
                otpPattern: (string) ($settings->farazsms_otp_pattern ?? '')
            );
        });

        $this->app->singleton(GhasedakClient::class, function (Application $app): GhasedakClient {
            $settings = $app->make(SmsSettings::class);

            return new GhasedakClient(
                apiKey: (string) ($settings->ghasedak_api_key ?? ''),
                sender: (string) ($settings->ghasedak_sender ?? ''),
                otpTemplate: (string) ($settings->ghasedak_otp_template ?? '')
            );
        });

        $this->app->singleton('sms', function (Application $app): SmsManager {
            return new SmsManager($app);
        });

        $this->app->alias('sms', SmsManager::class);
    }
}
