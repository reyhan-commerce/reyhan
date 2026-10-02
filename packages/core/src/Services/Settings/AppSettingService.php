<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Settings;

use Reyhan\Core\Settings\GeneralSettings;
use Reyhan\Core\Settings\ThemeSettings;
use Illuminate\Support\Facades\Cache;

class AppSettingService
{
    public const string CACHE_KEY = 'app:settings:general';

    public const string THEME_CACHE_KEY = 'app:settings:theme';

    protected const int TTL_SECONDS = 86400;

    public function __construct(
        protected GeneralSettings $settings,
        protected ThemeSettings $themeSettings
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getPublicSettings(): array
    {
        /** @var array<string, mixed> $publicSettings */
        $publicSettings = Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
            $themeData = [
                'primary_color' => $this->themeSettings->primary_color,
                'secondary_color' => $this->themeSettings->secondary_color,
                'border_radius' => $this->themeSettings->border_radius,
                'spacing_scale' => $this->themeSettings->spacing_scale,
                'shadow_scale' => $this->themeSettings->shadow_scale,
                'blur_scale' => $this->themeSettings->blur_scale,
                'font_family' => $this->themeSettings->font_family,
                'font_scale' => $this->themeSettings->font_scale,
                'logo_light' => $this->themeSettings->logo_light,
                'logo_dark' => $this->themeSettings->logo_dark,
                'favicon' => $this->themeSettings->favicon,
            ];

            return [
                'store_name' => $this->settings->store_name,
                'store_slogan' => $this->settings->store_slogan,
                'store_logo' => $this->themeSettings->logo_light ?: $this->settings->store_logo,
                'store_favicon' => $this->themeSettings->favicon ?: $this->settings->store_favicon,
                'support_phone' => $this->settings->support_phone,
                'support_email' => $this->settings->support_email,
                'address' => $this->settings->address,
                'postal_code' => $this->settings->postal_code,
                'work_hours' => $this->settings->work_hours,
                'free_shipping_threshold' => $this->settings->free_shipping_threshold,
                'is_store_open' => $this->settings->is_store_open,
                'maintenance_message' => $this->settings->maintenance_message,
                'instagram_url' => $this->settings->instagram_url,
                'telegram_url' => $this->settings->telegram_url,
                'whatsapp_url' => $this->settings->whatsapp_url,
                'enamad_code' => $this->settings->enamad_code,
                'announcement_enabled' => $this->settings->announcement_enabled ?? true,
                'announcement_text' => $this->settings->announcement_text,
                'announcement_link' => $this->settings->announcement_link,
                'hero_badge_text' => $this->settings->hero_badge_text,
                'hero_primary_button_text' => $this->settings->hero_primary_button_text,
                'hero_secondary_button_text' => $this->settings->hero_secondary_button_text,
                'trust_badges' => $this->settings->trust_badges ?? [],
                'categories_title' => $this->settings->categories_title,
                'categories_button_text' => $this->settings->categories_button_text,
                'flash_deals_title' => $this->settings->flash_deals_title,
                'flash_deals_subtitle' => $this->settings->flash_deals_subtitle,
                'featured_products_title' => $this->settings->featured_products_title,
                'featured_products_button_text' => $this->settings->featured_products_button_text,
                'blog_title' => $this->settings->blog_title,
                'blog_button_text' => $this->settings->blog_button_text,
                'brands_title' => $this->settings->brands_title,
                'footer_about_text' => $this->settings->footer_about_text,
                'footer_copyright_text' => $this->settings->footer_copyright_text,
                'footer_designer_credit' => $this->settings->footer_designer_credit,
                'referral_reward_toman' => $this->settings->referral_reward_toman ?? 50000,
                'referral_banner_title' => $this->settings->referral_banner_title,
                'referral_banner_desc' => $this->settings->referral_banner_desc,
                'return_guarantee_days' => $this->settings->return_guarantee_days ?? 7,
                'return_policy_notice' => $this->settings->return_policy_notice,
                'tax_invoice_notice' => $this->settings->tax_invoice_notice,
                'support_work_hours_notice' => $this->settings->support_work_hours_notice,
                'theme' => $themeData,
            ];
        });

        return $publicSettings;
    }

    /**
     * @return array<string, mixed>
     */
    public function getThemeTokens(): array
    {
        /** @var array<string, mixed> $themeTokens */
        $themeTokens = Cache::remember(self::THEME_CACHE_KEY, self::TTL_SECONDS, function () {
            return [
                'primary_color' => $this->themeSettings->primary_color,
                'secondary_color' => $this->themeSettings->secondary_color,
                'border_radius' => $this->themeSettings->border_radius,
                'spacing_scale' => $this->themeSettings->spacing_scale,
                'shadow_scale' => $this->themeSettings->shadow_scale,
                'blur_scale' => $this->themeSettings->blur_scale,
                'font_family' => $this->themeSettings->font_family,
                'font_scale' => $this->themeSettings->font_scale,
                'logo_light' => $this->themeSettings->logo_light,
                'logo_dark' => $this->themeSettings->logo_dark,
                'favicon' => $this->themeSettings->favicon,
            ];
        });

        return $themeTokens;
    }
}
