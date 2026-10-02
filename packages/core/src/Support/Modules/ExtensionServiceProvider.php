<?php

declare(strict_types=1);

namespace Reyhan\Core\Support\Modules;

use Illuminate\Support\ServiceProvider;

abstract class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Unique identifier for this extension.
     */
    abstract public function getExtensionId(): string;

    /**
     * Human-readable extension name.
     */
    abstract public function getExtensionName(): string;

    /**
     * Semantic version of this extension.
     */
    public function getExtensionVersion(): string
    {
        return '1.0.0';
    }

    /**
     * Register any extension-specific routes, bindings, or configuration.
     */
    public function register(): void
    {
        // Extension register lifecycle hook
    }

    /**
     * Bootstrap extension services, migrations, and event listeners.
     */
    public function boot(): void
    {
        // Extension boot lifecycle hook
    }
}
