<?php

declare(strict_types=1);

namespace Reyhan\Core\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    public string $primary_color;

    public string $secondary_color;

    public string $border_radius;

    public string $spacing_scale;

    public string $shadow_scale;

    public string $blur_scale;

    public string $font_family;

    public string $font_scale;

    public ?string $logo_light;

    public ?string $logo_dark;

    public ?string $favicon;

    public static function group(): string
    {
        return 'theme';
    }
}
