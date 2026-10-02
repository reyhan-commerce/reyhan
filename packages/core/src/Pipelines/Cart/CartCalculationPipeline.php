<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Cart;

use Reyhan\Core\Data\Pricing\CartPricingData;
use Illuminate\Pipeline\Pipeline;

final class CartCalculationPipeline
{
    /**
     * The standard default pipes through which cart pricing requests pass.
     *
     * @var array<int, class-string>
     */
    protected static array $defaultPipes = [
        CollectCartItemsPipe::class,
        ApplyCatalogDiscountsPipe::class,
        ApplyCouponsAndPromotionsPipe::class,
        CalculateShippingFeePipe::class,
        CalculateTaxesPipe::class,
        AssemblePricingDataPipe::class,
    ];

    /**
     * Active configured pipes.
     *
     * @var array<int, class-string>
     */
    protected static array $pipes = [
        CollectCartItemsPipe::class,
        ApplyCatalogDiscountsPipe::class,
        ApplyCouponsAndPromotionsPipe::class,
        CalculateShippingFeePipe::class,
        CalculateTaxesPipe::class,
        AssemblePricingDataPipe::class,
    ];

    /**
     * Allow plugins and extensions to append custom pipes.
     *
     * @param  class-string  $pipe
     */
    public static function appendPipe(string $pipe): void
    {
        self::$pipes[] = $pipe;
    }

    /**
     * Allow plugins and extensions to prepend custom pipes.
     *
     * @param  class-string  $pipe
     */
    public static function prependPipe(string $pipe): void
    {
        array_unshift(self::$pipes, $pipe);
    }

    /**
     * Set the entire pipes list.
     *
     * @param  array<int, class-string>  $pipes
     */
    public static function setPipes(array $pipes): void
    {
        self::$pipes = $pipes;
    }

    /**
     * Reset pipes back to framework defaults.
     */
    public static function resetPipes(): void
    {
        self::$pipes = self::$defaultPipes;
    }

    /**
     * Get configured pipes.
     *
     * @return array<int, class-string>
     */
    public static function getPipes(): array
    {
        return self::$pipes;
    }

    /**
     * Process the cart calculation context through the pipeline.
     */
    public function process(CartCalculationContext $context): CartPricingData
    {
        return app(Pipeline::class)
            ->send($context)
            ->through(self::$pipes)
            ->then(fn (CartCalculationContext $ctx): CartPricingData => $ctx->result);
    }
}
