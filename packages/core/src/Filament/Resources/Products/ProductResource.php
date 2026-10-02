<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Products;

use Alareqi\FilamentTree\Forms\Components\TreeSelect;
use Reyhan\Core\Filament\Resources\Products\Pages\CreateProduct;
use Reyhan\Core\Filament\Resources\Products\Pages\EditProduct;
use Reyhan\Core\Filament\Resources\Products\Pages\ListProducts;
use Reyhan\Core\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\Specification;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
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
use Morilog\Jalali\Jalalian;
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;
use UnitEnum;
use Zvizvi\FilamentColumnFilters\Filters\ColumnFilter;

class ProductResource extends Resource
{
    use HasSEOFields;

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
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        // Main Canvas (2 Cols on Desktop)
                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([
                                Section::make('اطلاعات و محتوای اصلی محصول')
                                    ->description('نام تجاری، خلاصه کوتاه و توضیحات تکمیلی کالا')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('نام کامل محصول')
                                            ->placeholder('مثال: کرم پودر مات ۲۴ ساعته لورآل')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set, callable $get): void {
                                                if ($operation === 'create' && empty($get('slug')) && ! empty($state)) {
                                                    $set('slug', Str::slug($state, '-', null));
                                                }
                                            }),

                                        Textarea::make('short_description')
                                            ->label('خلاصه کوتاه (نمایش در کارت‌های خرید)')
                                            ->placeholder('یک یا دو خط توضیح کلیدی در مورد مشخصات بارز این محصول...')
                                            ->rows(2),

                                        RichEditor::make('description')
                                            ->label('نقد، بررسی و توضیحات جامع محصول')
                                            ->fileAttachmentsDirectory('products/descriptions')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('گالری تصاویر کالا')
                                    ->description('تصاویر باکیفیت محصول برای نمایش در صفحه خرید و زوم')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('gallery')
                                            ->collection('gallery')
                                            ->label('')
                                            ->multiple()
                                            ->reorderable()
                                            ->image()
                                            ->maxFiles(8)
                                            ->helperText('می‌توانید تا ۸ تصویر اضافه کنید. اولین تصویر به عنوان کاور اصلی استفاده خواهد شد.'),
                                    ]),

                                Section::make('مشخصات فنی کالا')
                                    ->description('تکمیل مشخصات فنی ساختاریافته کالا جهت نمایش در تب مشخصات و مقایسه تخصصی')
                                    ->collapsible()
                                    ->schema([
                                        Repeater::make('specifications')
                                            ->relationship('specifications')
                                            ->label('مشخصات فنی')
                                            ->columns(3)
                                            ->schema([
                                                Select::make('specification_id')
                                                    ->label('عنوان مشخصه')
                                                    ->options(fn () => Specification::query()->with('group')->get()->mapWithKeys(fn ($s) => [$s->id => ($s->group ? "{$s->group->name} / " : '').$s->name.($s->unit ? " ({$s->unit})" : '')]))
                                                    ->searchable()
                                                    ->required(),
                                                TextInput::make('value')
                                                    ->label('مقدار مشخصه')
                                                    ->required()
                                                    ->columnSpan(2),
                                            ])
                                            ->defaultItems(0),
                                    ]),

                                static::seoSection()
                                    ->heading('سئو و بهینه‌سازی موتورهای جستجو (SEO)')
                                    ->description('پیش‌نمایش زنده در گوگل و شبکه‌های اجتماعی، تگ‌های متا و تنظیمات سئو')
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        // Sidebar Canvas (1 Col on Desktop)
                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->schema([
                                Section::make('وضعیت و انتشار')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('فعال برای فروش آنلاین')
                                            ->default(true)
                                            ->helperText('در صورت غیرفعال بودن، کالا در کاتالوگ فرانت نمایش داده نمی‌شود'),

                                        Toggle::make('is_tax_exempt')
                                            ->label('معاف از مالیات بر ارزش افزوده (ماده ۹)')
                                            ->default(false)
                                            ->helperText('برای کالاهای اساسی، کتاب، دارو و محصولات کشاورزی معاف از ۱۰٪ ارزش افزوده'),

                                        Toggle::make('is_featured')
                                            ->label('محصول ویژه و پیشنهادی')
                                            ->default(false)
                                            ->helperText('نمایش در اسلایدرهای صفحه اصلی فروشگاه'),

                                        DateTimePicker::make('published_at')
                                            ->label('تاریخ انتشار')
                                            ->default(now()),
                                    ]),

                                Section::make('دسته‌بندی و سازنده')
                                    ->schema([
                                        TreeSelect::make('category_id')
                                            ->label('دسته‌بندی اصلی')
                                            ->placeholder('انتخاب دسته‌بندی...')
                                            ->searchable()
                                            ->required()
                                            ->treeOptions(fn (): array => Category::query()
                                                ->orderBy('order')
                                                ->get()
                                                ->map(fn (Category $c): array => [
                                                    'value' => $c->getKey(),
                                                    'parent' => $c->parent_id,
                                                    'label' => $c->name,
                                                ])
                                                ->all()
                                            ),

                                        Select::make('brand_id')
                                            ->label('برند سازنده')
                                            ->relationship('brand', 'name')
                                            ->searchable()
                                            ->preload(),

                                        TextInput::make('slug')
                                            ->label('نامک یکتا (Persian Slug)')
                                            ->required()
                                            ->unique(Product::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->helperText('برای ساختار URL اختصاصی این محصول در فرانت‌اند'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->collection('gallery')
                    ->label('تصویر')
                    ->circular()
                    ->size(45),

                TextColumn::make('name')
                    ->label('مشخصات کالا')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->columnFilter(ColumnFilter::search())
                    ->description(fn (Product $record): string => 'اسلاگ: '.$record->slug)
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->columnFilter(ColumnFilter::select()),

                TextColumn::make('brand.name')
                    ->label('برند')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->columnFilter(ColumnFilter::select()),

                TextColumn::make('price_range')
                    ->label('محدوده قیمت (تومان)')
                    ->formatStateUsing(function (Product $record): string {
                        $range = $record->price_range;
                        if (! $range['min'] && ! $range['max']) {
                            return 'بدون تنوع';
                        }
                        $minToman = (int) ($range['min'] / 10);
                        $maxToman = (int) ($range['max'] / 10);

                        if ($minToman === $maxToman) {
                            return number_format($minToman).' تومان';
                        }

                        return number_format($minToman).' تا '.number_format($maxToman).' تومان';
                    })
                    ->weight('medium'),

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

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn (?string $state): string => $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('هنوز محصولی ثبت نشده است')
            ->emptyStateDescription('برای افزودن اولین محصول آرایشی یا بهداشتی، روی دکمه ثبت محصول جدید کلیک کنید.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->filtersFormColumns(2)
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('وضعیت فعال بودن')
                    ->trueLabel('فقط کالاهای فعال')
                    ->falseLabel('کالاهای غیرفعال'),
                TernaryFilter::make('is_featured')
                    ->label('محصولات ویژه'),
                SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
                SelectFilter::make('brand_id')
                    ->label('برند سازنده')
                    ->relationship('brand', 'name'),
                TrashedFilter::make()
                    ->label('سطل زباله'),
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
