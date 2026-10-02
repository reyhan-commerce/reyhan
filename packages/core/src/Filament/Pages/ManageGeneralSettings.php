<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Pages;

use Reyhan\Core\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
                                    ->placeholder('https://instagram.com/reyhan'),

                                TextInput::make('telegram_url')
                                    ->label('لینک کانال یا پشتیبانی تلگرام')
                                    ->url()
                                    ->placeholder('https://t.me/reyhan'),

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

                        Tab::make('نوار اعلان و هدر')
                            ->icon(Heroicon::OutlinedMegaphone)
                            ->columns(2)
                            ->schema([
                                Toggle::make('announcement_enabled')
                                    ->label('نمایش نوار اعلان بالای سایت')
                                    ->default(true)
                                    ->columnSpanFull(),

                                TextInput::make('announcement_text')
                                    ->label('متن نوار اعلان')
                                    ->placeholder('ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان • تضمین ۱۰۰٪ اصالت کالا')
                                    ->columnSpanFull(),

                                TextInput::make('announcement_link')
                                    ->label('لینک مقصد نوار اعلان (اختیاری)')
                                    ->placeholder('/products یا https://...')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('صفحه اصلی و سکشن‌ها')
                            ->icon(Heroicon::OutlinedHome)
                            ->columns(2)
                            ->schema([
                                TextInput::make('hero_badge_text')
                                    ->label('متن نشان بالای هیرو')
                                    ->placeholder('تخفیف‌های ویژه و محصولات برگزیده'),

                                TextInput::make('hero_primary_button_text')
                                    ->label('متن دکمه اصلی هیرو')
                                    ->placeholder('مشاهده کل کاتالوگ'),

                                TextInput::make('hero_secondary_button_text')
                                    ->label('متن دکمه دوم هیرو')
                                    ->placeholder('دسته‌بندی‌های کالا'),

                                TextInput::make('categories_title')
                                    ->label('عنوان بخش دسته‌بندی‌ها')
                                    ->placeholder('دسته‌بندی‌های تخصصی'),

                                TextInput::make('categories_button_text')
                                    ->label('متن دکمه بخش دسته‌بندی‌ها')
                                    ->placeholder('مشاهده نقشه کامل'),

                                TextInput::make('flash_deals_title')
                                    ->label('عنوان بخش شگفت‌انگیزها')
                                    ->placeholder('پیشنهادات شگفت‌انگیز روز'),

                                TextInput::make('flash_deals_subtitle')
                                    ->label('زیرعنوان بخش شگفت‌انگیزها')
                                    ->placeholder('فرصت محدود با تخفیف‌های ویژه تا پایان امروز'),

                                TextInput::make('featured_products_title')
                                    ->label('عنوان بخش محصولات برگزیده')
                                    ->placeholder('محصولات برگزیده فروشگاه'),

                                TextInput::make('featured_products_button_text')
                                    ->label('متن دکمه محصولات برگزیده')
                                    ->placeholder('مشاهده همه کاتالوگ'),

                                TextInput::make('blog_title')
                                    ->label('عنوان بخش مقالات و وبلاگ')
                                    ->placeholder('مجله تخصصی و تازه‌ترین مقالات'),

                                TextInput::make('blog_button_text')
                                    ->label('متن دکمه وبلاگ')
                                    ->placeholder('ورود به وبلاگ'),

                                TextInput::make('brands_title')
                                    ->label('عنوان بخش برندها')
                                    ->placeholder('اصیل‌ترین برندهای معتبر جهانی و ایرانی'),

                                Repeater::make('trust_badges')
                                    ->label('نشان‌های اعتماد و تعهدات فروشگاه (Trust Badges)')
                                    ->schema([
                                        TextInput::make('icon')
                                            ->label('آیکون (Lucide)')
                                            ->placeholder('i-lucide-shield-check')
                                            ->required(),

                                        TextInput::make('title')
                                            ->label('عنوان شاخص')
                                            ->placeholder('ضمانت ۱۰۰٪ اصالت کالا')
                                            ->required(),

                                        TextInput::make('desc')
                                            ->label('توضیح کوتاه')
                                            ->placeholder('تمامی کالاها با برچسب اصالت')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('فوتر و حقوقی')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columns(2)
                            ->schema([
                                Textarea::make('footer_about_text')
                                    ->label('متن معرفی درباره ما در فوتر')
                                    ->helperText('اگر خالی باشد از شعار فروشگاه استفاده می‌شود.')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('footer_copyright_text')
                                    ->label('متن کپی‌رایت انتهای فوتر')
                                    ->placeholder('تمامی حقوق مادی و معنوی محفوظ می‌باشد.')
                                    ->columnSpanFull(),

                                TextInput::make('footer_designer_credit')
                                    ->label('متن امضای طراحی و توسعه')
                                    ->placeholder('طراحی شده با رعایت استانداردهای تجربه کاربری و تجارت الکترونیک')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('مشتریان، عودت و بازاریابی')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->columns(2)
                            ->schema([
                                TextInput::make('referral_reward_toman')
                                    ->label('مبلغ پاداش معرفی هر دوست')
                                    ->numeric()
                                    ->required()
                                    ->suffix('تومان')
                                    ->helperText('پس از اولین خرید موفق دوست به کیف پول معرف واریز می‌شود.'),

                                TextInput::make('return_guarantee_days')
                                    ->label('مهلت ضمانت بازگشت کالا')
                                    ->numeric()
                                    ->required()
                                    ->suffix('روز')
                                    ->helperText('تعداد روزهای مجاز پس از تحویل برای ثبت RMA.'),

                                TextInput::make('referral_banner_title')
                                    ->label('عنوان بنر معرفی دوستان')
                                    ->placeholder('دوستانت را دعوت کن، هدیه نقدی بگیر!')
                                    ->columnSpanFull(),

                                Textarea::make('referral_banner_desc')
                                    ->label('توضیحات بنر معرفی دوستان')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Textarea::make('return_policy_notice')
                                    ->label('متن قوانین و شرایط مرجوعی کالا')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Textarea::make('tax_invoice_notice')
                                    ->label('توضیحات قانونی فاکتور رسمی (ماده ۱۹)')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('support_work_hours_notice')
                                    ->label('متن ساعات پاسخگویی پشتیبانی')
                                    ->placeholder('شنبه تا چهارشنبه ۹ الی ۱۸ | پنج‌شنبه‌ها ۹ الی ۱۴')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
