<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\EnsureAccountIsActive::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response) {
            if (! in_array($response->getStatusCode(), [403, 404, 500], true)) {
                return $response;
            }

            if (request()->expectsJson()) {
                return $response;
            }

            $page = match ($response->getStatusCode()) {
                403 => 'errors/Forbidden',
                404 => 'errors/NotFound',
                default => 'errors/ServerError',
            };

            return \Inertia\Inertia::render($page)
                ->toResponse(request())
                ->setStatusCode($response->getStatusCode());
        });
    })->create();
