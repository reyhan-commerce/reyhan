<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Payments;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Filament\Resources\Payments\Pages\CreatePayment;
use Reyhan\Core\Filament\Resources\Payments\Pages\EditPayment;
use Reyhan\Core\Filament\Resources\Payments\Pages\ListPayments;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\User;
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

                TextColumn::make('user.name')
                    ->label('پرداخت‌کننده')
                    ->state(fn (Payment $record): string => $record->user instanceof User ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) ?: 'کاربر بدون نام' : 'کاربر مهمان')
                    ->description(fn (Payment $record): string => $record->user instanceof User ? ($record->user->mobile ?? '') : '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('gateway')
                    ->label('درگاه پرداخت')
                    ->formatStateUsing(fn (?PaymentGateway $state): string => $state?->label() ?? 'درگاه بانکی')
                    ->badge()
                    ->color(fn (?PaymentGateway $state): string => match ($state) {
                        PaymentGateway::Zarinpal => 'warning',
                        PaymentGateway::Saman => 'info',
                        PaymentGateway::Mellat => 'danger',
                        PaymentGateway::Sandbox => 'gray',
                        PaymentGateway::SnappPay => 'secondary',
                        PaymentGateway::CardToCard => 'primary',
                        PaymentGateway::Wallet => 'success',
                        default => 'primary',
                    }),

                TextColumn::make('amount')
                    ->label('مبلغ (تومان)')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)))
                    ->suffix(' تومان')
                    ->weight('medium')
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
            ->emptyStateHeading('هنوز تراکنشی ثبت نشده است')
            ->emptyStateDescription('هنگامی که کاربران به درگاه متصل شوند، وضعیت و جزئیات تراکنش‌های بانکی در اینجا ذخیره و نمایش داده می‌شود.')
            ->emptyStateIcon(Heroicon::OutlinedCreditCard)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('status')
                    ->label('فیلتر بر اساس وضعیت')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => $status->label()])->all()),

                SelectFilter::make('gateway')
                    ->label('فیلتر درگاه پرداخت')
                    ->options(
                        collect(PaymentGateway::cases())
                            ->mapWithKeys(fn (PaymentGateway $gateway): array => [$gateway->value => $gateway->label()])
                            ->all()
                    ),
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
