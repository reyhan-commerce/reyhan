<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Orders;

use Reyhan\Core\Actions\Orders\ApproveCardTransferReceiptAction;
use Reyhan\Core\Actions\Orders\RejectCardTransferReceiptAction;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ShippingMethod;
use Reyhan\Core\Filament\Resources\Orders\Pages\CreateOrder;
use Reyhan\Core\Filament\Resources\Orders\Pages\EditOrder;
use Reyhan\Core\Filament\Resources\Orders\Pages\ListOrders;
use Reyhan\Core\Filament\Resources\Orders\Pages\ViewOrder;
use Reyhan\Core\Http\Controllers\Api\V1\OrderInvoiceController;
use Reyhan\Core\Http\Controllers\OrderShippingLabelController;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Notifications\Orders\OrderShippedNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as SystemNotification;
use Illuminate\Support\HtmlString;
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

                        Select::make('shipping_method_id')
                            ->label('روش ارسال مرسوله')
                            ->relationship('shippingMethod', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('tracking_code')
                            ->label('کد رهگیری مرسوله پستی / باربری')
                            ->maxLength(64),

                        TextInput::make('tracking_url')
                            ->label('لینک رهگیری آنلاین')
                            ->url()
                            ->maxLength(512),

                        TextInput::make('delivery_time_slot')
                            ->label('بازه زمانی تحویل سفارش')
                            ->maxLength(64),

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

                        TextEntry::make('shipping_method_title')
                            ->label('روش ارسال')
                            ->badge()
                            ->color('info')
                            ->default(fn (Order $record): string => $record->shippingMethod?->name ?? ($record->shipping_method instanceof ShippingMethod ? $record->shipping_method->title() : ($record->shipping_method ?? 'پست پیشتاز'))),

                        TextEntry::make('created_at')
                            ->label('زمان ثبت سفارش')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),
                    ]),

                Section::make('اطلاعات لجستیک، رهگیری و ارسال')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('tracking_code')
                            ->label('کد رهگیری مرسوله')
                            ->copyable()
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedTruck)
                            ->default('هنوز ثبت نشده'),

                        TextEntry::make('tracking_url')
                            ->label('لینک رهگیری مرسوله')
                            ->default('-')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab(),

                        TextEntry::make('delivery_time_slot')
                            ->label('بازه زمانی تحویل')
                            ->default('عادی (ارسال سراسری)'),

                        TextEntry::make('shipped_at')
                            ->label('تاریخ ارسال')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : 'ارسال نشده'),
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

                        TextEntry::make('wallet_paid_amount')
                            ->label('پرداخت از کیف پول')
                            ->formatStateUsing(fn (int $state): string => $state === 0 ? '۰' : number_format((int) ($state / 10)).' تومان')
                            ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),

                        TextEntry::make('final_payable')
                            ->label('مبلغ نهایی پرداختی')
                            ->weight('bold')
                            ->color('success')
                            ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),
                    ]),

                Section::make('درخواست فاکتور رسمی مالیاتی (حقوقی)')
                    ->visible(fn (Order $record): bool => (bool) $record->is_corporate_invoice)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('corporate_data.company_name')
                            ->label('نام شرکت / سازمان')
                            ->weight('bold'),

                        TextEntry::make('corporate_data.national_id')
                            ->label('شناسه ملی شرکت')
                            ->copyable(),

                        TextEntry::make('corporate_data.economic_code')
                            ->label('کد اقتصادی')
                            ->copyable()
                            ->default('-'),

                        TextEntry::make('corporate_data.registration_number')
                            ->label('شماره ثبت')
                            ->default('-'),

                        TextEntry::make('corporate_data.phone')
                            ->label('شماره تلفن شرکت')
                            ->default('-'),
                    ]),

                Section::make('رسید پرداخت آفلاین کارت‌به‌کارت')
                    ->visible(fn (Order $record): bool => $record->cardTransferReceipt !== null)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('cardTransferReceipt.tracking_number')
                            ->label('شماره پیگیری / ارجاع بانکی')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('cardTransferReceipt.source_card_number')
                            ->label('شماره کارت مبدا')
                            ->default('-'),

                        TextEntry::make('cardTransferReceipt.status')
                            ->label('وضعیت بررسی فیش')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'error',
                                default => 'warning',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'approved' => 'تایید شده توسط امور مالی',
                                'rejected' => 'رد شده',
                                default => 'در انتظار تایید امور مالی',
                            }),
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
                    ->state(fn (Order $record): string => $record->shippingMethod?->name ?? ($record->shipping_method instanceof ShippingMethod ? $record->shipping_method->title() : ($record->shipping_method ?? '-')))
                    ->toggleable(),

                TextColumn::make('tracking_code')
                    ->label('کد رهگیری')
                    ->copyable()
                    ->placeholder('-')
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
                Action::make('print_invoice')
                    ->label('چاپ فاکتور')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->color('gray')
                    ->url(fn (Order $record): string => OrderInvoiceController::generateAdminInvoiceUrl($record))
                    ->openUrlInNewTab(),
                Action::make('print_tax_invoice')
                    ->label('فاکتور رسمی (ماده ۱۹)')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('info')
                    ->visible(fn (Order $record): bool => (bool) $record->is_corporate_invoice)
                    ->url(fn (Order $record): string => OrderInvoiceController::generateAdminInvoiceUrl($record).'&type=tax')
                    ->openUrlInNewTab(),
                Action::make('verifyCardTransfer')
                    ->label('بررسی فیش واریزی')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->cardTransferReceipt !== null && $record->cardTransferReceipt->status === 'pending')
                    ->form([
                        Placeholder::make('receipt_info')
                            ->label('اطلاعات فیش بانکی')
                            ->content(function (Order $record): HtmlString {
                                $receipt = $record->cardTransferReceipt;
                                if (! $receipt) {
                                    return new HtmlString('-');
                                }
                                $amount = number_format((int) ($receipt->amount / 10)).' تومان';
                                $tracking = e($receipt->tracking_number);
                                $card = e($receipt->source_card_number ?? 'ثبت نشده');

                                return new HtmlString("
                                    <div class='space-y-1 text-sm'>
                                        <div><strong>مبلغ واریزی:</strong> {$amount}</div>
                                        <div><strong>کد رهگیری / ارجاع:</strong> {$tracking}</div>
                                        <div><strong>شماره کارت مبدا:</strong> {$card}</div>
                                    </div>
                                ");
                            }),
                        Radio::make('decision')
                            ->label('تصمیم مدیر مالی')
                            ->options([
                                'approve' => 'تأیید فیش واریزی و تغییر سفارش به در حال پردازش',
                                'reject' => 'رد فیش واریزی (نامعتبر یا عدم تطابق مبلغ)',
                            ])
                            ->default('approve')
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('یادداشت مدیر مالی')
                            ->placeholder('توضیحات در صورت رد فیش یا شماره سند حسابداری...')
                            ->rows(2),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $receipt = $record->cardTransferReceipt;
                        if (! $receipt) {
                            return;
                        }

                        $isApproved = ($data['decision'] ?? '') === 'approve';
                        $notes = trim((string) ($data['admin_notes'] ?? ''));

                        if ($isApproved) {
                            app(ApproveCardTransferReceiptAction::class)
                                ->execute($record, $receipt, auth()->id(), $notes ?: null);

                            Notification::make()
                                ->title('فیش واریزی با موفقیت تأیید شد، موجودی انبار کسر گردید و سفارش به در حال پردازش تغییر یافت')
                                ->success()
                                ->send();
                        } else {
                            app(RejectCardTransferReceiptAction::class)
                                ->execute($record, $receipt, auth()->id(), $notes ?: null);

                            Notification::make()
                                ->title('فیش واریزی رد شد')
                                ->warning()
                                ->send();
                        }
                    }),
                Action::make('print_shipping_label')
                    ->label('برچسب پستی')
                    ->icon(Heroicon::OutlinedTag)
                    ->color('gray')
                    ->url(fn (Order $record): string => OrderShippingLabelController::generateLabelUrl($record))
                    ->openUrlInNewTab(),
                EditAction::make(),
                Action::make('markAsShipped')
                    ->label('ثبت ارسال و رهگیری')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('success')
                    ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Processing, OrderStatus::PendingPayment], true))
                    ->form([
                        TextInput::make('tracking_code')
                            ->label('کد رهگیری مرسوله پستی / تیپاکس')
                            ->required()
                            ->maxLength(64)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                $clean = trim((string) $state);
                                if (strlen($clean) >= 20 && ctype_digit($clean)) {
                                    $set('tracking_url', 'https://tracking.post.ir/?id='.$clean);
                                }
                            }),

                        TextInput::make('tracking_url')
                            ->label('لینک سامانه رهگیری مرسوله')
                            ->url()
                            ->maxLength(512)
                            ->placeholder('https://tracking.post.ir/?id=...'),

                        Toggle::make('send_sms')
                            ->label('ارسال پیامک اطلاع‌رسانی با لینک رهگیری به شماره خریدار')
                            ->default(true),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $trackingCode = trim((string) $data['tracking_code']);
                        $trackingUrl = ! empty($data['tracking_url'])
                            ? trim((string) $data['tracking_url'])
                            : (strlen($trackingCode) >= 20 && ctype_digit($trackingCode) ? 'https://tracking.post.ir/?id='.$trackingCode : null);

                        $notes = $record->notes ? $record->notes."\n" : '';
                        $notes .= 'کد رهگیری پستی: '.$trackingCode;

                        $record->update([
                            'status' => OrderStatus::Shipped,
                            'tracking_code' => $trackingCode,
                            'tracking_url' => $trackingUrl,
                            'shipped_at' => now(),
                            'notes' => $notes,
                        ]);

                        if (! empty($data['send_sms'])) {
                            $mobile = $record->shipping_address['recipient_mobile'] ?? $record->user?->mobile;
                            if ($mobile) {
                                try {
                                    $recipient = $record->user ?? SystemNotification::route('sms', (string) $mobile);
                                    $recipient->notify(new OrderShippedNotification($record, $trackingCode, $trackingUrl));
                                } catch (\Throwable $e) {
                                    Log::warning("Failed to send tracking notification: {$e->getMessage()}");
                                }
                            }
                        }

                        Notification::make()
                            ->title('سفارش به وضعیت ارسال شده تغییر یافت و اطلاعات رهگیری ثبت شد')
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
