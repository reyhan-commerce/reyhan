<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Review;
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
        $userName = 'کاربر گرامی';
        if ($this->user) {
            $firstName = $this->user->first_name ?: '';
            $lastName = $this->user->last_name ?: '';
            if ($firstName || $lastName) {
                // Privacy masking: e.g. "فاطمه ر."
                $lastInitial = $lastName ? mb_substr($lastName, 0, 1) . '.' : '';
                $userName = trim("{$firstName} {$lastInitial}") ?: $this->user->full_name;
            }
        }

        return [
            'id' => $this->id,
            'user_name' => $userName,
            'rating' => $this->rating,
            'longevity_rating' => $this->longevity_rating,
            'coverage_rating' => $this->coverage_rating,
            'value_rating' => $this->value_rating,
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
