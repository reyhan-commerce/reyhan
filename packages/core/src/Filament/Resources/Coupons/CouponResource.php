<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Coupons;

use Reyhan\Core\Enums\CouponScope;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Filament\Resources\Coupons\Pages\CreateCoupon;
use Reyhan\Core\Filament\Resources\Coupons\Pages\EditCoupon;
use Reyhan\Core\Filament\Resources\Coupons\Pages\ListCoupons;
use Reyhan\Core\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'کوپن‌های تخفیف';

    protected static ?string $modelLabel = 'کوپن تخفیف';

    protected static ?string $pluralModelLabel = 'کوپن‌های تخفیف';

    protected static string|UnitEnum|null $navigationGroup = 'سفارشات و مالی';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات پایه کوپن')
                    ->description('تعریف کد، عنوان و نوع تخفیف')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('کد تخفیف')
                            ->required()
                            ->maxLength(50)
                            ->unique(Coupon::class, 'code', ignoreRecord: true)
                            ->placeholder('مثال: NOWRUZ1405')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase; font-family: monospace;']),

                        TextInput::make('title')
                            ->label('عنوان کوپن')
                            ->maxLength(255)
                            ->placeholder('مثال: تخفیف نوروزی ویژه عید'),

                        Select::make('type')
                            ->label('نوع تخفیف')
                            ->options(CouponType::class)
                            ->required()
                            ->native(false),

                        TextInput::make('value')
                            ->label('مقدار تخفیف')
                            ->required()
                            ->numeric()
                            ->helperText('درصد (مثلاً ۲۰) یا مبلغ به ریال (۱۰ ریال = ۱ تومان)'),

                        Select::make('scope')
                            ->label('دامنه اعمال تخفیف')
                            ->options(CouponScope::class)
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('محدودیت‌ها و سقف استفاده')
                    ->description('تعیین سقف مبالغ و تعداد استفاده از کوپن')
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_order_amount')
                            ->label('حداقل مبلغ سفارش (ریال)')
                            ->numeric()
                            ->nullable()
                            ->helperText('خالی = بدون محدودیت حداقل خرید'),

                        TextInput::make('max_discount_amount')
                            ->label('حداکثر سقف تخفیف (ریال)')
                            ->numeric()
                            ->nullable()
                            ->helperText('مخصوص تخفیف‌های درصدی'),

                        TextInput::make('usage_limit')
                            ->label('سقف کل دفعات استفاده')
                            ->numeric()
                            ->nullable()
                            ->helperText('خالی = نامحدود'),

                        TextInput::make('usage_limit_per_user')
                            ->label('سقف استفاده هر کاربر')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ]),

                Section::make('زمان‌بندی و انتشار')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('تاریخ شروع اعتبار')
                            ->nullable(),

                        DateTimePicker::make('expires_at')
                            ->label('تاریخ انقضا')
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('کوپن فعال و آماده استفاده است')
                            ->default(true)
                            ->required(),
                    ]),

                Section::make('دامنه شمولیت تخفیف')
                    ->description('انتخاب دسته‌بندی‌ها، برندها یا تنوع‌های مشمول این کوپن')
                    ->schema([
                        Select::make('categories')
                            ->label('دسته‌بندی‌های مشمول')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->visible(fn (callable $get) => $get('scope') === CouponScope::Categories->value || $get('scope') === CouponScope::Categories),

                        Select::make('brands')
                            ->label('برندهای مشمول')
                            ->relationship('brands', 'name')
                            ->multiple()
                            ->preload()
                            ->visible(fn (callable $get) => $get('scope') === CouponScope::Brands->value || $get('scope') === CouponScope::Brands),

                        Select::make('variants')
                            ->label('تنوع‌های مشمول')
                            ->relationship('variants', 'sku')
                            ->multiple()
                            ->searchable()
                            ->visible(fn (callable $get) => $get('scope') === CouponScope::Variants->value || $get('scope') === CouponScope::Variants),
                    ])
                    ->visible(fn (callable $get) => in_array($get('scope'), [
                        CouponScope::Categories->value,
                        CouponScope::Categories,
                        CouponScope::Brands->value,
                        CouponScope::Brands,
                        CouponScope::Variants->value,
                        CouponScope::Variants,
                    ], true)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('کد کوپن')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold')
                    ->description(fn (Coupon $record): string => $record->title),

                TextColumn::make('type')
                    ->label('نوع')
                    ->badge(),

                TextColumn::make('value')
                    ->label('مقدار تخفیف')
                    ->formatStateUsing(fn (int $state, Coupon $record): string => $record->type === CouponType::Percentage ? $state.'٪' : number_format((int) ($state / 10)).' تومان')
                    ->sortable(),

                TextColumn::make('scope')
                    ->label('دامنه')
                    ->badge(),

                TextColumn::make('usage_count')
                    ->label('دفعات مصرف')
                    ->formatStateUsing(fn (int $state, Coupon $record): string => $record->usage_limit ? $state.' از '.$record->usage_limit : (string) $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('expires_at')
                    ->label('تاریخ انقضا')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : 'همیشگی')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('هنوز کوپن تخفیفی تعریف نشده است')
            ->emptyStateDescription('برای ایجاد جشنواره‌ها و کمپین‌های تبلیغاتی، اولین کد تخفیف را ایجاد کنید.')
            ->emptyStateIcon(Heroicon::OutlinedTicket)
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع تخفیف')
                    ->options(CouponType::class),
                SelectFilter::make('scope')
                    ->label('دامنه')
                    ->options(CouponScope::class),
                TernaryFilter::make('is_active')
                    ->label('وضعیت فعال/غیرفعال'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
