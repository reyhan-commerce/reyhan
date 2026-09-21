<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
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
                Section::make('اطلاعات دسته‌بندی')
                    ->description('مشخصات و ساختار درختی دسته‌بندی')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام دسته‌بندی')
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
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('parent_id')
                            ->label('دسته‌بندی والد')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('دسته‌بندی ریشه (بدون والد)'),

                        TextInput::make('order')
                            ->label('ترتیب نمایش')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Textarea::make('description')
                            ->label('توضیحات دسته‌بندی')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('icon')
                            ->label('آیکون')
                            ->maxLength(100)
                            ->placeholder('i-heroicons-sparkles'),

                        FileUpload::make('image')
                            ->label('تصویر شاخص')
                            ->image()
                            ->directory('categories'),

                        Toggle::make('is_active')
                            ->label('فعال و قابل نمایش')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('تصویر')
                    ->circular(),

                TextColumn::make('name')
                    ->label('نام دسته‌بندی')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (Category $record): string => $record->parent_id ? 'medium' : 'bold')
                    ->formatStateUsing(function (string $state, Category $record): string {
                        return $record->parent_id ? '↳ '.$state : '📁 '.$state;
                    })
                    ->description(function (Category $record): ?string {
                        $ancestors = $record->getAncestors();
                        if ($ancestors->isEmpty()) {
                            return 'دسته‌بندی اصلی (ریشه)';
                        }

                        return 'مسیر: '.$ancestors->pluck('name')->implode(' > ');
                    }),

                TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('parent.name')
                    ->label('والد')
                    ->searchable()
                    ->placeholder('ریشه')
                    ->badge()
                    ->color('info'),

                TextColumn::make('products_count')
                    ->label('تعداد کالا')
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('success'),

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
            ->defaultGroup('parent.name')
            ->groups([
                Group::make('parent.name')
                    ->label('دسته‌بندی والد')
                    ->collapsible(),
            ])
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
            'tree' => Pages\CategoryTreePage::route('/tree'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
