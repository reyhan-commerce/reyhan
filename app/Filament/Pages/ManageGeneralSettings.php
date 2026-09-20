<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'تنظیمات عمومی';

    protected static ?string $title = 'تنظیمات عمومی فروشگاه';

    protected static ?int $navigationSort = 1;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات اصلی فروشگاه')
                    ->description('اطلاعات پایه و هویتی فروشگاه ایزیشاپ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('نام فروشگاه')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('store_slogan')
                            ->label('شعار فروشگاه')
                            ->maxLength(255),

                        FileUpload::make('store_logo')
                            ->label('لوگوی فروشگاه')
                            ->image()
                            ->directory('settings'),

                        FileUpload::make('store_favicon')
                            ->label('آیکون وب‌سایت (Favicon)')
                            ->image()
                            ->directory('settings'),
                    ]),

                Section::make('راه‌های ارتباطی و آدرس')
                    ->description('اطلاعات تماس جهت نمایش در فوتر و صفحه ارتباط با ما')
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
                            ->label('کد پستی')
                            ->maxLength(20),

                        Textarea::make('address')
                            ->label('آدرس فیزیکی')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('قوانین فروش و سفارش')
                    ->description('تنظیمات هزینه ارسال و وضعیت دسترسی به فروشگاه')
                    ->columns(2)
                    ->schema([
                        TextInput::make('free_shipping_threshold')
                            ->label('سقف خرید برای ارسال رایگان (تومان)')
                            ->numeric()
                            ->required()
                            ->prefix('تومان'),

                        Toggle::make('is_store_open')
                            ->label('فروشگاه فعال است (امکان ثبت سفارش)')
                            ->default(true),

                        Textarea::make('maintenance_message')
                            ->label('پیام عدم پذیرش سفارش یا تعمیرات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('شبکه‌های اجتماعی و نمادها')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')
                            ->label('لینک صفحه اینستاگرام')
                            ->url(),

                        TextInput::make('telegram_url')
                            ->label('لینک کانال تلگرام')
                            ->url(),

                        Textarea::make('enamad_code')
                            ->label('کد لوگوی اینماد')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
