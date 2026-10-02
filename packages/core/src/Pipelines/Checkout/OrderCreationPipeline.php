<?php

declare(strict_types=1);

namespace Reyhan\Core\Pipelines\Checkout;

use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Illuminate\Pipeline\Pipeline;

final class OrderCreationPipeline
{
    /**
     * The standard pipes through which order creation requests pass.
     *
     * @var array<int, class-string>
     */
    protected static array $pipes = [
        VerifyCartStatePipe::class,
        ApplyDynamicPromotionsPipe::class,
        CalculateTaxesAndShippingPipe::class,
        ReserveInventoryMutexPipe::class,
        ExecutePreOrderHooksPipe::class,
        PersistOrderRecordPipe::class,
        InitiatePaymentOrWalletPipe::class,
        FireOrderCreatedEventsPipe::class,
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
     * Get configured pipes.
     *
     * @return array<int, class-string>
     */
    public static function getPipes(): array
    {
        return self::$pipes;
    }

    /**
     * Process the order creation context through the pipeline.
     */
    public function process(OrderCreationContext $context): CreateOrderResultData
    {
        return app(Pipeline::class)
            ->send($context)
            ->through(self::$pipes)
            ->then(fn (OrderCreationContext $ctx): CreateOrderResultData => $ctx->result);
    }
}
