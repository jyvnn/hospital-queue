<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * Ensure all Set-Cookie headers include the HttpOnly flag.
 */
class EnsureHttpOnlyCookies
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!($response instanceof Response)) {
            return $response;
        }

        // Extract existing cookies
        $cookies = $response->headers->getCookies();

        if (empty($cookies)) {
            return $response;
        }

        // Remove any existing Set-Cookie headers so we can re-add normalized ones
        $response->headers->remove('Set-Cookie');

        foreach ($cookies as $cookie) {
            // Create a new cookie with HttpOnly forced to true
            $new = new SymfonyCookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                true, // httpOnly forced
                $cookie->isRaw(),
                method_exists($cookie, 'getSameSite') ? $cookie->getSameSite() : null
            );

            $response->headers->setCookie($new);
        }

        return $response;
    }
}
