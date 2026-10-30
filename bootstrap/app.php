<?php

use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\AuthRateLimiter;
use App\Http\Middleware\CartToken;
use App\Http\Middleware\NoStore;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

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
            'no-store' => NoStore::class,
            'set.locale' => SetLocale::class,
        ]);

        $middleware->group('api', [
            'set.locale',
            'api.rate-limiter',
            'security.headers',
        ]);

        $middleware->group('auth', [
            'auth.rate-limiter',
            'security.headers',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Never leak Stripe error details to clients; log only identifiers useful for support.
        $exceptions->render(function (ApiErrorException $e, Request $request) {
            Log::error('Stripe API error', ['type' => $e::class, 'code' => $e->getStripeCode(), 'request_id' => $e->getRequestId()]);

            return response()->json(['message' => 'The payment provider is unavailable. Please try again.'], 502);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
