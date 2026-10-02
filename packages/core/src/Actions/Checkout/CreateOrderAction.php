<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Checkout;

use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Data\Checkout\CreateOrderData;
use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Reyhan\Core\Models\User;
use Reyhan\Core\Pipelines\Checkout\OrderCreationContext;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;

final class CreateOrderAction
{
    public function __construct(
        protected OrderCreationPipeline $pipeline,
    ) {}

    /**
     * Execute checkout and order creation through extensible pipeline.
     */
    public function execute(
        UserContract|User $user,
        CreateOrderData $data,
    ): CreateOrderResultData {
        $context = new OrderCreationContext(
            user: $user,
            data: $data,
        );

        return $this->pipeline->process($context);
    }
}
