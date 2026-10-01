<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: file_exists(__DIR__.'/../routes/api.php') ? __DIR__.'/../routes/api.php' : null,
        commands: file_exists(__DIR__.'/../routes/console.php') ? __DIR__.'/../routes/console.php' : null,
        channels: file_exists(__DIR__.'/../routes/channels.php') ? __DIR__.'/../routes/channels.php' : null,
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
