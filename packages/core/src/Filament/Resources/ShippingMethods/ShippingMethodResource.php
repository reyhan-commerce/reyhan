<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\ShippingMethods;

use Reyhan\Core\Filament\Resources\ShippingMethods\Pages\CreateShippingMethod;
use Reyhan\Core\Filament\Resources\ShippingMethods\Pages\EditShippingMethod;
use Reyhan\Core\Filament\Resources\ShippingMethods\Pages\ListShippingMethods;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\ShippingMethod;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'روش‌های ارسال';

    protected static ?string $modelLabel = 'روش ارسال';

    protected static ?string $pluralModelLabel = 'روش‌های ارسال مرسوله';

    protected static string|UnitEnum|null $navigationGroup = 'سفارشات و مالی';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات اصلی روش ارسال')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام روش ارسال')
                            ->placeholder('مثال: پست پیشتاز سراسری')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('شناسه یکتا (Slug)')
                            ->placeholder('مثال: pishtaz')
                            ->required()
                            ->unique(ShippingMethod::class, 'slug', ignoreRecord: true)
                            ->maxLength(100),

                        TextInput::make('icon')
                            ->label('آیکون روش ارسال')
                            ->placeholder('i-lucide-truck')
                            ->default('i-lucide-truck'),

                        TextInput::make('estimated_delivery_days')
                            ->label('مدت تخمینی تحویل')
                            ->placeholder('مثال: ۲ تا ۴ روز کاری')
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label('توضیحات تکمیلی برای مشتری')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('تعرفه‌ها و محدودیت‌ها')
                    ->columns(2)
                    ->schema([
                        TextInput::make('base_cost')
                            ->label('هزینه پایه ارسال (ریال)')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('مبلغ به ریال است (مثال: ۶۵۰,۰۰۰ ریال = ۶۵,۰۰۰ تومان)'),

                        TextInput::make('cost_per_kg')
                            ->label('هزینه اضافه به ازای هر کیلو مازاد (ریال)')
                            ->numeric()
                            ->default(0)
                            ->helperText('هزینه هر ۱۰۰۰ گرم وزن اضافه بالاتر از ۱ کیلوگرم'),

                        TextInput::make('free_shipping_threshold')
                            ->label('حداقل مبلغ خرید برای ارسال رایگان (ریال)')
                            ->numeric()
                            ->nullable()
                            ->helperText('در صورت خالی بودن، از تنظیمات عمومی فروشگاه تبعیت می‌کند.'),

                        Select::make('supported_provinces')
                            ->label('استان‌های تحت پوشش')
                            ->multiple()
                            ->options(fn () => Province::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->placeholder('همه استان‌ها (سراسری)')
                            ->helperText('اگر خالی بگذارید، این روش برای تمام استان‌های ایران فعال خواهد بود.'),
                    ]),

                Section::make('تنظیمات زمان‌بندی و وضعیت')
                    ->columns(3)
                    ->schema([
                        Toggle::make('requires_time_slot')
                            ->label('امکان انتخاب بازه زمانی تحویل')
                            ->helperText('مخصوص پیک اکسپرس شهری (انتخاب صبح ۹-۱۳ یا عصر ۱۶-۲۰)'),

                        Toggle::make('is_active')
                            ->label('فعال در فرآیند ثبت سفارش')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام روش')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('شناسه (Slug)')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('base_cost')
                    ->label('هزینه پایه')
                    ->formatStateUsing(fn (int $state): string => number_format((int) ($state / 10)).' تومان')
                    ->sortable(),

                TextColumn::make('estimated_delivery_days')
                    ->label('مدت تحویل')
                    ->default('-'),

                IconColumn::make('requires_time_slot')
                    ->label('انتخاب بازه تحویل')
                    ->boolean(),

                ToggleColumn::make('is_active')
                    ->label('وضعیت فعال'),

                TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListShippingMethods::route('/'),
            'create' => CreateShippingMethod::route('/create'),
            'edit' => EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
