<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Users;

use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Filament\Resources\Users\Pages\CreateUser;
use Reyhan\Core\Filament\Resources\Users\Pages\EditUser;
use Reyhan\Core\Filament\Resources\Users\Pages\ListUsers;
use Reyhan\Core\Filament\Resources\Users\Pages\ViewUser;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Wallet\WalletService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'مشتریان و کاربران';

    protected static ?string $modelLabel = 'مشتری';

    protected static ?string $pluralModelLabel = 'مشتریان';

    protected static string|UnitEnum|null $navigationGroup = 'مشتریان و بازخورد';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات فردی مشتری')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('نام')
                            ->maxLength(100),

                        TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->maxLength(100),

                        TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->tel()
                            ->required()
                            ->unique(User::class, 'mobile', ignoreRecord: true),

                        TextInput::make('national_code')
                            ->label('کد ملی ۱۰ رقمی')
                            ->maxLength(10)
                            ->unique(User::class, 'national_code', ignoreRecord: true),

                        TextInput::make('email')
                            ->label('ایمیل')
                            ->email()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('حساب کاربری فعال است')
                            ->default(true),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات هویتی مشتری')

                    ->columns(3)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('نام و نام خانوادگی')
                            ->weight('bold'),

                        TextEntry::make('mobile')
                            ->label('شماره موبایل')
                            ->copyable(),

                        TextEntry::make('national_code')
                            ->label('کد ملی')
                            ->default('-'),

                        TextEntry::make('email')
                            ->label('آدرس ایمیل')
                            ->default('-'),

                        IconEntry::make('is_active')
                            ->label('وضعیت حساب')
                            ->boolean(),

                        TextEntry::make('mobile_verified_at')
                            ->label('زمان تایید موبایل')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : 'تایید نشده')
                            ->badge()
                            ->color(fn (?string $state): string => $state ? 'success' : 'warning'),

                        TextEntry::make('created_at')
                            ->label('تاریخ عضویت')
                            ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('نام مشتری')
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold')
                    ->description(fn (User $record): string => $record->email ? $record->mobile.' • '.$record->email : $record->mobile),

                TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('تعداد سفارشات')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('wallet_balance')
                    ->label('کیف پول')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                IconColumn::make('mobile_verified_at')
                    ->label('تایید پیامکی')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedXMark)
                    ->state(fn (User $record): bool => $record->mobile_verified_at !== null),

                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('عضویت')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('هنوز کاربری ثبت نام نکرده است')
            ->emptyStateDescription('لیست خریداران و مشتریان ثبت‌نام شده در این بخش نمایش داده خواهد شد.')
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->filtersFormColumns(2)
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('وضعیت حساب')
                    ->placeholder('همه')
                    ->trueLabel('فقط حساب‌های فعال')
                    ->falseLabel('فقط حساب‌های مسدود'),

                TernaryFilter::make('mobile_verified_at')
                    ->label('تایید موبایل')
                    ->nullable()
                    ->placeholder('همه')
                    ->trueLabel('تایید شده')
                    ->falseLabel('تایید نشده'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('adjust_wallet')
                    ->label('مدیریت کیف پول')
                    ->icon(Heroicon::OutlinedWallet)
                    ->color('warning')
                    ->form([
                        Select::make('action_type')
                            ->label('نوع عملیات')
                            ->options([
                                'deposit' => 'افزایش اعتبار (واریز به کیف پول)',
                                'withdraw' => 'کاهش اعتبار (برداشت از کیف پول)',
                            ])
                            ->default('deposit')
                            ->required(),

                        TextInput::make('amount_toman')
                            ->label('مبلغ به تومان')
                            ->numeric()
                            ->required()
                            ->minValue(1000)
                            ->helperText('مثال: ۱۰۰,۰۰۰ تومان'),

                        TextInput::make('reason')
                            ->label('علت / توضیحات تراکنش')
                            ->placeholder('مثال: پاداش خرید، اصلاح حساب، کش‌بک ویژه')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (User $record, array $data): void {
                        $walletService = app(WalletService::class);
                        $amountRial = (int) $data['amount_toman'] * 10;
                        $desc = (string) $data['reason'];

                        if ($data['action_type'] === 'deposit') {
                            $walletService->deposit(
                                user: $record,
                                amountRial: $amountRial,
                                description: "شارژ دستی ادمین: {$desc}",
                                type: WalletTransactionType::AdminAdjustment
                            );
                            Notification::make()
                                ->title('موجودی کیف پول با موفقیت افزایش یافت.')
                                ->success()
                                ->send();
                        } else {
                            try {
                                $walletService->withdraw(
                                    user: $record,
                                    amountRial: $amountRial,
                                    description: "کسر دستی ادمین: {$desc}"
                                );
                                Notification::make()
                                    ->title('موجودی کیف پول با موفقیت کسر شد.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('خطا در کسر موجودی')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
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
