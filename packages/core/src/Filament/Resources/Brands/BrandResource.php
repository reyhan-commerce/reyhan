<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Brands;

use Reyhan\Core\Filament\Resources\Brands\Pages\CreateBrand;
use Reyhan\Core\Filament\Resources\Brands\Pages\EditBrand;
use Reyhan\Core\Filament\Resources\Brands\Pages\ListBrands;
use Reyhan\Core\Models\Brand;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'برندها';

    protected static ?string $modelLabel = 'برند';

    protected static ?string $pluralModelLabel = 'برندها';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و کاتالوگ';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 2])
                            ->schema([
                                Section::make('مشخصات برند')
                                    ->description('اطلاعات شرکت سازنده یا برند تجاری کالاها')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('نام فارسی برند')
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
                                            ->unique(Brand::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255),

                                        TextInput::make('name_en')
                                            ->label('نام لاتین / انگلیسی')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label('توضیحات و معرفی برند')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 1])
                            ->schema([
                                Section::make('لوگو و نماد تجاری')
                                    ->schema([
                                        FileUpload::make('logo')
                                            ->label('تصویر لوگو')
                                            ->image()
                                            ->directory('brands')
                                            ->imageEditor(),
                                    ]),

                                Section::make('تنظیمات انتشار')
                                    ->schema([
                                        TextInput::make('order')
                                            ->label('ترتیب نمایش')
                                            ->required()
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('فعال و قابل نمایش')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('لوگو')
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label('نام برند')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Brand $record): string => $record->name_en ? $record->name_en.' • '.$record->slug : $record->slug),

                TextColumn::make('products_count')
                    ->label('تعداد کالاها')
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('order')
                    ->label('ترتیب')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->emptyStateHeading('هنوز برندی ثبت نشده است')
            ->emptyStateDescription('برای نسبت دادن محصولات به سازندگان آنها، اولین برند را اضافه کنید.')
            ->emptyStateIcon(Heroicon::OutlinedSparkles)
            ->filtersFormColumns(2)
            ->filters([
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
