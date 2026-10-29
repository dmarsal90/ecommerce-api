<?php

use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\AuthRateLimiter;
use App\Http\Middleware\CartToken;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.rate-limiter' => AuthRateLimiter::class,
            'api.rate-limiter' => ApiRateLimiter::class,
            'security.headers' => SecurityHeaders::class,
            'cart.token' => CartToken::class,
        ]);

        $middleware->group('api', [
            'api.rate-limiter',
            'security.headers',
        ]);

        $middleware->group('auth', [
            'auth.rate-limiter',
            'security.headers',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
