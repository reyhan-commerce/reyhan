<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.primary_color', '#e11d48');
        $this->migrator->add('theme.secondary_color', '#0284c7');
        $this->migrator->add('theme.border_radius', '0.25rem');
        $this->migrator->add('theme.spacing_scale', 'normal');
        $this->migrator->add('theme.shadow_scale', 'md');
        $this->migrator->add('theme.blur_scale', 'md');
        $this->migrator->add('theme.font_family', 'Vazirmatn');
        $this->migrator->add('theme.font_scale', '1rem');
        $this->migrator->add('theme.logo_light', null);
        $this->migrator->add('theme.logo_dark', null);
        $this->migrator->add('theme.favicon', null);
    }
};
