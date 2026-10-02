<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Resources\V1;

use Reyhan\Core\Enums\ShippingMethod as ShippingMethodEnum;
use Reyhan\Core\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

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
        $shippingTitle = $this->shippingMethod?->name;
        if (! $shippingTitle) {
            $shippingTitle = $this->shipping_method instanceof ShippingMethodEnum
                ? $this->shipping_method->title()
                : ($this->shipping_method ?? 'پست پیشتاز');
        }

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'shipping_method' => $this->shipping_method instanceof ShippingMethodEnum ? $this->shipping_method->value : $this->shipping_method,
            'shipping_method_id' => $this->shipping_method_id,
            'shipping_method_title' => $shippingTitle,
            'shipping_method_icon' => $this->shippingMethod?->icon ?? 'i-lucide-truck',
            'tracking_code' => $this->tracking_code,
            'tracking_url' => $this->tracking_url,
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'delivery_date_jalali' => $this->delivery_date ? Jalalian::fromCarbon($this->delivery_date)->format('Y/m/d') : null,
            'delivery_time_slot' => $this->delivery_time_slot,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'items_subtotal' => $this->items_subtotal,
            'original_items_subtotal' => $this->items_subtotal + ($this->discount_amount ?? 0),
            'discount_amount' => $this->discount_amount,
            'coupon_discount' => $this->coupon_discount,
            'coupon_code' => $this->coupon_code,
            'shipping_fee' => $this->shipping_fee,
            'tax_amount' => $this->tax_amount ?? (int) round(($this->items_subtotal - ($this->coupon_discount ?? 0)) * 0.10),
            'wallet_paid_amount' => $this->wallet_paid_amount ?? 0,
            'final_payable' => $this->final_payable,
            'remaining_payable' => max(0, $this->final_payable - ($this->wallet_paid_amount ?? 0)),
            'is_corporate_invoice' => (bool) $this->is_corporate_invoice,
            'corporate_data' => $this->corporate_data,
            'card_receipt' => $this->cardTransferReceipt ? [
                'status' => $this->cardTransferReceipt->status,
                'tracking_number' => $this->cardTransferReceipt->tracking_number,
                'source_card_number' => $this->cardTransferReceipt->source_card_number,
            ] : null,
            'items_count' => $this->items_count ?? $this->items->count(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'shipped_at_jalali' => $this->shipped_at ? Jalalian::fromCarbon($this->shipped_at)->format('Y/m/d H:i') : null,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_jalali' => Jalalian::fromCarbon($this->created_at)->format('Y/m/d H:i'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => $this->whenLoaded('user', fn () => [
                'name' => $this->user?->full_name,
                'mobile' => $this->user?->mobile,
            ]),
        ];
    }
}
