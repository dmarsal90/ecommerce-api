<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies a guest cart by an opaque UUID sent in X-Cart-Token (no cookies, so it works cross-origin).
 * Missing or malformed tokens get a fresh one, always echoed back in the response header.
 */
class CartToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Cart-Token');

        if (! is_string($token) || ! Str::isUuid($token)) {
            $token = (string) Str::uuid();
            $request->headers->set('X-Cart-Token', $token);
        }

        $response = $next($request);
        $response->headers->set('X-Cart-Token', $token);

        return $response;
    }
}
