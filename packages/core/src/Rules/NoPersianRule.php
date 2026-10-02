<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class NoPersianRule implements ValidationRule
{
    /**
     * Run the validation rule ensuring NO Persian characters are present (e.g. for slugs, usernames, passwords).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (preg_match('/[\x{0600}-\x{06FF}\x{200C}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}]/u', $value)) {
            $fail('فیلد مورد نظر نباید شامل کاراکترها یا حروف فارسی باشد.');
        }
    }
}
