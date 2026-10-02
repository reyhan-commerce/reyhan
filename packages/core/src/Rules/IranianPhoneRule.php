<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class IranianPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian Landline Phone Numbers with area code (تلفن ثابت ایران).
     * Format: 11 digits starting with 0 (e.g. 021xxxxxxxx, 031xxxxxxxx, 051xxxxxxxx).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('شماره تلفن ثابت باید به صورت عددی وارد شود.');

            return;
        }

        $phone = trim((string) $value);

        // Normalize Persian/Arabic digits
        $phone = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $phone
        );

        if (! preg_match('/^0[1-8]\d{9}$/', $phone)) {
            $fail('شماره تلفن ثابت وارد شده نامعتبر است. نمونه معتبر با پیش‌شماره: 02188776655');
        }
    }
}
