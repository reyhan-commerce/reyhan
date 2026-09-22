<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.loyalty_rate_amount_per_point', 10000);
        $this->migrator->add('general.loyalty_point_redemption_value', 500);
        $this->migrator->add('general.loyalty_signup_bonus', 50);
    }
};
