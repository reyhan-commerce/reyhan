<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class IranianMobileRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian Mobile Numbers (شماره موبایل ایران).
     * Supports formats: 0912xxxxxxx, +98912xxxxxxx, 0098912xxxxxxx, 98912xxxxxxx, 912xxxxxxx.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('شماره موبایل باید به صورت متنی یا عددی وارد شود.');

            return;
        }

        $mobile = trim((string) $value);

        // Normalize Persian/Arabic digits to English digits
        $mobile = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $mobile
        );

        if (! preg_match('/^(?:(?:\+|00)?98|0)?9\d{9}$/', $mobile)) {
            $fail('شماره موبایل وارد شده نامعتبر است. نمونه معتبر: 09123456789');
        }
    }
}
