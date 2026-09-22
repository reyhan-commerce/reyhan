<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'تنظیمات عمومی';

    protected static ?string $title = 'تنظیمات عمومی فروشگاه';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات سیستم';

    protected static ?int $navigationSort = 1;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('تنظیمات فروشگاه')
                    ->tabs([
                        Tab::make('هویت و برندینگ')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->columns(2)
                            ->schema([
                                TextInput::make('store_name')
                                    ->label('نام فروشگاه')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('store_slogan')
                                    ->label('شعار فروشگاه')
                                    ->placeholder('فروشگاه تخصصی آرایشی و بهداشتی')
                                    ->maxLength(255),

                                FileUpload::make('store_logo')
                                    ->label('لوگوی اصلی فروشگاه')
                                    ->image()
                                    ->directory('settings')
                                    ->helperText('پیشنهاد: تصویر PNG با زمینه شفاف'),

                                FileUpload::make('store_favicon')
                                    ->label('آیکون وب‌سایت (Favicon)')
                                    ->image()
                                    ->directory('settings')
                                    ->helperText('پیشنهاد: ابعاد ۳۲×۳۲ یا ۴۸×۴۸ پیکسل'),
                            ]),

                        Tab::make('راه‌های ارتباطی و آدرس')
                            ->icon(Heroicon::OutlinedPhone)
                            ->columns(2)
                            ->schema([
                                TextInput::make('support_phone')
                                    ->label('تلفن پشتیبانی')
                                    ->tel()
                                    ->maxLength(50),

                                TextInput::make('support_email')
                                    ->label('ایمیل پشتیبانی')
                                    ->email()
                                    ->maxLength(100),

                                TextInput::make('postal_code')
                                    ->label('کد پستی انبار مرکزی')
                                    ->maxLength(20),

                                TextInput::make('work_hours')
                                    ->label('ساعات کاری و پاسخگویی')
                                    ->placeholder('شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴')
                                    ->maxLength(255),

                                Textarea::make('address')
                                    ->label('آدرس فیزیکی دفتر یا انبار')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('سفارشات و ارسال')
                            ->icon(Heroicon::OutlinedTruck)
                            ->columns(2)
                            ->schema([
                                TextInput::make('free_shipping_threshold')
                                    ->label('حداقل خرید برای ارسال رایگان')
                                    ->numeric()
                                    ->required()
                                    ->suffix('تومان')
                                    ->helperText('خریدهای بالاتر از این مبلغ شامل ارسال رایگان خواهند شد'),

                                Toggle::make('is_store_open')
                                    ->label('وضعیت فعال بودن فروشگاه (امکان ثبت سفارش)')
                                    ->default(true)
                                    ->helperText('در صورت خاموش بودن، دکمه خرید در فرانت غیرفعال می‌شود'),

                                Textarea::make('maintenance_message')
                                    ->label('متن اطلاعیه تعطیلی یا تعمیرات')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('شبکه‌های اجتماعی و نمادها')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->columns(2)
                            ->schema([
                                TextInput::make('instagram_url')
                                    ->label('لینک صفحه اینستاگرام')
                                    ->url()
                                    ->placeholder('https://instagram.com/easyshop'),

                                TextInput::make('telegram_url')
                                    ->label('لینک کانال یا پشتیبانی تلگرام')
                                    ->url()
                                    ->placeholder('https://t.me/easyshop'),

                                TextInput::make('whatsapp_url')
                                    ->label('لینک یا شماره واتساپ')
                                    ->url()
                                    ->placeholder('https://wa.me/989123456789'),

                                Textarea::make('enamad_code')
                                    ->label('کد اسکریپت یا لوگوی اینماد (Enamad)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('باشگاه مشتریان و امتیازات')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->columns(2)
                            ->schema([
                                TextInput::make('loyalty_signup_bonus')
                                    ->label('امتیاز هدیه عضویت اولیه مشتری')
                                    ->numeric()
                                    ->required()
                                    ->suffix('امتیاز')
                                    ->helperText('هنگام ثبت‌نام به حساب مشتری واریز می‌شود'),

                                TextInput::make('loyalty_rate_amount_per_point')
                                    ->label('مبلغ خرید به ازای هر ۱ امتیاز پاداش')
                                    ->numeric()
                                    ->required()
                                    ->suffix('تومان')
                                    ->helperText('مثال: هر ۱۰,۰۰۰ تومان خرید موفق = ۱ امتیاز پاداش'),

                                TextInput::make('loyalty_point_redemption_value')
                                    ->label('ارزش هر امتیاز در تبدیل به تخفیف')
                                    ->numeric()
                                    ->required()
                                    ->suffix('تومان')
                                    ->helperText('مثال: هر ۱ امتیاز = ۵۰۰ تومان تخفیف در خرید بعدی'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
