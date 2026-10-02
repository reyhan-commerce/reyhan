<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Catalog;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

final class StoreStockAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => PersianNormalizer::normalizeMobile((string) $this->input('mobile')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
        ];
    }
}
