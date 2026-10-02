<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\BlogCategories;

use Reyhan\Core\Filament\Resources\BlogCategories\Pages\CreateBlogCategory;
use Reyhan\Core\Filament\Resources\BlogCategories\Pages\EditBlogCategory;
use Reyhan\Core\Filament\Resources\BlogCategories\Pages\ListBlogCategories;
use Reyhan\Core\Models\BlogCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'دسته‌بندی‌های وبلاگ';

    protected static ?string $modelLabel = 'دسته‌بندی وبلاگ';

    protected static ?string $pluralModelLabel = 'دسته‌بندی‌های وبلاگ';

    protected static string|UnitEnum|null $navigationGroup = 'محتوا و اطلاع‌رسانی';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 2])
                            ->schema([
                                Section::make('مشخصات دسته')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('عنوان دسته‌بندی')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set, callable $get): void {
                                                if ($operation === 'create' && empty($get('slug')) && ! empty($state)) {
                                                    $set('slug', Str::slug($state, '-', null));
                                                }
                                            }),

                                        TextInput::make('slug')
                                            ->label('نامک (Slug)')
                                            ->required()
                                            ->unique(BlogCategory::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255),

                                        Textarea::make('description')
                                            ->label('توضیحات دسته‌بندی')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Grid::make(1)
                            ->columnSpan(['default' => 3, 'lg' => 1])
                            ->schema([
                                Section::make('تنظیمات انتشار')
                                    ->schema([
                                        TextInput::make('order')
                                            ->label('ترتیب نمایش')
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('فعال و قابل مشاهده')
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
                TextColumn::make('name')
                    ->label('عنوان دسته‌بندی')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('نامک (Slug)')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('posts_count')
                    ->label('تعداد مقالات')
                    ->counts('posts')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('ترتیب')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),
            ])
            ->defaultSort('order')
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

    public static function getPages(): array
    {
        return [
            'index' => ListBlogCategories::route('/'),
            'create' => CreateBlogCategory::route('/create'),
            'edit' => EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
