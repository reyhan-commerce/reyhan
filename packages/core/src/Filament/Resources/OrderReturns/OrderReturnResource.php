<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\OrderReturns;

use Reyhan\Core\Enums\OrderReturnStatus;
use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Filament\Resources\OrderReturns\Pages\ListOrderReturns;
use Reyhan\Core\Filament\Resources\OrderReturns\Pages\ViewOrderReturn;
use Reyhan\Core\Models\OrderReturn;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Wallet\WalletService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class OrderReturnResource extends Resource
{
    protected static ?string $model = OrderReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'مرجوعی کالا (RMA)';

    protected static ?string $modelLabel = 'درخواست مرجوعی';

    protected static ?string $pluralModelLabel = 'درخواست‌های مرجوعی کالا';

    protected static string|UnitEnum|null $navigationGroup = 'سفارشات و مالی';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = OrderReturn::query()->where('status', OrderReturnStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات درخواست مرجوعی')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('return_number')
                            ->label('شماره درخواست RMA')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('order.order_number')
                            ->label('شماره سفارش')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge()
                            ->formatStateUsing(fn (OrderReturnStatus $state): string => $state->label())
                            ->color(fn (OrderReturnStatus $state): string => $state->color()),

                        TextEntry::make('user.name')
                            ->label('نام مشتری')
                            ->default(fn (OrderReturn $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : '-'),

                        TextEntry::make('user.mobile')
                            ->label('شماره موبایل خریدار')
                            ->default('-'),

                        TextEntry::make('refund_amount')
                            ->label('مبلغ استرداد')
                            ->weight('black')
                            ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),

                        TextEntry::make('reason')
                            ->label('علت مرجوعی')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('توضیحات تکمیلی مشتری')
                            ->columnSpanFull()
                            ->default('-'),

                        TextEntry::make('admin_notes')
                            ->label('یادداشت مدیر مالی/انبار')
                            ->columnSpanFull()
                            ->default('-'),
                    ]),

                Section::make('اقلام مرجوعی')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('orderItem.product_name')
                                    ->label('نام کالا')
                                    ->weight('bold')
                                    ->columnSpan(2),

                                TextEntry::make('quantity')
                                    ->label('تعداد')
                                    ->suffix(' عدد'),

                                TextEntry::make('price')
                                    ->label('مبلغ')
                                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->label('شماره RMA')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('order.order_number')
                    ->label('سفارش')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('خریدار')
                    ->state(fn (OrderReturn $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : '-'),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge(),

                TextColumn::make('refund_amount')
                    ->label('مبلغ (تومان)')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان')
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('فیلتر وضعیت')
                    ->options(OrderReturnStatus::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('تأیید اولیه')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (OrderReturn $record): bool => $record->status === OrderReturnStatus::Pending)
                    ->requiresConfirmation()
                    ->modalHeading('تأیید درخواست مرجوعی کالا')
                    ->modalDescription('آیا با مرجوعی این کالا موافقت می‌کنید؟ پس از تأیید، مشتری می‌تواند کالا را به آدرس انبار ارسال نماید.')
                    ->action(function (OrderReturn $record): void {
                        $record->update([
                            'status' => OrderReturnStatus::Approved,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('درخواست مرجوعی تأیید شد')
                            ->success()
                            ->send();
                    }),

                Action::make('receive')
                    ->label('دریافت در انبار')
                    ->icon(Heroicon::OutlinedInbox)
                    ->color('info')
                    ->visible(fn (OrderReturn $record): bool => $record->status === OrderReturnStatus::Approved)
                    ->requiresConfirmation()
                    ->action(function (OrderReturn $record): void {
                        $record->update([
                            'status' => OrderReturnStatus::ItemReceived,
                        ]);

                        Notification::make()
                            ->title('وصول کالا در انبار ثبت شد')
                            ->success()
                            ->send();
                    }),

                Action::make('refund')
                    ->label('استرداد به کیف پول')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('primary')
                    ->visible(fn (OrderReturn $record): bool => $record->status === OrderReturnStatus::ItemReceived)
                    ->requiresConfirmation()
                    ->modalHeading('استرداد مبلغ به کیف پول مشتری')
                    ->modalDescription(fn (OrderReturn $record): string => 'مبلغ '.number_format((int) ($record->refund_amount / 10)).' تومان به کیف پول مشتری واریز خواهد شد.')
                    ->action(function (OrderReturn $record): void {
                        $user = $record->user;
                        if (! $user instanceof User) {
                            Notification::make()
                                ->title('کاربر مرتبط با این درخواست یافت نشد')
                                ->danger()
                                ->send();

                            return;
                        }

                        /** @var WalletService $walletService */
                        $walletService = app(WalletService::class);
                        $walletService->deposit(
                            user: $user,
                            amountRial: $record->refund_amount,
                            description: "استرداد وجه بابت مرجوعی کالا (RMA: {$record->return_number})",
                            type: WalletTransactionType::Refund,
                            orderId: $record->order_id,
                            meta: ['return_number' => $record->return_number]
                        );

                        $record->update([
                            'status' => OrderReturnStatus::Refunded,
                        ]);

                        Notification::make()
                            ->title('مبلغ مرجوعی با موفقیت به کیف پول کاربر شارژ شد')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('رد درخواست')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (OrderReturn $record): bool => in_array($record->status, [OrderReturnStatus::Pending, OrderReturnStatus::Approved], true))
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('علت رد درخواست مرجوعی')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (OrderReturn $record, array $data): void {
                        $record->update([
                            'status' => OrderReturnStatus::Rejected,
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('درخواست مرجوعی رد شد')
                            ->warning()
                            ->send();
                    }),
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
            'index' => ListOrderReturns::route('/'),
            'view' => ViewOrderReturn::route('/{record}'),
        ];
    }
}
