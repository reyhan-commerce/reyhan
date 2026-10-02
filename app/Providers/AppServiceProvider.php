<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Userland application service bindings
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Userland application bootstrapping
    }
}
