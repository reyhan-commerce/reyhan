<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Widgets;

use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Filament\Resources\Orders\OrderResource;
use Reyhan\Core\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Morilog\Jalali\Jalalian;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'آخرین سفارشات ثبت‌شده در سامانه';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(8)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('شماره سفارش')
                    ->weight('bold')
                    ->copyable()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),

                TextColumn::make('user.mobile')
                    ->label('خریدار')
                    ->description(fn (Order $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : 'مهمان'),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color()),

                TextColumn::make('final_payable')
                    ->label('مبلغ پرداختی')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),

                TextColumn::make('created_at')
                    ->label('زمان ثبت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),
            ])
            ->paginated(false)
            ->socket(channel: 'orders', event: 'OrderCreated')
            ->socket(channel: 'orders', event: 'OrderUpdated');
    }
}
