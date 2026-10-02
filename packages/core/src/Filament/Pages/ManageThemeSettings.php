<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Pages;

use Reyhan\Core\Settings\ThemeSettings;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageThemeSettings extends SettingsPage
{
    protected static string $settings = ThemeSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'پوسته و هویت بصری';

    protected static ?string $title = 'تنظیمات پوسته، رنگ‌ها و تایپوگرافی';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات سیستم';

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('تنظیمات پوسته')
                    ->tabs([
                        Tab::make('رنگ‌بندی و پالت')
                            ->icon(Heroicon::OutlinedSwatch)
                            ->columns(2)
                            ->schema([
                                ColorPicker::make('primary_color')
                                    ->label('رنگ اصلی برند (Primary Color)')
                                    ->helperText('این رنگ برای دکمه‌ها، بج‌ها، لینک‌های فعال و المان‌های کلیدی استفاده می‌شود.')
                                    ->required(),

                                ColorPicker::make('secondary_color')
                                    ->label('رنگ مکمل (Secondary Color)')
                                    ->helperText('برای تاکیدهای بصری، بنرهای ثانویه و آیکون‌های کمکی.')
                                    ->required(),
                            ]),

                        Tab::make('هندسه و فواصل')
                            ->icon(Heroicon::OutlinedSquare3Stack3d)
                            ->columns(2)
                            ->schema([
                                Select::make('border_radius')
                                    ->label('شعاع گوشه‌ها (Border Radius)')
                                    ->helperText('شعاع پایه اجزا و کارت‌ها در فرانت‌اند (استاندارد Nuxt UI بر پایه این مقدار به صورت متناسب مقیاس‌بندی می‌شود).')
                                    ->options([
                                        '0px' => 'تیز و تخت (0px - Sharp / Brutalist)',
                                        '0.125rem' => 'بسیار کم و ظریف (2px - Subtle)',
                                        '0.25rem' => 'استاندارد سیستم (4px - Standard / پیش‌فرض Nuxt UI)',
                                        '0.375rem' => 'نرم و ملایم (6px - Smooth)',
                                        '0.5rem' => 'گرد و امروزی (8px - Rounded)',
                                        '0.75rem' => 'بسیار گرد (12px - High)',
                                    ])
                                    ->default('0.25rem')
                                    ->required(),

                                Select::make('spacing_scale')
                                    ->label('مقیاس فواصل (Spacing)')
                                    ->options([
                                        'compact' => 'فشرده (Compact)',
                                        'normal' => 'استاندارد (Normal)',
                                        'spacious' => 'باز و فراخ (Spacious)',
                                    ])
                                    ->required(),

                                Select::make('shadow_scale')
                                    ->label('شدت سایه‌ها (Shadows)')
                                    ->options([
                                        'none' => 'بدون سایه (Flat)',
                                        'sm' => 'سایه ملایم (Soft)',
                                        'md' => 'سایه استاندارد (Medium)',
                                        'lg' => 'سایه عمیق (Deep)',
                                    ])
                                    ->required(),

                                Select::make('blur_scale')
                                    ->label('افکت شیشه‌ای / بلور (Glassmorphism)')
                                    ->options([
                                        'none' => 'بدون بلور (ساده)',
                                        'sm' => 'بلور خفیف (4px)',
                                        'md' => 'بلور شیشه‌ای متوسط (12px)',
                                        'lg' => 'بلور مات قوی (24px)',
                                    ])
                                    ->required(),
                            ]),

                        Tab::make('تایپوگرافی')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->columns(2)
                            ->schema([
                                Select::make('font_family')
                                    ->label('فونت اصلی سایت')
                                    ->options([
                                        'Vazirmatn' => 'وزیرمتن (Vazirmatn - استاندارد وب فارسی)',
                                        'IRANSans' => 'ایران‌سنس',
                                        'YekanBakh' => 'یکان‌بخش',
                                    ])
                                    ->required(),

                                Select::make('font_scale')
                                    ->label('اندازه پایه قلم (Font Scale)')
                                    ->options([
                                        '0.875rem' => 'ریز (14px)',
                                        '1rem' => 'استاندارد (16px)',
                                        '1.125rem' => 'درشت و خوانا (18px)',
                                    ])
                                    ->required(),
                            ]),

                        Tab::make('لوگو و نمادها')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->columns(3)
                            ->schema([
                                FileUpload::make('logo_light')
                                    ->label('لوگوی تم روشن (Light Logo)')
                                    ->image()
                                    ->directory('theme')
                                    ->helperText('نمایش در پس‌زمینه سفید یا روشن'),

                                FileUpload::make('logo_dark')
                                    ->label('لوگوی تم تاریک (Dark Logo)')
                                    ->image()
                                    ->directory('theme')
                                    ->helperText('نمایش در حالت شب (Dark Mode)'),

                                FileUpload::make('favicon')
                                    ->label('فاوآیکون (Favicon)')
                                    ->image()
                                    ->directory('theme')
                                    ->helperText('آیکون تب مرورگر (ICO / PNG)'),
                            ]),
                    ]),
            ]);
    }
}
