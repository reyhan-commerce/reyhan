<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Referrals;

use Reyhan\Core\Enums\ReferralStatus;
use Reyhan\Core\Filament\Resources\Referrals\Pages\ListReferrals;
use Reyhan\Core\Models\Referral;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'معرفی دوستان (Referral)';

    protected static ?string $modelLabel = 'معرفی دوست';

    protected static ?string $pluralModelLabel = 'برنامه معرفی دوستان';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referrer.full_name')
                    ->label('کاربر معرف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Referral $record): string => ($record->referrer?->mobile ?? '—').' (کد: '.($record->referrer?->referral_code ?? '—').')'),

                TextColumn::make('referred.full_name')
                    ->label('کاربر دعوت‌شده')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Referral $record): string => $record->referred?->mobile ?? '—'),

                TextColumn::make('order.order_number')
                    ->label('شماره سفارش اول')
                    ->badge()
                    ->color('info')
                    ->default('—'),

                TextColumn::make('reward_amount')
                    ->label('مبلغ پاداش')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge(),

                TextColumn::make('completed_at')
                    ->label('تاریخ تکمیل')
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ دعوت')
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(ReferralStatus::options()),
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
            'index' => ListReferrals::route('/'),
        ];
    }
}
