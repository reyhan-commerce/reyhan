<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class CardNumberRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian 16-digit Bank Card (شماره کارت شتاب).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('شماره کارت باید به صورت عددی وارد شود.');

            return;
        }

        $card = str_replace([' ', '-'], '', (string) $value);

        if (! preg_match('/^\d{16}$/', $card)) {
            $fail('شماره کارت شتاب باید ۱۶ رقم عددی باشد.');

            return;
        }

        // Luhn algorithm validation for 16-digit payment cards
        $sum = 0;
        for ($i = 0; $i < 16; $i++) {
            $digit = (int) $card[$i];
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        if ($sum % 10 !== 0) {
            $fail('شماره کارت وارد شده بر اساس الگوریتم شتاب بانکی نامعتبر است.');
        }
    }
}
