<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $store_name;

    public ?string $store_slogan;

    public ?string $store_logo;

    public ?string $store_favicon;

    public ?string $support_phone;

    public ?string $support_email;

    public ?string $address;

    public ?string $postal_code;

    public ?string $work_hours;

    public ?string $whatsapp_url;

    public int $free_shipping_threshold;

    public bool $is_store_open;

    public ?string $maintenance_message;

    public int $loyalty_rate_amount_per_point;

    public int $loyalty_point_redemption_value;

    public int $loyalty_signup_bonus;

    public ?string $instagram_url;

    public ?string $telegram_url;

    public ?string $enamad_code;

    public static function group(): string
    {
        return 'general';
    }
}
