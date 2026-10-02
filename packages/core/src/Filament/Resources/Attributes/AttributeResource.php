<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Attributes;

use Reyhan\Core\Enums\AttributeType;
use Reyhan\Core\Filament\Resources\Attributes\Pages\CreateAttribute;
use Reyhan\Core\Filament\Resources\Attributes\Pages\EditAttribute;
use Reyhan\Core\Filament\Resources\Attributes\Pages\ListAttributes;
use Reyhan\Core\Models\Attribute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'ویژگی‌ها و متغیرها';

    protected static ?string $modelLabel = 'ویژگی کالا';

    protected static ?string $pluralModelLabel = 'ویژگی‌ها';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و کاتالوگ';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات کلی ویژگی')
                    ->description('عنوان و نوع داده ویژگی (رنگ، اندازه، حجم، و غیره)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('عنوان ویژگی')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set, callable $get): void {
                                if ($operation === 'create' && empty($get('slug')) && ! empty($state)) {
                                    $set('slug', Str::slug($state, '-', null));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('نامک یکتا (Slug)')
                            ->required()
                            ->unique(Attribute::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('type')
                            ->label('نوع نمایش ویژگی')
                            ->options([
                                'text' => 'متن ساده (Text)',
                                'color' => 'رنگ انتخابی با کد هگز (Color Swatch)',
                                'number' => 'عدد و مقیاس (Number)',
                                'select' => 'منوی کشویی / لیست گزینه‌ای (Select)',
                            ])
                            ->default('text')
                            ->required()
                            ->live(),

                        TextInput::make('order')
                            ->label('ترتیب نمایش')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('مقادیر مجاز این ویژگی')
                    ->description('گزینه‌ها و مقادیر قابل انتخابی که به محصولات نسبت داده می‌شوند')
                    ->schema([
                        Repeater::make('values')
                            ->relationship('values')
                            ->label('گزینه‌ها')
                            ->schema([
                                TextInput::make('value')
                                    ->label('مقدار اصلی')
                                    ->required()
                                    ->placeholder('مثال: قرمز یا 50ml'),

                                TextInput::make('label')
                                    ->label('عنوان نمایشی فارسی (اختیاری)')
                                    ->placeholder('مثال: قرمز عنابی'),

                                ColorPicker::make('hex_code')
                                    ->label('کد رنگ HEX')
                                    ->visible(fn (callable $get) => $get('../../type') === 'color'),

                                TextInput::make('order')
                                    ->label('ترتیب')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->orderColumn('order')
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('عنوان ویژگی')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('نوع ویژگی')
                    ->badge()
                    ->color(fn (string|AttributeType $state): string => match ($state instanceof AttributeType ? $state->value : $state) {
                        'color' => 'warning',
                        'number' => 'info',
                        'select' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('values_count')
                    ->label('تعداد مقادیر')
                    ->counts('values')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('ترتیب')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('فیلتر بر اساس نوع')
                    ->options([
                        'text' => 'متن',
                        'color' => 'رنگ',
                        'number' => 'عدد',
                        'select' => 'لیست گزینه‌ای',
                    ]),
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
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }
}
