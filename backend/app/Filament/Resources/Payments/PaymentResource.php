<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'تراکنش‌های بانکی';

    protected static ?string $modelLabel = 'تراکنش';

    protected static ?string $pluralModelLabel = 'تراکنش‌های بانکی';

    protected static string|UnitEnum|null $navigationGroup = 'سفارشات و مالی';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('جزئیات تراکنش')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('وضعیت تراکنش')
                            ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => $status->label()])->all())
                            ->required(),

                        TextInput::make('amount')
                            ->label('مبلغ (ریال)')
                            ->numeric()
                            ->required(),

                        TextInput::make('tracking_code')
                            ->label('کد پیگیری شاپرک / مرجع')
                            ->maxLength(100),

                        TextInput::make('card_pan')
                            ->label('شماره کارت پرداخت‌کننده')
                            ->maxLength(50),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('شماره سفارش')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.mobile')
                    ->label('موبایل پرداخت‌کننده')
                    ->searchable(),

                TextColumn::make('gateway')
                    ->label('درگاه پرداخت')
                    ->formatStateUsing(fn (?PaymentGateway $state): string => match ($state) {
                        PaymentGateway::Zarinpal => 'زرین‌پال',
                        PaymentGateway::Saman => 'بانک سامان',
                        PaymentGateway::Mellat => 'بانک ملت',
                        PaymentGateway::Sandbox => 'سندباکس (تست)',
                        default => 'درگاه بانکی',
                    })
                    ->badge()
                    ->color('primary'),

                TextColumn::make('amount')
                    ->label('مبلغ (تومان)')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)))
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('tracking_code')
                    ->label('کد پیگیری')
                    ->searchable()
                    ->copyable()
                    ->default('-'),

                TextColumn::make('card_pan')
                    ->label('شماره کارت')
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label('زمان تراکنش')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('فیلتر بر اساس وضعیت')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => $status->label()])->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
