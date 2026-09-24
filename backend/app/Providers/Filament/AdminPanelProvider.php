<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->font('Vazirmatn')
            ->spa()
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('فروشگاه و کاتالوگ')->collapsed(),
                NavigationGroup::make('سفارشات و مالی')->collapsed(),
                NavigationGroup::make('مشتریان و بازخورد')->collapsed(),
                NavigationGroup::make('محتوا و اطلاع‌رسانی')->collapsed(),
                NavigationGroup::make('تنظیمات سیستم')->collapsed(),
                NavigationGroup::make('دسترسی و پرسنل')->collapsed(),
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('دسترسی و پرسنل')
                    ->navigationSort(1),
                FilamentSpatieLaravelBackupPlugin::make()
                    ->navigationGroup('تنظیمات سیستم')
                    ->navigationSort(10)
                    ->navigationIcon('heroicon-o-circle-stack')
                    ->navigationLabel('پشتیبان‌گیری')
                    ->usingQueueConnection('redis')
                    ->usingQueue('default')
                    ->timeout(300)
                    ->authorize(fn (): bool => auth('admin')->user()?->can('view-backups') ?? false),
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
