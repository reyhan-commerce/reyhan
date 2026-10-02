<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Address
 */
class AddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient_name' => $this->recipient_name,
            'recipient_mobile' => $this->recipient_mobile,
            'province' => $this->province ? [
                'id' => $this->province->id,
                'name' => $this->province->name,
                'slug' => $this->province->slug,
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
                'slug' => $this->city->slug,
            ] : null,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'building_number' => $this->building_number,
            'unit' => $this->unit,
            'is_default' => (bool) $this->is_default,
            'full_address' => $this->full_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
