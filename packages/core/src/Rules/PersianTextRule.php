<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class PersianTextRule implements ValidationRule
{
    /**
     * Run the validation rule for Persian alphabet characters, half-space (\u200C), spaces, and Arabic diacritics.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('مقدار وارد شده باید متن به زبان فارسی باشد.');

            return;
        }

        // Match Persian characters, Persian digits, spaces, half-space, Arabic marks
        if (! preg_match('/^[\x{0600}-\x{06FF}\x{200C}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\s]+$/u', $value)) {
            $fail('فیلد مورد نظر فقط می‌تواند شامل حروف و کاراکترهای زبان فارسی باشد.');
        }
    }
}
