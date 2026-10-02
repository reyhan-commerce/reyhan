<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userName = __('Dear User');
        if ($this->user) {
            $firstName = $this->user->first_name ?: '';
            $lastName = $this->user->last_name ?: '';
            if ($firstName || $lastName) {
                // Privacy masking: e.g. "فاطمه ر."
                $lastInitial = $lastName ? mb_substr($lastName, 0, 1).'.' : '';
                $userName = trim("{$firstName} {$lastInitial}") ?: $this->user->full_name;
            }
        }

        return [
            'id' => $this->id,
            'user_name' => $userName,
            'rating' => $this->rating,
            'criteria_ratings' => $this->criteria_ratings ?: [
                'longevity' => $this->longevity_rating ?? 5,
                'coverage' => $this->coverage_rating ?? 5,
                'value' => $this->value_rating ?? 5,
            ],
            'longevity_rating' => $this->longevity_rating ?? ($this->criteria_ratings['longevity'] ?? 5),
            'coverage_rating' => $this->coverage_rating ?? ($this->criteria_ratings['coverage'] ?? 5),
            'value_rating' => $this->value_rating ?? ($this->criteria_ratings['value'] ?? 5),
            'comment' => $this->comment,
            'strengths' => $this->strengths ?: [],
            'weaknesses' => $this->weaknesses ?: [],
            'is_verified_purchase' => $this->is_verified_purchase,
            'status' => $this->status->value,
            'admin_reply' => $this->admin_reply,
            'admin_reply_at' => $this->admin_reply_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];

    }
}
