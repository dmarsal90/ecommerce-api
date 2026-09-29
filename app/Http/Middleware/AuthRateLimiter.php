<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'auth:'.$request->ip();
        $maxAttempts = 10;
        $decayMinutes = 15;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        if ($response->getStatusCode() < 400) {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
