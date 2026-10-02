<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class PostalCodeRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian 10-digit Postal Code (کد پستی).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('کد پستی باید به صورت عددی وارد شود.');

            return;
        }

        $code = str_replace([' ', '-'], '', (string) $value);

        if (! preg_match('/^\d{10}$/', $code)) {
            $fail('کد پستی باید دقیقا ۱۰ رقم عددی بدون خط تیره یا فاصله باشد.');

            return;
        }

        // Iranian postal code rules:
        // 1. Cannot start with 0 or 2
        // 2. The 5th digit cannot be 0
        if (in_array($code[0], ['0', '2'], true)) {
            $fail('کد پستی معتبر نمی‌تواند با ارقام ۰ یا ۲ شروع شود.');

            return;
        }

        if ($code[4] === '0') {
            $fail('رقم پنجم کد پستی نمی‌تواند عدد ۰ باشد.');

            return;
        }

        // Check for identical repeating digits
        for ($i = 0; $i < 10; $i++) {
            if ($code === str_repeat((string) $i, 10)) {
                $fail('کد پستی وارد شده نامعتبر است.');

                return;
            }
        }
    }
}
