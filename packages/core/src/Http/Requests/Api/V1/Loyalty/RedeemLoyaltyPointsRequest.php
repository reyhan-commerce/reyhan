<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Loyalty;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

final class RedeemLoyaltyPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('points') && $this->input('points') !== null) {
            $this->merge([
                'points' => (int) PersianNormalizer::normalizeNumber((string) $this->input('points')),
            ]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:10'],
        ];
    }
}
