<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\Api\V1\AppSettingController;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Invalidate public settings cache and refresh dynamic driver state on settings save
        Event::listen(SettingsSaved::class, function (SettingsSaved $event) {
            Cache::forget(AppSettingController::CACHE_KEY);

            if ($this->app->bound(SmsManager::class)) {
                $this->app->make(SmsManager::class)->forgetDrivers();
            }
        });
    }
}
