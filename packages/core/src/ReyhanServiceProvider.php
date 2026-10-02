<?php

declare(strict_types=1);

namespace Reyhan\Core;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Reyhan\Core\Console\Commands\ReyhanDoctorCommand;
use Reyhan\Core\Console\Commands\ReyhanInstallCommand;
use Reyhan\Core\Console\Commands\ReyhanUpdateCommand;
use Reyhan\Core\Console\Commands\ReyhanVersionCommand;
use Reyhan\Core\Console\Commands\ShopPresetCommand;
use Reyhan\Core\Console\Commands\SystemUpdateCommand;
use Reyhan\Core\Support\Reyhan;

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
                'model' => \Reyhan\Core\Models\Admin::class,
            ]),
        ]);

        $this->app->singleton(Reyhan::class, fn () => new Reyhan());
        $this->app->singleton(\Reyhan\Core\Services\Cart\CartService::class);
        $this->app->singleton(\Reyhan\Core\Services\Inventory\StockReservationService::class);
        $this->app->singleton(\Reyhan\Core\Services\Pricing\PricingService::class);
        $this->app->singleton(\Reyhan\Core\Services\Checkout\CheckoutService::class);
        $this->app->singleton(\Reyhan\Core\Services\Accounting\LedgerService::class);

        // Aliases for DI and Facade resolution
        $this->app->alias(\Reyhan\Core\Services\Cart\CartService::class, 'reyhan.cart');
        $this->app->alias(\Reyhan\Core\Services\Inventory\StockReservationService::class, 'reyhan.inventory');
        $this->app->alias(\Reyhan\Core\Services\Pricing\PricingService::class, 'reyhan.pricing');
        $this->app->alias(\Reyhan\Core\Services\Checkout\CheckoutService::class, 'reyhan.checkout');
        $this->app->alias(\Reyhan\Core\Services\Accounting\LedgerService::class, 'reyhan.ledger');

        $this->app->register(Providers\AppServiceProvider::class);
        $this->app->register(Providers\SmsServiceProvider::class);
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

        // Factory name resolver for Core models
        \Illuminate\Database\Eloquent\Factories\Factory::guessFactoryNamesUsing(function (string $modelName) {
            if (str_starts_with($modelName, 'Reyhan\\Core\\Models\\')) {
                return 'Reyhan\\Core\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
            return 'Database\\Factories\\'.class_basename($modelName).'Factory';
        });

        // 5. Console Commands & Publishing
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\CancelExpiredPendingOrdersCommand::class,
                Console\Commands\ExportMoadianInvoicesCommand::class,
                Console\Commands\MakePaymentDriverCommand::class,
                Console\Commands\MakePluginCommand::class,
                Console\Commands\MakeShippingDriverCommand::class,
                Console\Commands\RecoverAbandonedCartsCommand::class,
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
