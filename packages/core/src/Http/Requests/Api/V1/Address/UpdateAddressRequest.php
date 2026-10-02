<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Address;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Reyhan\Core\Rules\IranianMobileRule;
use Reyhan\Core\Rules\PostalCodeRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('recipient_mobile')) {
            $merge['recipient_mobile'] = PersianNormalizer::normalizeMobile((string) $this->input('recipient_mobile'));
        }

        if ($this->has('postal_code')) {
            $merge['postal_code'] = PersianNormalizer::normalizeNumber((string) $this->input('postal_code'));
        }

        if ($this->has('recipient_name')) {
            $merge['recipient_name'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('recipient_name'));
        }

        if ($this->has('address_line')) {
            $merge['address_line'] = PersianNormalizer::normalizeSearchQuery((string) $this->input('address_line'));
        }

        if ($this->has('building_number') && $this->input('building_number') !== null) {
            $merge['building_number'] = PersianNormalizer::normalizeNumber((string) $this->input('building_number'));
        }

        if ($this->has('unit') && $this->input('unit') !== null) {
            $merge['unit'] = PersianNormalizer::normalizeNumber((string) $this->input('unit'));
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
            'province_id' => ['sometimes', 'integer', 'exists:provinces,id'],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'recipient_name' => ['sometimes', 'string', 'max:100'],
            'recipient_mobile' => ['sometimes', 'string', new IranianMobileRule],
            'postal_code' => ['sometimes', 'string', new PostalCodeRule],
            'address_line' => ['sometimes', 'string', 'max:500'],
            'building_number' => ['nullable', 'string', 'max:20'],
            'unit' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
