<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Checkout;

use Reyhan\Core\Actions\Checkout\CreateOrderAction;
use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Data\Checkout\CreateOrderData;
use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Reyhan\Core\Models\User;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;

final class CheckoutService
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly OrderCreationPipeline $pipeline,
    ) {}

    /**
     * Process checkout and persist order via the domain pipeline.
     */
    public function process(UserContract|User $user, CreateOrderData $data): CreateOrderResultData
    {
        return $this->createOrderAction->execute($user, $data);
    }

    /**
     * Get the active order creation pipeline.
     */
    public function pipeline(): OrderCreationPipeline
    {
        return $this->pipeline;
    }

    /**
     * Allow plugins to prepend a pipe to the order creation pipeline.
     *
     * @param  class-string  $pipe
     */
    public function prependPipe(string $pipe): void
    {
        OrderCreationPipeline::prependPipe($pipe);
    }

    /**
     * Allow plugins to append a pipe to the order creation pipeline.
     *
     * @param  class-string  $pipe
     */
    public function appendPipe(string $pipe): void
    {
        OrderCreationPipeline::appendPipe($pipe);
    }
}
