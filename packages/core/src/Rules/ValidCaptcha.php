<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Reyhan\Core\Services\Captcha\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCaptcha implements ValidationRule
{
    protected CaptchaService $captchaService;

    public function __construct(?CaptchaService $captchaService = null)
    {
        $this->captchaService = $captchaService ?? app(CaptchaService::class);
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            $fail(__('Security challenge is invalid or expired. Please click the checkbox again.'));

            return;
        }

        if (! $this->captchaService->verify($value)) {
            $fail(__('Security challenge is invalid or expired. Please click the checkbox again.'));
        }
    }
}
