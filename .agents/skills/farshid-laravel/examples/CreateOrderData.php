<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Http\Requests\Orders\StoreOrderRequest;

final readonly class CreateOrderData
{
    /**
     * @param array<int, array{product_id: int, quantity: int, unit_price: int}> $items
     */
    public function __construct(
        public int $userId,
        public array $items,
        public string $paymentMethod,
        public ?string $couponCode = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            items: (array) $request->validated('items'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code'),
            notes: $request->validated('notes'),
        );
    }

    /**
     * Calculate the gross subtotal for all order items.
     */
    public function calculateSubtotal(): int
    {
        return array_reduce(
            $this->items,
            fn (int $carry, array $item) => $carry + ($item['quantity'] * $item['unit_price']),
            0,
        );
    }
}
