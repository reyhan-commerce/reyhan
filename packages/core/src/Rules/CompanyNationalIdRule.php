<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class CompanyNationalIdRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian 11-digit Corporate National ID (شناسه ملی اشخاص حقوقی).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('شناسه ملی شرکت باید ۱۱ رقم عددی باشد.');

            return;
        }

        $code = trim((string) $value);

        // Normalize digits
        $code = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $code
        );

        if (! preg_match('/^\d{11}$/', $code)) {
            $fail('شناسه ملی اشخاص حقوقی باید دقیقا ۱۱ رقم عددی باشد.');

            return;
        }

        $digits = array_map('intval', str_split($code));
        $checkDigit = $digits[10];
        $tenthDigitPlusTwo = $digits[9] + 2;

        $weights = [29, 27, 23, 19, 17, 29, 27, 23, 19, 17];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += ($digits[$i] + $tenthDigitPlusTwo) * $weights[$i];
        }

        $remainder = $sum % 11;
        if ($remainder === 10) {
            $remainder = 0;
        }

        if ($checkDigit !== $remainder) {
            $fail('شناسه ملی اشخاص حقوقی وارد شده بر اساس الگوریتم ثبت شرکت‌ها نامعتبر است.');
        }
    }
}
