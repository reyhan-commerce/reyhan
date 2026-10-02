<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Data\Checkout\CreateOrderData;
use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Reyhan\Core\Data\Pricing\CartPricingData;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Inventory\StockReservationService;

final class OrderCreationContext
{
    public function __construct(
        public UserContract|User $user,
        public CreateOrderData $data,
        public ?Address $address = null,
        public ?Cart $cart = null,
        public ?CartPricingData $pricing = null,
        public ?string $shippingMethodCode = null,
        public ?int $shippingMethodId = null,
        public int $shippingFee = 0,
        public int $finalPayable = 0,
        public int $walletDeduction = 0,
        public int $remainingPayable = 0,
        public string $reservationId = '',
        /** @var array<int, int> variantId => quantity */
        public array $reservedVariantIds = [],
        public ?Order $order = null,
        public ?Payment $payment = null,
        public ?CreateOrderResultData $result = null,
        /** @var array<string, mixed> Extensibility payload for plugins */
        public array $customData = [],
    ) {}

    /**
     * Release all active Redis reservations held in this context.
     */
    public function rollbackReservations(StockReservationService $stockReservationService): void
    {
        if ($this->reservationId === '' || empty($this->reservedVariantIds)) {
            return;
        }

        foreach ($this->reservedVariantIds as $variantId => $qty) {
            $stockReservationService->release($variantId, $qty, $this->reservationId);
        }

        $this->reservedVariantIds = [];
    }
}
