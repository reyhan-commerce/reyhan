<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Categories;

use Alareqi\FilamentTree\Columns\TreeColumn;
use Alareqi\FilamentTree\Forms\Components\TreeSelect;
use Reyhan\Core\Filament\Resources\Categories\Pages\CreateCategory;
use Reyhan\Core\Filament\Resources\Categories\Pages\EditCategory;
use Reyhan\Core\Filament\Resources\Categories\Pages\ListCategories;
use Reyhan\Core\Models\Category;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $navigationLabel = 'دسته‌بندی‌ها';

    protected static ?string $modelLabel = 'دسته‌بندی';

    protected static ?string $pluralModelLabel = 'دسته‌بندی‌ها';

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه و کاتالوگ';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([
                                Section::make('مشخصات و عنوان دسته‌بندی')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('نام دسته‌بندی')
                                            ->placeholder('مثال: مراقبت از پوست')
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
                                            ->unique(Category::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon(Heroicon::OutlinedLink),

                                        Textarea::make('description')
                                            ->label('توضیحات دسته‌بندی')
                                            ->placeholder('توضیحاتی جهت معرفی این بخش و بهینه‌سازی سئو...')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->schema([
                                Section::make('سلسله‌مراتب و وضعیت')
                                    ->schema([
                                        TreeSelect::make('parent_id')
                                            ->label('دسته‌بندی والد')
                                            ->placeholder('دسته‌بندی اصلی (ریشه)')
                                            ->searchable()
                                            ->treeOptions(function (?Category $record): array {
                                                $excludedIds = $record?->exists
                                                    ? array_merge([$record->id], $record->getDescendantIds()->all())
                                                    : [];

                                                return Category::query()
                                                    ->when(! empty($excludedIds), fn ($query) => $query->whereNotIn('id', $excludedIds))
                                                    ->orderBy('order')
                                                    ->get()
                                                    ->map(fn (Category $category): array => [
                                                        'value' => $category->getKey(),
                                                        'parent' => $category->parent_id,
                                                        'label' => $category->name,
                                                    ])
                                                    ->all();
                                            }),

                                        TextInput::make('order')
                                            ->label('ترتیب اولویت نمایش')
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('فعال و قابل مشاهده در سایت')
                                            ->default(true),
                                    ]),

                                Section::make('تصویر و آیکون')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('تصویر کاور دسته')
                                            ->image()
                                            ->directory('categories'),

                                        TextInput::make('icon')
                                            ->label('نام آیکون Nuxt UI')
                                            ->placeholder('i-lucide-sparkles'),
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
                ImageColumn::make('image')
                    ->label('تصویر')
                    ->circular()
                    ->size(40),

                TreeColumn::make('name')
                    ->label('نام دسته‌بندی')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (Category $record): string => $record->parent_id ? 'medium' : 'bold'),

                TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('products_count')
                    ->label('تعداد کالاها')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('ترتیب')
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
            ->reorderable('order')
            ->tree(parentColumn: 'parent_id', treeColumn: 'name')
            ->defaultSort('order')
            ->emptyStateHeading('هنوز دسته‌بندی ثبت نشده است')
            ->emptyStateDescription('برای شروع ساختار درختی کاتالوگ، اولین دسته‌بندی را ایجاد کنید.')
            ->emptyStateIcon(Heroicon::OutlinedFolder)
            ->filtersFormColumns(2)
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('وضعیت فعال/غیرفعال'),
                SelectFilter::make('parent_id')
                    ->label('دسته‌بندی والد')
                    ->relationship('parent', 'name'),
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
