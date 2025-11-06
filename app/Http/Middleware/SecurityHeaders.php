<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add a set of recommended security headers to responses.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!($response instanceof Response)) {
            return $response;
        }

        // X-Frame-Options: DENY or SAMEORIGIN
        $xFrame = strtoupper(env('X_FRAME_OPTIONS', 'DENY'));
        if (!in_array($xFrame, ['DENY', 'SAMEORIGIN'])) {
            $xFrame = 'DENY';
        }
        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', $xFrame);
        }

        // Prevent MIME sniffing
        if (! $response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        // Ensure Content-Type is set for HTML responses to avoid browser guessing
        if (! $response->headers->has('Content-Type')) {
            $content = (string) $response->getContent();
            $looksLikeHtml = false;
            if ($content !== '') {
                $low = strtolower(substr($content, 0, 512));
                if (strpos($low, '<!doctype') !== false || strpos($low, '<html') !== false || strpos($low, '<script') !== false || strpos($low, '<div') !== false) {
                    $looksLikeHtml = true;
                }
            }

            if ($looksLikeHtml) {
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            }
        }

        // Referrer-Policy
        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', env('REFERRER_POLICY', 'strict-origin-when-cross-origin'));
        }

        // Permissions-Policy (formerly Feature-Policy) - keep conservative defaults
        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=()");
        }

        // HSTS - only set when running over HTTPS and enabled
        $hstsEnabled = env('HSTS_ENABLED', false);
        if ($hstsEnabled && $request->isSecure()) {
            if (! $response->headers->has('Strict-Transport-Security')) {
                // 6 months by default
                $maxAge = env('HSTS_MAX_AGE', 15768000);
                $includeSubDomains = env('HSTS_INCLUDE_SUBDOMAINS', false) ? '; includeSubDomains' : '';
                $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}{$includeSubDomains}");
            }
        }

        if ($response->headers->has('X-Powered-By')) {
            $response->headers->remove('X-Powered-By');
        }

        return $response;
    }
}
