<?php

declare(strict_types=1);

arch('actions are final and have execute method')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

arch('dtos are final')
    ->expect('App\Data')
    ->classes()
    ->toBeFinal();

arch('form requests are final')
    ->expect('App\Http\Requests')
    ->classes()
    ->toBeFinal();

arch('api controllers are final')
    ->expect('App\Http\Controllers\Api\V1')
    ->classes()
    ->toBeFinal();

arch('strict no-repository rule')
    ->expect('App\Repositories')
    ->not->toBeUsed();

arch('strict types are declared in app')
    ->expect('App')
    ->toUseStrictTypes();
