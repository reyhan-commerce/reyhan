<?php

declare(strict_types=1);

namespace Reyhan\Core\Providers\Filament;

use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\User;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BokshornIt\FilamentActivityTimeline\ActivityTimelinePlugin;
use Filament\Http\Middleware\Authenticate;
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
use Marcusvbda\FilamentRealtimeDriver\FilamentRealtimeDriverPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Zvizvi\FilamentColumnFilters\FilamentColumnFiltersPlugin;
use Zvizvi\FilamentNotificationsTabs\FilamentNotificationsTabsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authGuard('admin')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->font('Vazirmatn')
            ->spa()
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'Reyhan\Core\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'Reyhan\Core\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'Reyhan\Core\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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
                FilamentSpatieLaravelHealthPlugin::make()
                    ->navigationGroup('تنظیمات سیستم')
                    ->navigationSort(11)
                    ->navigationIcon('heroicon-o-heart')
                    ->navigationLabel('سلامت سیستم و سرور')
                    ->authorize(fn (): bool => auth('admin')->user()?->can('view-health') ?? false),
                FilamentColumnFiltersPlugin::make(),
                FilamentNotificationsTabsPlugin::make(),
                FilamentRealtimeDriverPlugin::make()
                    ->socket()
                    ->databaseNotifications(),
                ActivityTimelinePlugin::make()
                    ->navigationGroup('تنظیمات سیستم')
                    ->navigationIcon('heroicon-o-clipboard-document-list')
                    ->navigationSort(12)
                    ->causerIcons([
                        Admin::class => 'heroicon-m-shield-check',
                        User::class => 'heroicon-m-user',
                    ])
                    ->systemCauserIcon('heroicon-m-cpu-chip')
                    ->events([
                        'paid' => ['icon' => 'heroicon-m-banknotes', 'color' => 'success'],
                        'shipped' => ['icon' => 'heroicon-m-truck', 'color' => 'info'],
                        'cancelled' => ['icon' => 'heroicon-m-x-circle', 'color' => 'danger'],
                        'refunded' => ['icon' => 'heroicon-m-arrow-path', 'color' => 'warning'],
                        'stock_changed' => ['icon' => 'heroicon-m-archive-box', 'color' => 'warning'],
                        'price_changed' => ['icon' => 'heroicon-m-currency-dollar', 'color' => 'primary'],
                    ]),
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
