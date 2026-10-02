<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Products\RelationManagers;

use Reyhan\Core\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'تنوع‌ها و موجودی انبار (SKU)';

    protected static ?string $modelLabel = 'تنوع محصول';

    protected static ?string $pluralModelLabel = 'تنوع‌های محصول';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('شناسه و قیمت تنوع')
                    ->columns(3)
                    ->schema([
                        TextInput::make('sku')
                            ->label('کد انبار (SKU)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true),

                        TextInput::make('barcode')
                            ->label('بارکد کالا')
                            ->maxLength(255)
                            ->unique(ProductVariant::class, 'barcode', ignoreRecord: true),

                        Select::make('attributeValues')
                            ->label('ویژگی‌های اختصاصی (رنگ/حجم/وغیره)')
                            ->relationship('attributeValues', 'value')
                            ->multiple()
                            ->preload()
                            ->required(),

                        TextInput::make('price')
                            ->label('قیمت فروش (ریال)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ریال'),

                        TextInput::make('compare_at_price')
                            ->label('قیمت قبل از تخفیف (ریال)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ریال'),

                        TextInput::make('weight')
                            ->label('وزن (گرم)')
                            ->numeric()
                            ->suffix('گرم'),
                    ]),

                Section::make('مدیریت موجودی و انبارداری')
                    ->columns(3)
                    ->schema([
                        TextInput::make('stock')
                            ->label('موجودی انبار')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('low_stock_threshold')
                            ->label('آستانه هشدار کسری انبار')
                            ->required()
                            ->numeric()
                            ->default(5),

                        DatePicker::make('expiry_date')
                            ->label('تاریخ انقضا'),

                        TextInput::make('batch_number')
                            ->label('شماره بچ / سری ساخت')
                            ->maxLength(100),

                        TextInput::make('order')
                            ->label('ترتیب')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('فعال برای فروش')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')
                    ->label('کد کالا (SKU)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('attributeValues.value')
                    ->label('مشخصات')
                    ->badge()
                    ->color('primary')
                    ->separator(' / '),

                TextColumn::make('price')
                    ->label('قیمت فروش')
                    ->formatStateUsing(fn ($state) => number_format((float) $state).' ریال')
                    ->sortable(),

                TextColumn::make('compare_at_price')
                    ->label('قیمت قبل تخفیف')
                    ->formatStateUsing(fn ($state) => $state ? number_format((float) $state).' ریال' : '—')
                    ->color('gray'),

                TextColumn::make('stock')
                    ->label('موجودی')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('افزودن تنوع جدید'),
            ])
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
}
