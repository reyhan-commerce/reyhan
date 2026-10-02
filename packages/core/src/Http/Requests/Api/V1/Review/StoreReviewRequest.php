<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Review;

use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

final class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('comment') && $this->input('comment') !== null) {
            $this->merge([
                'comment' => PersianNormalizer::normalizeSearchQuery((string) $this->input('comment')),
            ]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'criteria_ratings' => ['nullable', 'array'],
            'criteria_ratings.*' => ['integer', 'between:1,5'],
            'longevity_rating' => ['nullable', 'integer', 'between:1,5'],
            'coverage_rating' => ['nullable', 'integer', 'between:1,5'],
            'value_rating' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
            'strengths' => ['nullable', 'array', 'max:5'],
            'strengths.*' => ['string', 'max:100'],
            'weaknesses' => ['nullable', 'array', 'max:5'],
            'weaknesses.*' => ['string', 'max:100'],
        ];
    }
}
