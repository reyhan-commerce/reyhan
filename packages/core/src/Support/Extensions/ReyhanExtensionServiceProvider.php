<?php

declare(strict_types=1);

namespace Reyhan\Core\Support\Extensions;

use Closure;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Reyhan\Core\Pipelines\Cart\CartCalculationPipeline;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;
use Reyhan\Core\Services\Payment\PaymentManager;
use Reyhan\Core\Support\Reyhan;

abstract class ReyhanExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register a custom payment gateway driver into the Reyhan PaymentManager.
     *
     * @param  string  $name  e.g. 'pasargad', 'asan_pardakht'
     * @param  Closure|class-string  $driver
     */
    protected function registerPaymentDriver(string $name, Closure|string $driver): void
    {
        $this->app->afterResolving(PaymentManager::class, function (PaymentManager $manager) use ($name, $driver): void {
            if (is_string($driver) && class_exists($driver)) {
                $manager->extend($name, fn () => app($driver));
            } elseif ($driver instanceof Closure) {
                $manager->extend($name, $driver);
            }
        });
    }

    /**
     * Hook a custom pipe into the OrderCreationPipeline.
     *
     * @param  class-string  $pipe
     */
    protected function registerCheckoutPipe(string $pipe, bool $prepend = false): void
    {
        if ($prepend) {
            OrderCreationPipeline::prependPipe($pipe);
        } else {
            OrderCreationPipeline::appendPipe($pipe);
        }
    }

    /**
     * Hook a custom pipe into the CartCalculationPipeline.
     *
     * @param  class-string  $pipe
     */
    protected function registerPricingPipe(string $pipe, bool $prepend = false): void
    {
        if ($prepend) {
            CartCalculationPipeline::prependPipe($pipe);
        } else {
            CartCalculationPipeline::appendPipe($pipe);
        }
    }

    /**
     * Swap a core Eloquent model with a custom extension model.
     *
     * @param  string  $alias  e.g. 'product', 'order', 'cart'
     * @param  class-string<Model>  $concrete
     */
    protected function swapModel(string $alias, string $concrete): void
    {
        Reyhan::useModel($alias, $concrete);
    }

    /**
     * Register custom Filament admin panel resources.
     *
     * @param  array<int, class-string>  $resources
     */
    protected function registerAdminResources(array $resources): void
    {
        if (class_exists(Panel::class)) {
            Panel::configureUsing(function (Panel $panel) use ($resources): void {
                if ($panel->getId() === 'admin') {
                    $panel->resources($resources);
                }
            });
        }
    }

    /**
     * Load extension API routes with standardized prefix and middleware.
     */
    protected function loadExtensionApiRoutes(string $path, string $prefix = 'v1'): void
    {
        if (file_exists($path)) {
            Route::prefix("api/{$prefix}")
                ->middleware(['api'])
                ->group($path);
        }
    }

    /**
     * Load extension migrations.
     */
    protected function loadExtensionMigrations(string $path): void
    {
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Load extension blade views with namespace.
     */
    protected function loadExtensionViews(string $path, string $namespace): void
    {
        if (is_dir($path)) {
            $this->loadViewsFrom($path, $namespace);
        }
    }

    /**
     * Load extension JSON translations.
     */
    protected function loadExtensionTranslations(string $path): void
    {
        if (is_dir($path)) {
            $this->loadJsonTranslationsFrom($path);
        }
    }
}
