<?php

declare(strict_types=1);

namespace Reyhan\Core\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    public string $active_driver;

    public ?string $kavenegar_api_key;

    public ?string $kavenegar_sender;

    public ?string $kavenegar_otp_pattern;

    public ?string $farazsms_api_key;

    public ?string $farazsms_sender;

    public ?string $farazsms_otp_pattern;

    public ?string $ghasedak_api_key;

    public ?string $ghasedak_sender;

    public ?string $ghasedak_otp_template;

    public static function group(): string
    {
        return 'sms';
    }

    /**
     * @return array<int, string>
     */
    public static function encrypted(): array
    {
        return [
            'kavenegar_api_key',
            'farazsms_api_key',
            'ghasedak_api_key',
        ];
    }
}
