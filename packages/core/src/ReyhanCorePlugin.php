<?php

declare(strict_types=1);

namespace Reyhan\Core;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class ReyhanCorePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'reyhan-core';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(in: __DIR__.'/Filament/Resources', for: 'App\\Filament\\Resources')
            ->discoverPages(in: __DIR__.'/Filament/Pages', for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: __DIR__.'/Filament/Widgets', for: 'App\\Filament\\Widgets');
    }

    public function boot(Panel $panel): void
    {
        // Core boot hooks
    }
}
