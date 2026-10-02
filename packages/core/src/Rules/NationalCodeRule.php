<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class NationalCodeRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian 10-digit National Code (کد ملی).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail(__('validation.custom.national_code.invalid', ['attribute' => $attribute]));

            return;
        }

        $code = str_pad((string) $value, 10, '0', STR_PAD_LEFT);

        if (! preg_match('/^\d{10}$/', $code)) {
            $fail('کد ملی وارد شده باید یک عدد ۱۰ رقمی معتبر باشد.');

            return;
        }

        // Check for identical repeating digits (e.g. 0000000000, 1111111111)
        for ($i = 0; $i < 10; $i++) {
            if ($code === str_repeat((string) $i, 10)) {
                $fail('کد ملی وارد شده نامعتبر است.');

                return;
            }
        }

        $digits = array_map('intval', str_split($code));
        $checkDigit = $digits[9];

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }

        $remainder = $sum % 11;
        $isValid = ($remainder < 2 && $checkDigit === $remainder) || ($remainder >= 2 && $checkDigit === (11 - $remainder));

        if (! $isValid) {
            $fail('کد ملی وارد شده بر اساس الگوریتم سازمان ثبت احوال نامعتبر است.');
        }
    }
}
