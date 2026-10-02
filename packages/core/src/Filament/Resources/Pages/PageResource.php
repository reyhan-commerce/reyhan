<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Pages;

use Reyhan\Core\Filament\Resources\Pages\Pages\CreatePage;
use Reyhan\Core\Filament\Resources\Pages\Pages\EditPage;
use Reyhan\Core\Filament\Resources\Pages\Pages\ListPages;
use Reyhan\Core\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'صفحات متنی (CMS)';

    protected static ?string $modelLabel = 'صفحه متنی';

    protected static ?string $pluralModelLabel = 'صفحات ایستا و درباره ما';

    protected static string|UnitEnum|null $navigationGroup = 'محتوا و اطلاع‌رسانی';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('تنظیمات صفحه')
                    ->tabs([
                        Tab::make('محتوا و متن اصلی')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('عنوان صفحه')
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
                                            ->unique(Page::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255),
                                    ]),

                                RichEditor::make('content')
                                    ->label('متن اصلی صفحه')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('بلوک‌های ویژه درباره ما')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                TextInput::make('metadata.badge')
                                    ->label('نشان یا برچسب بالای تیتر')
                                    ->placeholder('مثال: داستان و تعهد ما در ریحان'),

                                TextInput::make('metadata.heading')
                                    ->label('تیتر برجسته هیرو')
                                    ->placeholder('مثال: تجربه‌ای نو و سریع از خرید آنلاین با تضمین اصالت و بهترین قیمت'),

                                Repeater::make('metadata.stats')
                                    ->label('آمارهای کلیدی (Stats Grid)')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('مقدار / عدد')
                                            ->placeholder('+۱۵,۰۰۰')
                                            ->required(),
                                        TextInput::make('label')
                                            ->label('عنوان شاخص')
                                            ->placeholder('مشتری وفادار')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->defaultItems(0),

                                Repeater::make('metadata.features')
                                    ->label('تعهدات و ویژگی‌ها (Core Values)')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('عنوان تعهد')
                                            ->required(),
                                        TextInput::make('icon')
                                            ->label('نام آیکون lucide')
                                            ->placeholder('i-lucide-shield-check')
                                            ->default('i-lucide-shield-check'),
                                        Textarea::make('desc')
                                            ->label('توضیح مختصر')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),

                        Tab::make('تنظیمات سئو و انتشار')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('عنوان متا (SEO Title)')
                                    ->maxLength(255),

                                Textarea::make('meta_description')
                                    ->label('توضیحات متا (SEO Description)')
                                    ->rows(3),

                                Toggle::make('is_active')
                                    ->label('فعال و در دسترس')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان صفحه')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('نامک (Slug)')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('آخرین به‌روزرسانی')
                    ->dateTime()
                    ->sortable(),
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
