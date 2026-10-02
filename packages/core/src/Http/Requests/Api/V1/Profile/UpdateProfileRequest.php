<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Profile;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('national_code') && $this->input('national_code') !== null) {
            $merge['national_code'] = PersianNormalizer::normalizeNationalCode((string) $this->input('national_code'));
        }

        if ($this->has('first_name') && $this->input('first_name') !== null) {
            $merge['first_name'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('first_name'));
        }

        if ($this->has('last_name') && $this->input('last_name') !== null) {
            $merge['last_name'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('last_name'));
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'national_code' => ['nullable', 'digits:10', Rule::unique('users', 'national_code')->ignore($userId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
        ];
    }
}
