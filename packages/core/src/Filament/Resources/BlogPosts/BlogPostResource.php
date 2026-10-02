<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\BlogPosts;

use Reyhan\Core\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use Reyhan\Core\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use Reyhan\Core\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use Reyhan\Core\Models\BlogPost;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'پست‌های وبلاگ (مقالات)';

    protected static ?string $modelLabel = 'مقاله وبلاگ';

    protected static ?string $pluralModelLabel = 'مقالات و اخبار';

    protected static string|UnitEnum|null $navigationGroup = 'محتوا و اطلاع‌رسانی';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('اطلاعات مقاله')
                    ->tabs([
                        Tab::make('محتوا و نگارش')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('عنوان مقاله')
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
                                            ->unique(BlogPost::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255),

                                        Select::make('category_id')
                                            ->label('دسته‌بندی مقاله')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        TextInput::make('reading_time')
                                            ->label('زمان تقریبی مطالعه (دقیقه)')
                                            ->numeric()
                                            ->default(3)
                                            ->suffix('دقیقه')
                                            ->helperText('در صورت خالی ماندن، بر اساس تعداد کلمات خودکار محاسبه می‌شود'),
                                    ]),

                                Textarea::make('summary')
                                    ->label('خلاصه یا چکیده مقاله')
                                    ->rows(3)
                                    ->placeholder('توضیح کوتاه و جذاب که در کارت مقاله در وبلاگ نمایش داده می‌شود...')
                                    ->columnSpanFull(),

                                RichEditor::make('content')
                                    ->label('متن کامل مقاله')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('تصویر شاخص و رسانه')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label('تصویر کاور مقاله')
                                    ->image()
                                    ->directory('blog')
                                    ->imageEditor()
                                    ->helperText('پیشنهاد: ابعاد ۱۲۰۰×۶۳۰ با نسبت ۱۶:۹ جهت نمایش بهینه در شبکه‌های اجتماعی'),
                            ]),

                        Tab::make('انتشار و سئو')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('author_id')
                                            ->label('نویسنده مقاله')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload(),

                                        DateTimePicker::make('published_at')
                                            ->label('تاریخ انتشار')
                                            ->default(now()),

                                        Toggle::make('is_published')
                                            ->label('وضعیت انتشار')
                                            ->default(true),

                                        Toggle::make('is_featured')
                                            ->label('مقاله برگزیده (نمایش در هیرو وبلاگ)')
                                            ->default(false),
                                    ]),

                                TagsInput::make('tags')
                                    ->label('کلمات کلیدی و برچسب‌ها')
                                    ->placeholder('برچسب جدید...')
                                    ->columnSpanFull(),

                                TextInput::make('meta_title')
                                    ->label('عنوان سئو (Meta Title)')
                                    ->placeholder('در صورت خالی بودن، عنوان اصلی مقاله استفاده می‌شود')
                                    ->maxLength(255),

                                Textarea::make('meta_description')
                                    ->label('توضیحات سئو (Meta Description)')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('تصویر')
                    ->circular()
                    ->size(40),

                TextColumn::make('title')
                    ->label('عنوان مقاله')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(45),

                TextColumn::make('category.name')
                    ->label('دسته')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label('بازدید')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('منتشر شده')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('تاریخ انتشار')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filtersFormColumns(3)
            ->filters([
                SelectFilter::make('category_id')
                    ->label('فیلتر بر اساس دسته')
                    ->relationship('category', 'name'),

                TernaryFilter::make('is_published')
                    ->label('منتشر شده / پیش‌نویس'),

                TernaryFilter::make('is_featured')
                    ->label('مقالات ویژه'),
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
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
