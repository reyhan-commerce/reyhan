<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\SupportTickets;

use Reyhan\Core\Enums\TicketDepartment;
use Reyhan\Core\Enums\TicketStatus;
use Reyhan\Core\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use Reyhan\Core\Filament\Resources\SupportTickets\Pages\ViewSupportTicket;
use Reyhan\Core\Models\SupportTicket;
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

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'تیکت‌های پشتیبانی';

    protected static ?string $modelLabel = 'تیکت پشتیبانی';

    protected static ?string $pluralModelLabel = 'تیکت‌های پشتیبانی';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = SupportTicket::query()->where('status', TicketStatus::Open)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات تیکت')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ticket_number')
                            ->label('شماره تیکت')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('subject')
                            ->label('موضوع تیکت')
                            ->weight('bold')
                            ->columnSpan(2),

                        TextEntry::make('user.name')
                            ->label('نام کاربر')
                            ->default(fn (SupportTicket $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : '-'),

                        TextEntry::make('user.mobile')
                            ->label('تلفن همراه')
                            ->default('-'),

                        TextEntry::make('order.order_number')
                            ->label('شماره سفارش مربوطه')
                            ->default('عمومی (بدون سفارش)'),

                        TextEntry::make('department')
                            ->label('دپارتمان')
                            ->badge(),

                        TextEntry::make('priority')
                            ->label('اولویت')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge(),
                    ]),

                Section::make('پیام‌ها و گفتگوی تیکت')
                    ->schema([
                        RepeatableEntry::make('messages')
                            ->label('')
                            ->schema([
                                TextEntry::make('author')
                                    ->label('فرستنده')
                                    ->weight('bold')
                                    ->state(fn ($record): string => $record->is_staff ? 'کارشناس پشتیبانی (پاسخ رسمی)' : ($record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : 'مشتری')),

                                TextEntry::make('created_at')
                                    ->label('زمان ارسال')
                                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),

                                TextEntry::make('message')
                                    ->label('متن پیام')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('شماره تیکت')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->state(fn (SupportTicket $record): string => $record->user ? trim(($record->user->first_name ?? '').' '.($record->user->last_name ?? '')) : '-'),

                TextColumn::make('department')
                    ->label('دپارتمان')
                    ->badge(),

                TextColumn::make('priority')
                    ->label('اولویت')
                    ->badge(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge(),

                TextColumn::make('last_reply_at')
                    ->label('آخرین پاسخ')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable(),
            ])
            ->defaultSort('last_reply_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(TicketStatus::options()),
                SelectFilter::make('department')
                    ->label('دپارتمان')
                    ->options(TicketDepartment::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reply')
                    ->label('پاسخ به تیکت')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('primary')
                    ->visible(fn (SupportTicket $record): bool => $record->status !== TicketStatus::Closed)
                    ->form([
                        Textarea::make('message')
                            ->label('متن پاسخ پشتیبانی')
                            ->required()
                            ->rows(4)
                            ->placeholder('پاسخ رسمی پشتیبانی به مشتری...'),
                    ])
                    ->action(function (SupportTicket $record, array $data): void {
                        $record->messages()->create([
                            'user_id' => auth()->id(),
                            'message' => $data['message'],
                            'is_staff' => true,
                        ]);

                        $record->update([
                            'status' => TicketStatus::Answered,
                            'last_reply_at' => now(),
                        ]);

                        Notification::make()
                            ->title('پاسخ پشتیبانی با موفقیت ثبت شد')
                            ->success()
                            ->send();
                    }),

                Action::make('close')
                    ->label('بستن تیکت')
                    ->icon(Heroicon::OutlinedLockClosed)
                    ->color('gray')
                    ->visible(fn (SupportTicket $record): bool => $record->status !== TicketStatus::Closed)
                    ->requiresConfirmation()
                    ->action(function (SupportTicket $record): void {
                        $record->update(['status' => TicketStatus::Closed]);
                        Notification::make()->title('تیکت بسته شد')->send();
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
            'index' => ListSupportTickets::route('/'),
            'view' => ViewSupportTicket::route('/{record}'),
        ];
    }
}
