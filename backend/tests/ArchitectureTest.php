<?php

declare(strict_types=1);

arch('actions are final and have execute method')
    ->expect('Reyhan\Core\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

arch('dtos are final')
    ->expect('Reyhan\Core\Data')
    ->classes()
    ->toBeFinal();

arch('pipelines and pipes are final')
    ->expect('Reyhan\Core\Pipelines')
    ->classes()
    ->toBeFinal();

arch('domain events are final')
    ->expect('Reyhan\Core\Events')
    ->classes()
    ->toBeFinal();

arch('form requests are final')
    ->expect('Reyhan\Core\Http\Requests')
    ->classes()
    ->toBeFinal();

arch('api controllers are final')
    ->expect('Reyhan\Core\Http\Controllers\Api\V1')
    ->classes()
    ->toBeFinal();

arch('models do not use direct http or request instances')
    ->expect('Reyhan\Core\Models')
    ->not->toUse([
        'Illuminate\Support\Facades\Http',
        'Illuminate\Http\Request',
    ]);

arch('strict no-repository rule')
    ->expect('Reyhan\Core\Repositories')
    ->not->toBeUsed();

arch('strict types are declared across core framework')
    ->expect('Reyhan\Core')
    ->toUseStrictTypes();

