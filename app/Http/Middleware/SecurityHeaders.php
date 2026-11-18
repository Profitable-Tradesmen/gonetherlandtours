<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        \App\Support\SiteData::flush(); // per-request memo: start each request clean (matters for tests and long-running workers)
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if (config('gnt.site_noindex')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Safe, non-breaking Content-Security-Policy directives (a full script/style policy would need to allow Alpine, GTM and reCAPTCHA).
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'");
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
