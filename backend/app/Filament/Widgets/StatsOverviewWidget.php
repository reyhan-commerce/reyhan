<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenueRials = (int) Order::whereDate('created_at', today())
            ->whereIn('status', [OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered])
            ->sum('final_payable');
        $todayRevenueToman = (int) ($todayRevenueRials / 10);

        $pendingOrdersCount = Order::where('status', OrderStatus::Processing)->count();

        $todayNewUsersCount = User::whereDate('created_at', today())->count();
        $totalUsersCount = User::count();

        $lowStockVariantsCount = ProductVariant::where('stock', '<=', 5)->count();

        return [
            Stat::make('فروش امروز', number_format($todayRevenueToman).' تومان')
                ->description('مجموع درآمدهای موفق امروز')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('سفارشات آماده ارسال', (string) $pendingOrdersCount)
                ->description('سفارشات در وضعیت در حال پردازش')
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingOrdersCount > 0 ? 'warning' : 'primary'),

            Stat::make('کاربران سامانه', number_format($totalUsersCount).' نفر')
                ->description($todayNewUsersCount > 0 ? "+{$todayNewUsersCount} کاربر جدید امروز" : 'مشتریان ثبت‌نام شده')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('هشدار موجودی انبار', (string) $lowStockVariantsCount.' قلم کالا')
                ->description('تنوع‌های با موجودی ۵ یا کمتر')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockVariantsCount > 0 ? 'danger' : 'success'),
        ];
    }
}
