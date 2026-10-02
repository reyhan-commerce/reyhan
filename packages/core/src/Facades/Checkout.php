<?php

declare(strict_types=1);

namespace Reyhan\Core\Facades;

use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Data\Checkout\CreateOrderData;
use Reyhan\Core\Data\Checkout\CreateOrderResultData;
use Reyhan\Core\Models\User;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;
use Reyhan\Core\Services\Checkout\CheckoutService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CreateOrderResultData process(UserContract|User $user, CreateOrderData $data)
 * @method static OrderCreationPipeline pipeline()
 * @method static void prependPipe(string $pipe)
 * @method static void appendPipe(string $pipe)
 *
 * @see \Reyhan\Core\Services\Checkout\CheckoutService
 */
final class Checkout extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CheckoutService::class;
    }
}
