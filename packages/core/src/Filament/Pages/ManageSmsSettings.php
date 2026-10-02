<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Pages;

use Reyhan\Core\Settings\SmsSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSmsSettings extends SettingsPage
{
    protected static string $settings = SmsSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'تنظیمات پیامک';

    protected static ?string $title = 'مدیریت ارائه‌دهندگان و درگاه پیامک';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات سیستم';

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('درایور پیش‌فرض ارسال پیامک')
                    ->description('انتخاب سرویس‌دهنده فعال جهت ارسال کدهای ورود OTP و اعلان‌های خرید')
                    ->schema([
                        Select::make('active_driver')
                            ->label('ارائه‌دهنده فعال پیامک')
                            ->options([
                                'kavenegar' => 'کاوه‌نگار (Kavenegar)',
                                'farazsms' => 'فراز اس‌ام‌اس (FarazSMS / IPPanel)',
                                'ghasedak' => 'قاصدک (Ghasedak)',
                                'log' => 'ثبت در لاگ (محیط توسعه و تست)',
                            ])
                            ->required(),
                    ]),

                Tabs::make('ارائه‌دهندگان پیامک')
                    ->tabs([
                        Tab::make('کاوه‌نگار (Kavenegar)')
                            ->icon(Heroicon::OutlinedDevicePhoneMobile)
                            ->columns(2)
                            ->schema([
                                TextInput::make('kavenegar_api_key')
                                    ->label('کلید API کاوه‌نگار')
                                    ->password()
                                    ->revealable(),

                                TextInput::make('kavenegar_sender')
                                    ->label('شماره خط اختصاصی ارسال‌کننده')
                                    ->maxLength(50),

                                TextInput::make('kavenegar_otp_pattern')
                                    ->label('نام الگوی اعتبارسنجی (Lookup Pattern)')
                                    ->maxLength(100),
                            ]),

                        Tab::make('فراز اس‌ام‌اس (IPPanel)')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->columns(2)
                            ->schema([
                                TextInput::make('farazsms_api_key')
                                    ->label('کلید API / نام کاربری فراز')
                                    ->password()
                                    ->revealable(),

                                TextInput::make('farazsms_sender')
                                    ->label('شماره خط ارسال‌کننده')
                                    ->maxLength(50),

                                TextInput::make('farazsms_otp_pattern')
                                    ->label('کد الگوی ارسال سریع (Pattern Code)')
                                    ->maxLength(100),
                            ]),

                        Tab::make('قاصدک (Ghasedak)')
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->columns(2)
                            ->schema([
                                TextInput::make('ghasedak_api_key')
                                    ->label('کلید API قاصدک')
                                    ->password()
                                    ->revealable(),

                                TextInput::make('ghasedak_sender')
                                    ->label('شماره خط ارسال‌کننده')
                                    ->maxLength(50),

                                TextInput::make('ghasedak_otp_template')
                                    ->label('نام قالب اعتبارسنجی (Template Name)')
                                    ->maxLength(100),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
