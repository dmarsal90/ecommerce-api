<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Uses the storefront's active language (Accept-Language) for anything translated while handling the request, such as emails. */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->headers->has('Accept-Language')) {
            app()->setLocale($request->getPreferredLanguage(config('app.supported_locales')));
        }

        return $next($request);
    }
}
