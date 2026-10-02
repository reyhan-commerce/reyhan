<?php

declare(strict_types=1);

namespace Reyhan\Core\Settings;

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

    public bool $announcement_enabled;

    public ?string $announcement_text;

    public ?string $announcement_link;

    public ?string $hero_badge_text;

    public ?string $hero_primary_button_text;

    public ?string $hero_secondary_button_text;

    public ?array $trust_badges;

    public ?string $categories_title;

    public ?string $categories_button_text;

    public ?string $flash_deals_title;

    public ?string $flash_deals_subtitle;

    public ?string $featured_products_title;

    public ?string $featured_products_button_text;

    public ?string $blog_title;

    public ?string $blog_button_text;

    public ?string $brands_title;

    public ?string $footer_about_text;

    public ?string $footer_copyright_text;

    public ?string $footer_designer_credit;

    public int $referral_reward_toman;

    public ?string $referral_banner_title;

    public ?string $referral_banner_desc;

    public int $return_guarantee_days;

    public ?string $return_policy_notice;

    public ?string $tax_invoice_notice;

    public ?string $support_work_hours_notice;

    public static function group(): string
    {
        return 'general';
    }
}
