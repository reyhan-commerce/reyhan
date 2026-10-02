<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\LoyaltyTransactions;

use Reyhan\Core\Filament\Resources\LoyaltyTransactions\Pages\ListLoyaltyTransactions;
use Reyhan\Core\Models\LoyaltyTransaction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class LoyaltyTransactionResource extends Resource
{
    protected static ?string $model = LoyaltyTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'تراکنش‌های باشگاه مشتریان';

    protected static ?string $modelLabel = 'تراکنش امتیاز';

    protected static ?string $pluralModelLabel = 'باشگاه مشتریان و امتیازات';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('مشتری')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (LoyaltyTransaction $record): string => $record->user ? (string) $record->user->mobile : '—'),

                TextColumn::make('points')
                    ->label('امتیاز')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '').$state.' امتیاز')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('نوع پاداش / خرج')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'signup_bonus' => 'هدیه ثبت‌نام',
                        'order_reward' => 'پاداش خرید',
                        'review_bonus' => 'پاداش ثبت نظر',
                        'coupon_redemption' => 'تبدیل به کوپن',
                        'manual_adjustment' => 'تنظیم دستی مدیر',
                        default => $state,
                    })
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('شرح')
                    ->wrap()
                    ->limit(50),

                TextColumn::make('reference_id')
                    ->label('شماره ارجاع')
                    ->placeholder('-')
                    ->fontFamily('mono'),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع تراکنش')
                    ->options([
                        'signup_bonus' => 'هدیه ثبت‌نام',
                        'order_reward' => 'پاداش خرید',
                        'review_bonus' => 'پاداش ثبت نظر',
                        'coupon_redemption' => 'تبدیل به کوپن',
                        'manual_adjustment' => 'تنظیم دستی مدیر',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyTransactions::route('/'),
        ];
    }
}
