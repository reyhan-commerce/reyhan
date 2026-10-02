<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Pricing;

use Reyhan\Core\Data\Pricing\CartPricingData;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Pipelines\Cart\CartCalculationContext;
use Reyhan\Core\Pipelines\Cart\CartCalculationPipeline;

final class PricingService
{
    public function __construct(
        protected CartCalculationPipeline $pipeline,
    ) {}

    /**
     * Compute full pricing breakdown for a given Cart through the pipeline.
     */
    public function calculateCart(
        Cart $cart,
        ?City $destinationCity = null,
        ShippingMethod|\Reyhan\Core\Enums\ShippingMethod|int|string|null $shippingMethod = null
    ): CartPricingData {
        $context = new CartCalculationContext(
            cart: $cart,
            destinationCity: $destinationCity,
            shippingMethod: $shippingMethod,
        );

        return $this->pipeline->process($context);
    }

    /**
     * Get the active cart calculation pipeline.
     */
    public function pipeline(): CartCalculationPipeline
    {
        return $this->pipeline;
    }

    /**
     * Allow plugins to prepend custom pricing calculation pipes.
     *
     * @param  class-string  $pipe
     */
    public function prependPipe(string $pipe): void
    {
        CartCalculationPipeline::prependPipe($pipe);
    }

    /**
     * Allow plugins to append custom pricing calculation pipes.
     *
     * @param  class-string  $pipe
     */
    public function appendPipe(string $pipe): void
    {
        CartCalculationPipeline::appendPipe($pipe);
    }
}
