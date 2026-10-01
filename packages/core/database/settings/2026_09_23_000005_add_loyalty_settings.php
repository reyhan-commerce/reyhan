<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.loyalty_rate_amount_per_point')) {
            $this->migrator->add('general.loyalty_rate_amount_per_point', 10000);
        }
        if (! $this->migrator->exists('general.loyalty_point_redemption_value')) {
            $this->migrator->add('general.loyalty_point_redemption_value', 500);
        }
        if (! $this->migrator->exists('general.loyalty_signup_bonus')) {
            $this->migrator->add('general.loyalty_signup_bonus', 50);
        }
    }
};
