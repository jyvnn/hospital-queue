<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strip response bodies from HTTP redirects (3xx) to avoid returning content
 * in responses where a Location header is intended to be followed.
 */
class StripRedirectBody
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! ($response instanceof Response)) {
            return $response;
        }

        if ($response->isRedirection()) {
            // replace body with an empty payload and set a safe Content-Type
            $response->setContent('');
            $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
            if ($response->headers->has('Content-Length')) {
                $response->headers->remove('Content-Length');
            }
        }

        return $response;
    }
}
