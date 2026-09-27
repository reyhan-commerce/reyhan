<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'shipping_method' => $this->shipping_method?->value,
            'shipping_method_title' => $this->shipping_method?->title(),
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'items_subtotal' => $this->items_subtotal,
            'discount_amount' => $this->discount_amount,
            'coupon_discount' => $this->coupon_discount,
            'coupon_code' => $this->coupon_code,
            'shipping_fee' => $this->shipping_fee,
            'final_payable' => $this->final_payable,
            'items_count' => $this->items_count ?? $this->items->count(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => $this->whenLoaded('user', fn () => [
                'name' => $this->user?->full_name,
                'mobile' => $this->user?->mobile,
            ]),
        ];
    }
}
