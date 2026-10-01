<?php

declare(strict_types=1);

namespace Reyhan\Core;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\ReyhanDoctorCommand;
use App\Console\Commands\ReyhanInstallCommand;
use App\Console\Commands\ReyhanUpdateCommand;
use App\Console\Commands\ReyhanVersionCommand;
use App\Console\Commands\ShopPresetCommand;
use App\Console\Commands\SystemUpdateCommand;
use App\Support\Reyhan;

class ReyhanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/reyhan.php', 'reyhan');

        config([
            'auth.guards.admin' => config('auth.guards.admin', [
                'driver' => 'session',
                'provider' => 'admins',
            ]),
            'auth.providers.admins' => config('auth.providers.admins', [
                'driver' => 'eloquent',
                'model' => \App\Models\Admin::class,
            ]),
        ]);

        $this->app->singleton(Reyhan::class, fn () => new Reyhan());
    }

    public function boot(): void
    {
        // 1. Load Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // 2. Load Package Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reyhan');

        // 3. Load Translations
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');

        // 4. Load API Routes
        $this->loadRoutes();

        // 5. Console Commands & Publishing
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReyhanDoctorCommand::class,
                ReyhanInstallCommand::class,
                ReyhanUpdateCommand::class,
                ReyhanVersionCommand::class,
                ShopPresetCommand::class,
                SystemUpdateCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/reyhan.php' => config_path('reyhan.php'),
            ], 'reyhan-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/reyhan'),
            ], 'reyhan-views');
        }
    }

    protected function loadRoutes(): void
    {
        if (config('reyhan.routes.api_enabled', true) && file_exists(__DIR__.'/../routes/api/v1.php')) {
            Route::prefix('api/v1')
                ->middleware(['api'])
                ->group(__DIR__.'/../routes/api/v1.php');
        }

        if (file_exists(__DIR__.'/../routes/channels.php')) {
            require __DIR__.'/../routes/channels.php';
        }
    }
}
