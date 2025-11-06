<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and add a CSP header with a per-request nonce.
     *
     * This middleware defaults to Report-Only mode. Set CSP_REPORT_ONLY=false in your
     * environment to enforce the policy.
     */
    public function handle(Request $request, Closure $next)
    {
        // Generate a nonce for this request (used for inline scripts/styles if needed)
        try {
            $nonce = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            // fallback
            $nonce = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 32);
        }

        // Share nonce with views so templates can add it to inline <script nonce="{{ $cspNonce }}"> tags
        View::share('cspNonce', $nonce);

        $reportOnly = env('CSP_REPORT_ONLY', true);

        // NOTE: This policy is intentionally conservative and should be adapted to your app's needs.
        // Start in report-only mode and tighten over time.
        $policy = implode('; ', [
            "default-src 'self'",
            // allow scripts from self and any inline scripts that include the nonce
            "script-src 'self' 'nonce-{$nonce}'",
            // allow styles from self and inline styles (you may remove 'unsafe-inline' after migrating styles)
            "style-src 'self' 'unsafe-inline' 'nonce-{$nonce}'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "connect-src 'self' ws:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response = $next($request);

        // Only add header to HTTP responses
        if ($response instanceof Response) {
            $headerName = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            // Append to any existing CSP header (do not overwrite if already set by webserver)
            if ($response->headers->has($headerName)) {
                // merge by prepending our policy (server-side may already have configured headers)
                $existing = $response->headers->get($headerName);
                $response->headers->set($headerName, $policy . '; ' . $existing);
            } else {
                $response->headers->set($headerName, $policy);
            }
        }

        return $response;
    }
}
