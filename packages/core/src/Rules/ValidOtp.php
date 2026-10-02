<?php

declare(strict_types=1);

namespace Reyhan\Core\Rules;

use Reyhan\Core\Services\Otp\OtpService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidOtp implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected OtpService $otpService;

    public function __construct(
        protected string $mobileField = 'mobile',
        ?OtpService $otpService = null
    ) {
        $this->otpService = $otpService ?? app(OtpService::class);
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
        $mobile = (string) ($this->data[$this->mobileField] ?? '');
        $code = (string) $value;

        if (empty($mobile) || empty($code)) {
            $fail(__('Verification code is invalid or has expired.'));

            return;
        }

        // Verify and automatically consume/clear OTP in Redis
        if (! $this->otpService->verify($mobile, $code)) {
            $fail(__('Verification code is invalid or has expired.'));
        }
    }
}
