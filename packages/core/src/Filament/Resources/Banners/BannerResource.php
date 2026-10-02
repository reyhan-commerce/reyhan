<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\Banners;

use Reyhan\Core\Enums\BannerPosition;
use Reyhan\Core\Filament\Resources\Banners\Pages\CreateBanner;
use Reyhan\Core\Filament\Resources\Banners\Pages\EditBanner;
use Reyhan\Core\Filament\Resources\Banners\Pages\ListBanners;
use Reyhan\Core\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'بنرها و اسلایدرها';

    protected static ?string $modelLabel = 'بنر تبلیغاتی';

    protected static ?string $pluralModelLabel = 'بنرها و اسلایدرها';

    protected static string|UnitEnum|null $navigationGroup = 'محتوا و وبلاگ';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات بنر')
                    ->description('اطلاعات عنوان، موقعیت و لینک مقصد')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان بنر')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: جشنواره شگفت‌انگیز پاییزی'),

                        TextInput::make('subtitle')
                            ->label('زیرعنوان یا توضیحات کوتاه')
                            ->maxLength(255)
                            ->placeholder('مثال: تا ۵۰٪ تخفیف روی تمامی لوازم دیجیتال'),

                        TextInput::make('link_url')
                            ->label('لینک مقصد کلیک')
                            ->maxLength(512)
                            ->placeholder('مثال: /products?discount=true یا https://...'),

                        Select::make('position')
                            ->label('موقعیت نمایش')
                            ->options(BannerPosition::options())
                            ->default(BannerPosition::HomeSlider->value)
                            ->required(),

                        TextInput::make('order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0)
                            ->helperText('اعداد کوچکتر زودتر نمایش داده می‌شوند'),

                        Toggle::make('is_active')
                            ->label('وضعیت انتشار')
                            ->default(true)
                            ->inline(false),

                        DateTimePicker::make('starts_at')
                            ->label('زمان آغاز نمایش (اختیاری)'),

                        DateTimePicker::make('ends_at')
                            ->label('زمان پایان نمایش (اختیاری)'),
                    ]),

                Section::make('تصاویر بنر')
                    ->description('آپلود تصویر اختصاصی برای نسخه دسکتاپ و موبایل')
                    ->columns(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('image')
                            ->label('تصویر بنر (دسکتاپ)')
                            ->image()
                            ->imageEditor()
                            ->helperText('پیشنهادی برای اسلایدر: ۱۹۲۰×۶۰۰ پیکسل | برای بنر میانی: ۱۲۰۰×۴۰۰ پیکسل'),

                        SpatieMediaLibraryFileUpload::make('mobile_image')
                            ->collection('mobile_image')
                            ->label('تصویر بنر (موبایل - اختیاری)')
                            ->image()
                            ->imageEditor()
                            ->helperText('در صورت عدم آپلود، از تصویر دسکتاپ استفاده خواهد شد (پیشنهادی: ۸۰۰×۶۰۰ پیکسل)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->label('پیش‌نمایش')
                    ->square()
                    ->defaultImageUrl(fn (Banner $record): string => $record->image_url ?: '/placeholder.png'),

                TextColumn::make('title')
                    ->label('عنوان بنر')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Banner $b): ?string => $b->subtitle),

                TextColumn::make('position')
                    ->label('موقعیت')
                    ->badge(),

                TextColumn::make('order')
                    ->label('ترتیب')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('موقعیت نمایش')
                    ->options(BannerPosition::options()),
                TernaryFilter::make('is_active')
                    ->label('فقط فعال‌ها'),
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

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
