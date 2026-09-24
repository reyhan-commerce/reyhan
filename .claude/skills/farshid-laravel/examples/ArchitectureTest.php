<?php

declare(strict_types=1);

// -----------------------------------------------------------------------------
// Pest 3 Architecture Test Suite — Enforcing Farshid's Laravel Standards
// -----------------------------------------------------------------------------

test('all action classes are final and have execute method')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

test('repository pattern is strictly banned')
    ->expect('App\Repositories')
    ->not->toBeUsed();

test('dtos are final and readonly')
    ->expect('App\Data')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

test('models extend standard eloquent model and do not call external http')
    ->expect('App\Models')
    ->classes()
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->not->toUse('Illuminate\Support\Facades\Http');

test('controllers do not call DB transaction directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

test('jobs implement shouldQueue')
    ->expect('App\Jobs')
    ->classes()
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

test('strict php code quality preset')
    ->expect('App')
    ->toUseStrictTypes()
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);
