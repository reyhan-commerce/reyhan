<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Auth;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Reyhan\Core\Rules\ValidOtp;
use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => PersianNormalizer::normalizeMobile((string) $this->input('mobile')),
            ]);
        }

        if ($this->has('code')) {
            $this->merge([
                'code' => PersianNormalizer::normalizeNumber((string) $this->input('code')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'code' => ['required', 'string', 'digits:6', new ValidOtp],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
