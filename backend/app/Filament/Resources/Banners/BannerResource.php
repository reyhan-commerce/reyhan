<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners;

use App\Enums\BannerPosition;
use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
                    ->description('اطلاعات تصویر، موقعیت و لینک مقصد')
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

                        TextInput::make('image_url')
                            ->label('آدرس تصویر بنر (دسکتاپ)')
                            ->required()
                            ->maxLength(512)
                            ->placeholder('https://... یا /images/banners/...'),

                        TextInput::make('mobile_image_url')
                            ->label('آدرس تصویر موبایل (اختیاری)')
                            ->maxLength(512)
                            ->placeholder('در صورت خالی بودن، از تصویر دسکتاپ استفاده می‌شود'),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('پیش‌نمایش')
                    ->square()
                    ->defaultImageUrl('/placeholder.png'),

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
            ->rowActions([
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
