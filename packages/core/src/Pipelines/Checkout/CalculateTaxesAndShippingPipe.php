<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Services\Pricing\PricingService;
use Reyhan\Core\Services\Shipping\ShippingService;
use Reyhan\Core\Services\Wallet\WalletService;
use BackedEnum;
use Closure;

final class CalculateTaxesAndShippingPipe
{
    public function __construct(
        protected PricingService $pricingService,
        protected ShippingService $shippingService,
        protected WalletService $walletService,
    ) {}

    /**
     * @param  Closure(OrderCreationContext): mixed  $next
     */
    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        $shippingMethodInput = $context->data->shippingMethodId ?? $context->data->shippingMethod ?? 'pishtaz';

        $pricing = $this->pricingService->calculateCart(
            $context->cart,
            $context->address?->city,
            $shippingMethodInput
        );

        $context->pricing = $pricing;
        $context->shippingFee = $pricing->shippingFee;
        $context->finalPayable = $pricing->finalPayable;
        $context->shippingMethodId = $pricing->shippingMethodId;
        $context->shippingMethodCode = is_string($context->data->shippingMethod)
            ? $context->data->shippingMethod
            : ($context->data->shippingMethod instanceof BackedEnum
                ? $context->data->shippingMethod->value
                : ($pricing->shippingMethodTitle ? 'express' : 'pishtaz'));

        // Calculate Wallet Deduction
        $context->walletDeduction = 0;
        if ($context->data->useWallet) {
            $userBalance = $this->walletService->getBalance($context->user);
            $context->walletDeduction = min($context->finalPayable, $userBalance);
        }

        $context->remainingPayable = $context->finalPayable - $context->walletDeduction;

        return $next($context);
    }
}
