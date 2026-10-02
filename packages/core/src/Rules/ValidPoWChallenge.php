<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Reyhan\Core\Services\Captcha\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidPoWChallenge implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected CaptchaService $captchaService;

    public function __construct(
        protected string $keyField = 'key',
        protected string $elapsedField = 'elapsed_ms',
        ?CaptchaService $captchaService = null
    ) {
        $this->captchaService = $captchaService ?? app(CaptchaService::class);
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $key = (string) ($this->data[$this->keyField] ?? '');
        $nonce = (string) $value;
        $elapsedMs = (int) ($this->data[$this->elapsedField] ?? 0);

        if ($key === '' || $nonce === '') {
            $fail(__('Security verification failed. Please try again.'));

            return;
        }

        if (! $this->captchaService->solve($key, $nonce, $elapsedMs)) {
            $fail(__('Security verification failed. Please try again.'));
        }
    }
}
