<?php

declare(strict_types=1);

namespace App\Providers;

use App\Features\ShopFeature;
use App\Http\Controllers\Api\V1\AppSettingController;
use App\Models\Admin;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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
        // Grant all permissions unconditionally to super admins
        Gate::before(function ($user, string $ability): ?bool {
            if ($user instanceof Admin && ($user->hasRole('super_admin') || $user->hasRole('SuperAdmin'))) {
                return true;
            }

            return null;
        });

        // Backup management abilities
        foreach (['view-backups', 'create-backup', 'download-backup', 'delete-backup'] as $ability) {
            Gate::define($ability, function ($user) use ($ability): bool {
                if (! $user instanceof Admin) {
                    return false;
                }

                try {
                    return $user->hasPermissionTo($ability, 'admin');
                } catch (PermissionDoesNotExist) {
                    return false;
                }
            });
        }

        // Invalidate public settings cache and refresh dynamic driver state on settings save
        Event::listen(SettingsSaved::class, function (SettingsSaved $event) {
            Cache::forget(AppSettingController::CACHE_KEY);
            Cache::forget(AppSettingController::THEME_CACHE_KEY);

            if ($this->app->bound(SmsManager::class)) {
                $this->app->make(SmsManager::class)->forgetDrivers();
            }
        });

        // Register default Pennant feature flags
        foreach (ShopFeature::names() as $feature) {
            Feature::define($feature, fn () => true);
        }
    }
}
