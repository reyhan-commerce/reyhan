<?php

declare(strict_types=1);

namespace Reyhan\Core\Providers;

use Reyhan\Core\Events\Catalog\ProductRestockedEvent;
use Reyhan\Core\Features\ShopFeature;
use Reyhan\Core\Http\Controllers\Api\V1\AppSettingController;
use Reyhan\Core\Listeners\Catalog\SendProductRestockAlertsListener;
use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Observers\CategoryObserver;
use Reyhan\Core\Rules\CardNumberRule;
use Reyhan\Core\Rules\CompanyNationalIdRule;
use Reyhan\Core\Rules\IranianMobileRule;
use Reyhan\Core\Rules\IranianPhoneRule;
use Reyhan\Core\Rules\NationalCodeRule;
use Reyhan\Core\Rules\NoPersianRule;
use Reyhan\Core\Rules\PersianTextRule;
use Reyhan\Core\Rules\PostalCodeRule;
use Reyhan\Core\Rules\ShebaRule;
use Reyhan\Core\Services\Sms\SmsManager;
use Reyhan\Core\Support\Modules\ModuleManager;
use BokshornIt\FilamentActivityTimeline\Policies\ActivityPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;
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
        ModuleManager::registerDiscoveredExtensions($this->app);
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

        // Register Iranian custom validator string rules
        $this->registerIranianValidators();
    }

    /**
     * Register zero-dependency Iranian validation rule aliases.
     */
    protected function registerIranianValidators(): void
    {
        $rules = [
            'ir_mobile' => new IranianMobileRule,
            'ir_phone' => new IranianPhoneRule,
            'ir_national_code' => new NationalCodeRule,
            'ir_company_national_id' => new CompanyNationalIdRule,
            'ir_sheba' => new ShebaRule,
            'ir_postal_code' => new PostalCodeRule,
            'ir_bank_card' => new CardNumberRule,
            'ir_bank_card_number' => new CardNumberRule,
            'persian_text' => new PersianTextRule,
            'persian_alphabet' => new PersianTextRule,
            'no_persian' => new NoPersianRule,
        ];

        foreach ($rules as $name => $rule) {
            \Illuminate\Support\Facades\Validator::extend(
                $name,
                function (string $attribute, mixed $value, array $parameters, Validator $validator) use ($rule): bool {
                    $failed = false;
                    $rule->validate($attribute, $value, function ($message) use (&$failed): void {
                        $failed = true;
                    });

                    return ! $failed;
                }
            );
        }
    }
}
