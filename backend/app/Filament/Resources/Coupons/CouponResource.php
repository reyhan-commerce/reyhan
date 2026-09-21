<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
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
                Section::make('مشخصات کوپن تخفیف')
                    ->description('اطلاعات پایه، کد و نوع تخفیف')
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
                            ->label('عنوان / توضیحات کوپن')
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
                            ->helperText('درصد (مثلاً ۲۰) یا مبلغ به ریال'),

                        Select::make('scope')
                            ->label('دامنه اعمال تخفیف')
                            ->options(CouponScope::class)
                            ->required()
                            ->native(false)
                            ->live(),

                        TextInput::make('min_order_amount')
                            ->label('حداقل مبلغ سبد خرید (ریال)')
                            ->numeric()
                            ->nullable()
                            ->helperText('خالی = بدون محدودیت'),

                        TextInput::make('max_discount_amount')
                            ->label('حداکثر سقف تخفیف (ریال)')
                            ->numeric()
                            ->nullable()
                            ->helperText('مخصوص تخفیف درصدی'),

                        TextInput::make('usage_limit')
                            ->label('سقف کل دفعات استفاده')
                            ->numeric()
                            ->nullable()
                            ->helperText('خالی = نامحدود'),

                        TextInput::make('usage_limit_per_user')
                            ->label('سقف استفاده برای هر کاربر')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        DateTimePicker::make('starts_at')
                            ->label('تاریخ شروع اعتبار')
                            ->nullable(),

                        DateTimePicker::make('expires_at')
                            ->label('تاریخ انقضا')
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('فعال')
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
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('type')
                    ->label('نوع')
                    ->badge(),

                TextColumn::make('value')
                    ->label('مقدار')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('scope')
                    ->label('دامنه')
                    ->badge(),

                TextColumn::make('usage_count')
                    ->label('دفعات استفاده')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('expires_at')
                    ->label('تاریخ انقضا')
                    ->dateTime()
                    ->sortable(),
            ])
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
