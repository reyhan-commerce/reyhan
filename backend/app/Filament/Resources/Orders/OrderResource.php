<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Morilog\Jalali\Jalalian;
use UnitEnum;
use Zvizvi\FilamentColumnFilters\Filters\ColumnFilter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $navigationLabel = 'سفارشات';

    protected static ?string $modelLabel = 'سفارش';

    protected static ?string $pluralModelLabel = 'سفارشات';

    protected static string|UnitEnum|null $navigationGroup = 'سفارشات و مالی';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('وضعیت و مدیریت سفارش')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('وضعیت فعلی سفارش')
                            ->options(array_combine(
                                array_map(fn (OrderStatus $s): string => $s->value, OrderStatus::cases()),
                                array_map(fn (OrderStatus $s): string => $s->label(), OrderStatus::cases())
                            ))
                            ->required(),

                        Select::make('shipping_method')
                            ->label('روش ارسال مرسوله')
                            ->options(array_combine(
                                array_map(fn (ShippingMethod $m): string => $m->value, ShippingMethod::cases()),
                                array_map(fn (ShippingMethod $m): string => $m->label(), ShippingMethod::cases())
                            ))
                            ->required(),

                        Textarea::make('notes')
                            ->label('یادداشت‌های داخلی سفارش')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('شناسه و وضعیت کلی سفارش')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('شماره سفارش')
                            ->copyable()
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedTicket),

                        TextEntry::make('status')
                            ->label('وضعیت سفارش')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                            ->color(fn (OrderStatus $state): string => $state->color()),

                        TextEntry::make('shipping_method')
                            ->label('روش ارسال')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn (?ShippingMethod $state): string => $state?->label() ?? 'نامشخص'),

                        TextEntry::make('created_at')
                            ->label('زمان ثبت سفارش')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('مشخصات حساب خریدار')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('نام و نام خانوادگی خریدار')
                                    ->default(fn (Order $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : 'کاربر مهمان')
                                    ->weight('bold'),

                                TextEntry::make('user.mobile')
                                    ->label('تلفن همراه خریدار')
                                    ->copyable()
                                    ->default('-'),

                                TextEntry::make('user.email')
                                    ->label('پست الکترونیکی خریدار')
                                    ->default('-'),
                            ]),

                        Section::make('مشخصات و آدرس گیرنده مرسوله')
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('shipping_address.recipient_name')
                                    ->label('نام تحویل‌گیرنده')
                                    ->default('-'),

                                TextEntry::make('shipping_address.mobile')
                                    ->label('تلفن تحویل‌گیرنده')
                                    ->copyable()
                                    ->default('-'),

                                TextEntry::make('shipping_address.province_name')
                                    ->label('استان')
                                    ->default('-'),

                                TextEntry::make('shipping_address.city_name')
                                    ->label('شهر')
                                    ->default('-'),

                                TextEntry::make('shipping_address.postal_code')
                                    ->label('کد پستی')
                                    ->copyable()
                                    ->default('-')
                                    ->columnSpanFull(),

                                TextEntry::make('shipping_address.address')
                                    ->label('نشانی دقیق پستی')
                                    ->columnSpanFull()
                                    ->default('-'),
                            ]),
                    ]),

                Section::make('اقلام سفارش داده شده')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('نام کالا')
                                    ->weight('bold')
                                    ->columnSpan(2),

                                TextEntry::make('variant_title')
                                    ->label('مشخصات / تنوع')
                                    ->default('ساده'),

                                TextEntry::make('quantity')
                                    ->label('تعداد')
                                    ->suffix(' عدد'),

                                TextEntry::make('final_price')
                                    ->label('مبلغ سطر')
                                    ->weight('bold')
                                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),
                            ]),
                    ]),

                Section::make('خلاصه فاکتور و مبالغ پرداختی')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('items_subtotal')
                            ->label('جمع کل اقلام')
                            ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),

                        TextEntry::make('coupon_discount')
                            ->label('تخفیف کوپن')
                            ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),

                        TextEntry::make('shipping_fee')
                            ->label('هزینه ارسال')
                            ->formatStateUsing(fn (int $state): string => $state === 0 ? 'رایگان' : number_format((int) ($state / 10)).' تومان'),

                        TextEntry::make('final_payable')
                            ->label('مبلغ نهایی فاکتور')
                            ->weight('bold')
                            ->color('success')
                            ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('شماره سفارش')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->columnFilter(ColumnFilter::search())
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('خریدار')
                    ->state(fn (Order $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) ?: 'کاربر بدون نام' : 'کاربر مهمان')
                    ->description(fn (Order $record): string => $record->user !== null ? $record->user->mobile : ($record->shipping_address['mobile'] ?? ''))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color())
                    ->columnFilter(ColumnFilter::select())
                    ->sortable(),

                TextColumn::make('shipping_method')
                    ->label('روش ارسال')
                    ->formatStateUsing(fn (?ShippingMethod $state): string => $state?->label() ?? '-')
                    ->toggleable(),

                TextColumn::make('final_payable')
                    ->label('مبلغ کل (تومان)')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)))
                    ->suffix(' تومان')
                    ->weight('medium')
                    ->columnFilter(ColumnFilter::range())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('زمان ثبت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->columnFilter(ColumnFilter::date())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('هنوز سفارشی ثبت نشده است')
            ->emptyStateDescription('هنگامی که مشتریان در فروشگاه سفارشی ثبت کنند، اطلاعات سفارش و اقلام آن در این جدول نمایش داده می‌شود.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('status')
                    ->label('فیلتر بر اساس وضعیت')
                    ->options(array_combine(
                        array_map(fn (OrderStatus $s): string => $s->value, OrderStatus::cases()),
                        array_map(fn (OrderStatus $s): string => $s->label(), OrderStatus::cases())
                    )),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('markAsShipped')
                    ->label('ثبت ارسال')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Processing || $record->status === OrderStatus::PendingPayment)

                    ->form([
                        TextInput::make('tracking_code')
                            ->label('کد رهگیری مرسوله پستی')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $notes = $record->notes ? $record->notes."\n" : '';
                        $notes .= 'کد رهگیری پستی: '.$data['tracking_code'];

                        $record->update([
                            'status' => OrderStatus::Shipped,
                            'shipped_at' => now(),
                            'notes' => $notes,
                        ]);

                        Notification::make()
                            ->title('سفارش به وضعیت ارسال شده تغییر یافت')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->socket(channel: 'orders', event: 'OrderCreated')
            ->socket(channel: 'orders', event: 'OrderUpdated');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
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
