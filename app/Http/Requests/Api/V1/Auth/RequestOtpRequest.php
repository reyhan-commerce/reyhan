<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation (normalize mobile to standard 11-digit ASCII).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => PersianNormalizer::normalizeMobile((string) $this->input('mobile')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'captcha_token' => ['required', 'string'],
        ];
    }
}
