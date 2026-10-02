<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class ShebaRule implements ValidationRule
{
    /**
     * Run the validation rule for Iranian IBAN (شماره شبا).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('شماره شبا باید به صورت متنی وارد شود.');

            return;
        }

        $clean = strtoupper(str_replace([' ', '-'], '', $value));

        // Allow entering with or without IR prefix
        if (! str_starts_with($clean, 'IR')) {
            $clean = 'IR'.$clean;
        }

        if (strlen($clean) !== 26 || ! preg_match('/^IR\d{24}$/', $clean)) {
            $fail('شماره شبا باید با IR شروع شده و دارای ۲۴ رقم عددی باشد.');

            return;
        }

        // Standard ISO 7064 Mod 97 validation
        // Move IR + 2 check digits to the end and replace letters with numbers: I=18, R=27
        $rearranged = substr($clean, 4).substr($clean, 0, 4);
        $numericString = str_replace(['I', 'R'], ['18', '27'], $rearranged);

        // Compute large number modulo 97 in chunks
        $remainder = 0;
        $chunks = str_split($numericString, 7);

        foreach ($chunks as $chunk) {
            $remainder = (int) (($remainder.$chunk) % 97);
        }

        if ($remainder !== 1) {
            $fail('شماره شبا وارد شده بر اساس استانداردهای بانکی نامعتبر است.');
        }
    }
}
