<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Catalog\ProductRestockedEvent;
use App\Features\ShopFeature;
use App\Http\Controllers\Api\V1\AppSettingController;
use App\Listeners\Catalog\SendProductRestockAlertsListener;
use App\Models\Admin;
use App\Models\Category;
use App\Observers\CategoryObserver;
use App\Services\Sms\SmsManager;
use BokshornIt\FilamentActivityTimeline\Policies\ActivityPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Spatie\Activitylog\Models\Activity;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
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
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Grant all permissions unconditionally to super admins
        Gate::before(function ($user, string $ability): ?bool {
            if ($user instanceof Admin && ($user->hasRole('super_admin') || $user->hasRole('SuperAdmin'))) {
                return true;
            }

            return null;
        });

        // Backup and health monitoring management abilities
        foreach (['view-backups', 'create-backup', 'download-backup', 'delete-backup', 'view-health'] as $ability) {
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

        // Scramble API documentation access gate
        Gate::define('viewApiDocs', function (?Admin $admin): bool {
            if (app()->environment('local', 'testing', 'staging')) {
                return true;
            }

            return $admin !== null;
        });

        // Register system health checks
        Health::checks([
            DatabaseCheck::new(),
            RedisCheck::new(),
            UsedDiskSpaceCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            OptimizedAppCheck::new(),
        ]);

        // Invalidate public settings cache and refresh dynamic driver state on settings save
        Event::listen(SettingsSaved::class, function (SettingsSaved $event) {
            Cache::forget(AppSettingController::CACHE_KEY);
            Cache::forget(AppSettingController::THEME_CACHE_KEY);

            if ($this->app->bound(SmsManager::class)) {
                $this->app->make(SmsManager::class)->forgetDrivers();
            }
        });

        // Register catalog restock domain event listener
        Event::listen(ProductRestockedEvent::class, SendProductRestockAlertsListener::class);

        // Register Category observer for automatic cache invalidation
        Category::observe(CategoryObserver::class);

        // Register default Pennant feature flags
        foreach (ShopFeature::names() as $feature) {
            Feature::define($feature, fn () => true);
        }
    }
}
