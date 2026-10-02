<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms.active_driver', 'log');
        $this->migrator->addEncrypted('sms.kavenegar_api_key', env('KAVENEGAR_API_KEY', 'test_kavenegar_key'));
        $this->migrator->add('sms.kavenegar_sender', env('KAVENEGAR_SENDER', '10004346'));
        $this->migrator->add('sms.kavenegar_otp_pattern', env('KAVENEGAR_OTP_PATTERN', 'reyhan_verify'));

        $this->migrator->addEncrypted('sms.farazsms_api_key', env('FARAZSMS_API_KEY', 'test_faraz_key'));
        $this->migrator->add('sms.farazsms_sender', env('FARAZSMS_SENDER', '+983000505'));
        $this->migrator->add('sms.farazsms_otp_pattern', env('FARAZSMS_OTP_PATTERN', 'reyhan_otp'));

        $this->migrator->addEncrypted('sms.ghasedak_api_key', env('GHASEDAK_API_KEY', 'test_ghasedak_key'));
        $this->migrator->add('sms.ghasedak_sender', env('GHASEDAK_SENDER', '300002525'));
        $this->migrator->add('sms.ghasedak_otp_template', env('GHASEDAK_OTP_TEMPLATE', 'reyhan_otp'));
    }
};
