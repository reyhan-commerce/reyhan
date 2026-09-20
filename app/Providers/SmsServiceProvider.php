<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Integrations\FarazSms\FarazSmsClient;
use App\Services\Integrations\Ghasedak\GhasedakClient;
use App\Services\Integrations\Kavenegar\KavenegarClient;
use App\Services\Sms\SmsManager;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
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
            /** @var ConfigRepository $config */
            $config = $app->make(ConfigRepository::class);
            /** @var array{api_key?: string, sender?: string, otp_pattern?: string} $options */
            $options = $config->get('sms.drivers.kavenegar', []);

            return new KavenegarClient(
                apiKey: (string) ($options['api_key'] ?? ''),
                sender: (string) ($options['sender'] ?? ''),
                otpPattern: (string) ($options['otp_pattern'] ?? '')
            );
        });

        $this->app->singleton(FarazSmsClient::class, function (Application $app): FarazSmsClient {
            /** @var ConfigRepository $config */
            $config = $app->make(ConfigRepository::class);
            /** @var array{api_key?: string, sender?: string, otp_pattern?: string} $options */
            $options = $config->get('sms.drivers.farazsms', []);

            return new FarazSmsClient(
                apiKey: (string) ($options['api_key'] ?? ''),
                sender: (string) ($options['sender'] ?? ''),
                otpPattern: (string) ($options['otp_pattern'] ?? '')
            );
        });

        $this->app->singleton(GhasedakClient::class, function (Application $app): GhasedakClient {
            /** @var ConfigRepository $config */
            $config = $app->make(ConfigRepository::class);
            /** @var array{api_key?: string, sender?: string, otp_template?: string} $options */
            $options = $config->get('sms.drivers.ghasedak', []);

            return new GhasedakClient(
                apiKey: (string) ($options['api_key'] ?? ''),
                sender: (string) ($options['sender'] ?? ''),
                otpTemplate: (string) ($options['otp_template'] ?? '')
            );
        });

        $this->app->singleton('sms', function (Application $app): SmsManager {
            return new SmsManager($app);
        });

        $this->app->alias('sms', SmsManager::class);
    }
}
