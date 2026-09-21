<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $navigationLabel = 'محصولات';

    protected static ?string $modelLabel = 'محصول';

    protected static ?string $pluralModelLabel = 'محصولات';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و کاتالوگ';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات پایه محصول')
                    ->description('عنوان، دسته‌بندی و نامک اینترنتی محصول')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام کامل محصول')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set, callable $get): void {
                                if ($operation === 'create' && empty($get('slug')) && ! empty($state)) {
                                    $set('slug', Str::slug($state, '-', null));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('نامک یکتا (Persian Slug)')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('دسته‌بندی اصلی')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('brand_id')
                            ->label('برند سازنده')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload(),

                        Textarea::make('short_description')
                            ->label('خلاصه معرفی محصول')
                            ->rows(2)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('توضیحات و نقد کامل محصول')
                            ->columnSpanFull(),
                    ]),

                Section::make('گالری تصاویر محصول')
                    ->description('تصاویر باکیفیت برای نمایش در صفحه خرید و اسلایدر')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->label('تصاویر گالری')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxFiles(8),
                    ]),

                Section::make('سئو و بهینه‌سازی موتورهای جستجو')
                    ->description('اطلاعات متادیتا جهت ارتقای رتبه در گوگل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('عنوان متا (SEO Title)')
                            ->maxLength(255),

                        Textarea::make('meta_description')
                            ->label('توضیحات متا (SEO Description)')
                            ->rows(2),
                    ]),

                Section::make('تنظیمات انتشار و وضعیت')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('فعال برای فروش')
                            ->default(true)
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('محصول برگزیده / ویژه')
                            ->default(false),

                        DateTimePicker::make('published_at')
                            ->label('تاریخ و ساعت انتشار')
                            ->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->collection('gallery')
                    ->label('تصویر')
                    ->circular(),

                TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('brand.name')
                    ->label('برند')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('price_range')
                    ->label('محدوده قیمت')
                    ->formatStateUsing(function ($record): string {
                        /** @var Product $record */
                        $range = $record->price_range;
                        if (! $range['min'] && ! $range['max']) {
                            return 'بدون تنوع';
                        }
                        if ($range['min'] === $range['max']) {
                            return number_format((float) $range['min']).' ریال';
                        }

                        return number_format((float) $range['min']).' - '.number_format((float) $range['max']).' ریال';
                    }),

                TextColumn::make('variants_count')
                    ->label('تنوع‌ها')
                    ->counts('variants')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('تاریخ انتشار')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('وضعیت فعال'),
                TernaryFilter::make('is_featured')
                    ->label('محصولات ویژه'),
                SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
                SelectFilter::make('brand_id')
                    ->label('برند')
                    ->relationship('brand', 'name'),
                TrashedFilter::make()
                    ->label('حذف شده‌ها'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
