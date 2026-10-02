<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Content;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

final class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('mobile')) {
            $merge['mobile'] = PersianNormalizer::normalizeMobile((string) $this->input('mobile'));
        }

        if ($this->has('name')) {
            $merge['name'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('name'));
        }

        if ($this->has('subject') && $this->input('subject') !== null) {
            $merge['subject'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('subject'));
        }

        if ($this->has('message')) {
            $merge['message'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('message'));
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^09[0-9]{9}$/'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ];
    }
}
